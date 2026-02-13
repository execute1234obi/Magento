<?php

namespace Vendor\VendorsVerification\Observer;

use Magento\Framework\Event\ObserverInterface;

class AccountFormObserver implements ObserverInterface
{
    
    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $_vendorSession;
    
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
      * @var LoggerInterface
     */     
    protected $logger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Vnecoms\Vendors\Model\VendorFactory $vendorFactory
     * @param \Vnecoms\Credit\Model\Processor $creditProcessor
     * @param \Vnecoms\Credit\Model\CreditFactory $creditAccountFactory
     * @param \Vnecoms\Credit\Model\Credit\TransactionFactory $transactionFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Vnecoms\Vendors\Model\Session $vendorSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_vendorSession = $vendorSession;
        $this->logger = $logger;
    }    
     
     
    /**
     * Add the notification if there are any vendor awaiting for approval.
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $form = $observer->getForm();
        $tab = $observer->getTab();
        $element = $form->getElement('gmap_lng');        
        if(!$element) return;
        //$this->logger->info("element methods",get_class_methods($element));
        $renderer = $tab->getLayout()->createBlock('Vendor\VendorsVerification\Block\Vendors\Account\Edit\Form\Renderer\LocationMap');
        $element->setRenderer($renderer);
    }

     

    }
