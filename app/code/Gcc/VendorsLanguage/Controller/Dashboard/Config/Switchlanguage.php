<?php

namespace Gcc\VendorsLanguage\Controller\Dashboard\Config;

use Vnecoms\Vendors\Controller\Vendors\Action;
use Vnecoms\Vendors\App\Action\Context;

class Switchlanguage extends Action
{
    /**
     * @var \Vnecoms\VendorsConfig\Helper\Data
     */
    protected $vendorConfig;

    /**
     * @param Context $context
     * @param \Vnecoms\VendorsConfig\Helper\Data $vendorConfig
     */
    public function __construct(
        Context $context,
        \Vnecoms\VendorsConfig\Helper\Data $vendorConfig
    ) {
        // Vnecoms Action ka parent constructor use karein
        // Isme vendorSession pehle se hi include hota hai ($this->_vendorSession)
        parent::__construct($context);
        $this->vendorConfig = $vendorConfig;
    }

    public function execute()
    {
        $locale = $this->getRequest()->getParam('locale');
        
        $vendor = $this->_vendorSession->getVendor();

        if ($locale && $vendor && $vendor->getId()) {
            try {
                $this->vendorConfig->setVendorConfig(
                    'general/locale/code',
                    $locale,
                    $vendor->getId()
                );
                $this->messageManager->addSuccessMessage(__('Language has been changed.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Cannot switch language.'));
            }
        }

        return $this->_redirect('dashboard/index/index');
    }
}
