<?php

declare(strict_types=1);

namespace Omnipro\QuickProductPositioning\Block\Adminhtml\Category;

class AssignProducts extends \Magento\Catalog\Block\Adminhtml\Category\AssignProducts
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'Omnipro_QuickProductPositioning::catalog/category/edit/assign_products.phtml';
}
