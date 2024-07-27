<?php

declare(strict_types=1);

namespace Omnipro\QuickProductPositioning\Controller\Adminhtml\Export;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\ResourceConnection;

class ExportProducts extends Action
{
    /**
     * @var FileFactory
     */
    protected FileFactory $fileFactory;

    /**
     * @var File
     */
    protected File $fileIo;

    /**
     * @var CategoryRepositoryInterface
     */
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * @var ProductCollectionFactory
     */
    protected ProductCollectionFactory $productCollectionFactory;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * Constructor
     *
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param File $fileIo
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ResourceConnection $resource
     * @param DirectoryList $directoryList
     */
    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        File $fileIo,
        CategoryRepositoryInterface $categoryRepository,
        ProductCollectionFactory $productCollectionFactory,
        ResourceConnection $resource,
        DirectoryList $directoryList
    ) {
        $this->fileFactory = $fileFactory;
        $this->fileIo = $fileIo;
        $this->categoryRepository = $categoryRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->directoryList = $directoryList;
        $this->resource = $resource;
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function execute()
    {
        $categoryId = (int) $this->getRequest()->getParam('id');
        $fileName = 'products.csv';
        $filePath = $this->directoryList->getPath(DirectoryList::TMP) . "/" . $fileName;
        $this->createCsvFile($filePath, $categoryId);

        return $this->fileFactory->create(
            $fileName,
            [
                'type' => 'filename',
                'value' => $filePath,
                'rm' => true
            ],
            DirectoryList::TMP,
            'text/csv'
        );
    }

    /**
     * Create CSV file
     *
     * @param string $filePath
     * @param int $categoryId
     * @return void
     */
    protected function createCsvFile(string $filePath, int $categoryId): void
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from(
                ['ccp' => $connection->getTableName('catalog_category_product')],
                ['entity_id', 'category_id', 'product_id', 'position']
            )
            ->joinLeft(
                ['e' => $connection->getTableName('catalog_product_entity')],
                'ccp.product_id = e.entity_id',
                ['sku', 'type_id']
            )
            ->distinct()
            ->where('ccp.category_id = ?', $categoryId);

        $data = $connection->fetchAll($select);

        $csvData = [['entity_id', 'category_id', 'product_id', 'position', 'sku',  'type_id']];
        foreach ($data as $row) {
            $csvData[] = [
                $row['entity_id'],
                $row['category_id'],
                $row['product_id'],
                $row['position'],
                $row['sku'],
                $row['type_id']
            ];
        }

        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= '"' . implode('","', array_map('strval', $row)) . '"' . "\n";
        }

        $this->fileIo->write($filePath, $csvContent);
    }
}
