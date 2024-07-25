<?php

declare(strict_types=1);

namespace Omnipro\QuickProductPositioning\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zend_Validate_Exception;

class InstallFeaturedProductAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * InstallDefaultTemplate constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->installFeaturedAttribute();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function installFeaturedAttribute(): void
    {
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->addAttribute(
            Product::ENTITY,
            'featured',
            [
                'group' => 'General',
                'type' => 'int',
                'label' => 'Featured',
                'input' => 'boolean',
                'backend' => '',
                'source' => Boolean::class,
                'required' => false,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'default' => 0,
                'visible' => true,
                'user_defined' => true,
                'used_in_product_listing' => true,
                'searchable' => false,
                'filterable' => false,
                'apply_to' => 'configurable'
            ]
        );
    }
}
