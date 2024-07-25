<?php

declare(strict_types=1);

namespace Omnipro\QuickProductPositioning\Ui\Component\Listing\Column;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ProductSku extends Column
{
    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @param ContextInterface          $context
     * @param UiComponentFactory        $uiComponentFactory
     * @param ProductRepositoryInterface $productRepository
     * @param array                     $components
     * @param array                     $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ProductRepositoryInterface $productRepository,
        array $components = [],
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $productId = $item['entity_id'];
                try {
                    $product = $this->productRepository->getById($productId);
                    $item['sku'] = $product->getSku();
                    $item['product_name'] = $product->getName();
                    $item['product_type'] = $product->getTypeId();
                } catch (NoSuchEntityException $e) {
                    $item['sku'] = __('SKU not available');
                    $item['product_name'] = __('Name not available');
                    $item['product_type'] = __('Type not available');
                }
            }
        }
        return $dataSource;
    }
}
