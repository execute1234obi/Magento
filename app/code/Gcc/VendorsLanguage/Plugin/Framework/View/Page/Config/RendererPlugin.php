<?php
/**
 * Copyright © 2018 Vnecoms. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace Gcc\VendorsLanguage\Plugin\Framework\View\Page\Config;

use \Magento\Framework\View\Page\Config as PageConfig;
use \Vnecoms\Vendors\App\Area\FrontNameResolver;

/**
 * Class with class map capability
 *
 * ...
 */
class RendererPlugin
{
    /**
     * @var PageConfig
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Vnecoms\VendorsConfig\Helper\Data
     */
    protected $vendorConfig;

    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $vendorsSession;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        PageConfig $pageConfig,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\State $appState,
        \Vnecoms\VendorsConfig\Helper\Data $vendorConfig,
        \Vnecoms\Vendors\Model\Session $vendorSession
    ) {
        $this->localeResolver = $localeResolver;
        $this->pageConfig = $pageConfig;
        $this->moduleManager = $moduleManager;
        $this->vendorConfig = $vendorConfig;
        $this->vendorsSession = $vendorSession;
        $this->appState = $appState;
    }

    /**
     * Interceptors around render element page
     *
     * @param PageConfig\Renderer $subject
     * @param \Closure $proceed
     * @param $elementType
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
   public function aroundRenderAttributesHtml(
    \Magento\Framework\View\Page\Config\Renderer $subject,
    \Closure $proceed
) {
    $result = $proceed();

    // 👉 URL se locale uthao
    $locale = $_GET['locale'] ?? null;

    if ($locale) {
        $lang = strstr($locale, '_', true) ?: $locale;

        // lang="en" replace karo
        $result = preg_replace('/lang="(.*?)"/', 'lang="'.$lang.'"', $result);
    }

    return $result;
}
}
