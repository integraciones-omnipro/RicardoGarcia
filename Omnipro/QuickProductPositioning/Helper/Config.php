<?php

namespace Omnipro\QuickProductPositioning\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;

class Config
{
    public const XML_PATH_CATEGORY_POSITION_STATUS_VALIDATION = 'position_configuration/general/enabled';

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfig $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param int|string|null $store
     * @return bool
     */
    public function isEnabled(int|string $store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CATEGORY_POSITION_STATUS_VALIDATION,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
