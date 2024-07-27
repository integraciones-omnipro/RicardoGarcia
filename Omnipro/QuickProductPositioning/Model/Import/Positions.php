<?php

declare(strict_types=1);

namespace Omnipro\QuickProductPositioning\Model\Import;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Psr\Log\LoggerInterface;

class Positions extends AbstractEntity
{
    public const TABLE = 'catalog_category_product';
    public const ENTITY_ID_COLUMN = 'entity_id';
    public const COL_CATEGORY_ID = 'category_id';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_POSITION = 'position';
    public const COL_SKU = 'sku';
    public const COL_TYPE_ID = 'type_id';

    /**
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * @var string[]
     */
    protected $_permanentAttributes = [
        self::ENTITY_ID_COLUMN,
        self::COL_CATEGORY_ID,
        self::COL_PRODUCT_ID,
        self::COL_POSITION
    ];

    /**
     * @var string[]
     */
    protected $validColumnNames = [
        self::ENTITY_ID_COLUMN,
        self::COL_CATEGORY_ID,
        self::COL_PRODUCT_ID,
        self::COL_POSITION,
        self::COL_SKU,
        self::COL_TYPE_ID
    ];

    /**
     * @var AdapterInterface
     */
    protected AdapterInterface $connection;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @param JsonHelper $jsonHelper
     * @param ImportHelper $importExportData
     * @param Data $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param LoggerInterface $logger
     */
    public function __construct(
        JsonHelper $jsonHelper,
        ImportHelper $importExportData,
        Data $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        LoggerInterface $logger
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->errorAggregator = $errorAggregator;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getEntityTypeCode(): string
    {
        return 'catalog_product';
    }

    /**
     * @return string[]
     */
    public function getValidColumnNames(): array
    {
        return $this->validColumnNames;
    }

    /**
     * @param array $rowData
     * @param $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum): bool
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        array_filter(
            $rowData,
            function ($key) {
                return in_array($key, $this->getValidColumnNames());
            },
            ARRAY_FILTER_USE_KEY
        );

        $this->_validatedRows[$rowNum] = true;

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * @return true
     */
    protected function _importData()
    {
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->deleteEntity();
                break;
            case Import::BEHAVIOR_APPEND:
            case Import::BEHAVIOR_REPLACE:
                $this->saveAndReplaceEntity();
                break;
        }

        return true;
    }

    /**
     * @return void
     */
    private function deleteEntity(): void
    {
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);

                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowId = $rowData[static::ENTITY_ID_COLUMN];
                    $rows[] = $rowId;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }

        if ($rows) {
            $this->deleteEntityFinish(array_unique($rows));
        }
    }

    /**
     * @return void
     */
    private function saveAndReplaceEntity(): void
    {
        $behavior = $this->getBehavior();
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];

            foreach ($bunch as $rowNum => $row) {
                if (!$this->validateRow($row, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);

                    continue;
                }

                $rowId = $row[static::ENTITY_ID_COLUMN];
                $rows[] = $rowId;
                $columnValues = [];

                foreach ($this->getAvailableColumns() as $columnKey) {
                    $columnValues[$columnKey] = $row[$columnKey];
                }

                $entityList[$rowId][] = $columnValues;
                $this->countItemsCreated += (int) !isset($row[static::ENTITY_ID_COLUMN]);
                $this->countItemsUpdated += (int) isset($row[static::ENTITY_ID_COLUMN]);
            }

            if (Import::BEHAVIOR_REPLACE === $behavior) {
                if ($rows && $this->deleteEntityFinish(array_unique($rows))) {
                    $this->saveEntityFinish($entityList);
                }
            } elseif (Import::BEHAVIOR_APPEND === $behavior) {
                $this->saveEntityFinish($entityList);
            }
        }
    }

    private function saveEntityFinish(array $entityData): void
    {
        if ($entityData) {
            $tableName = $this->connection->getTableName(static::TABLE);
            $rows = [];

            foreach ($entityData as $entityRows) {
                foreach ($entityRows as $row) {
                    $filteredRow = array_filter(
                        $row,
                        function ($key) {
                            return in_array($key, $this->getAvailableColumns());
                        },
                        ARRAY_FILTER_USE_KEY
                    );
                    $rows[] = $filteredRow;
                }
            }

            if ($rows) {
                $this->connection->insertOnDuplicate($tableName, $rows, $this->getAvailableColumns());
            }
        }
    }

    /**
     * @param array $entityIds
     * @return bool
     */
    private function deleteEntityFinish(array $entityIds): bool
    {
        if ($entityIds) {
            try {
                $this->countItemsDeleted += $this->connection->delete(
                    $this->connection->getTableName(static::TABLE),
                    $this->connection->quoteInto(static::ENTITY_ID_COLUMN . ' IN (?)', $entityIds)
                );

                return true;
            } catch (Exception $e) {
                $this->logger->error(__('LocalizedException: %1', $e->getMessage()));
                return false;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function getAvailableColumns(): array
    {
        return array_diff($this->validColumnNames, [self::COL_SKU, self::COL_TYPE_ID]);
    }
}
