<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\VendorsMembership\Observer;

use Magento\Framework\Event\ObserverInterface;
use Vnecoms\VendorsMembership\Model\Product\Type\Membership;

class CheckoutPredispatchObserver implements ObserverInterface
{
    /**
     * @var \Vnecoms\VendorsMembership\Helper\Data
     */
    protected $_membershipHelper;

    /**
     * @var \Vnecoms\Vendors\Model\SessionFactory
     */
    protected $_vendorSessionFactory;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $_onepage;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;

    /**
     * @var \Vnecoms\Vendors\Model\GroupFactory
     */
    protected $_groupFactory;

    public function __construct(
        \Vnecoms\VendorsMembership\Helper\Data $membershipHelper,
        \Vnecoms\Vendors\Model\SessionFactory $vendorSessionFactory,
        \Magento\Checkout\Model\Type\Onepage $onepage,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Vnecoms\Vendors\Model\GroupFactory $groupFactory
    ) {
        $this->_membershipHelper = $membershipHelper;
        $this->_vendorSessionFactory = $vendorSessionFactory;
        $this->_onepage = $onepage;
        $this->_messageManager = $messageManager;
        $this->_redirect = $redirect;
        $this->_groupFactory = $groupFactory;
    }

    /**
     * Add the notification if there are any vendor awaiting for approval. 
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controllerAction = $observer->getControllerAction();
        $request = $observer->getRequest();
        $quote = $this->_onepage->getQuote();
        $session = $this->_vendorSessionFactory->create();
        $customer = $session->getCustomer();
        $vendor = $session->getVendor();
        
        $hasMembershipPackage = false;
        try {
            $loadedGroups = [];
            foreach ($quote->getAllItems() as $item) {
                if ($item->getProductType() == Membership::TYPE_CODE) {
                    if(!$vendor->getId()) throw new \Exception(
                        __('Only seller account can buy membership packages.')
                    );
                    
                    $relatedGroupId = $item->getProduct()->load($item->getProductId())
                       ->getData('vendor_membership_group_id');

                    if (
                        $hasMembershipPackage
                        && $hasMembershipPackage != $relatedGroupId
                    ) {
                        /*Return error cannot add different membership package in shopping cart*/
                        throw new \Exception(
                            __('You cannot buy different membership packages.')
                        );
                    }

                    $hasMembershipPackage = $relatedGroupId;
                    
                    
                    if ($relatedGroupId != $vendor->getGroupId()) {
                        if(!isset($loadedGroups[$relatedGroupId])){
                            $loadedGroups[$relatedGroupId] = $this->_groupFactory->create();
                            $loadedGroups[$relatedGroupId]->load($relatedGroupId);
                        }
                        $group = $loadedGroups[$relatedGroupId];
                        if ($group->getRank() < $vendor->getGroup()->getRank()) {
                            /*Return error can not downgrade membership*/
                            throw new \Exception(__(
                                'You are in %1 membership you cannot downgrade to a lower package (%2).',
                                '<strong>'.$vendor->getGroup()->getVendorGroupCode().'</strong>',
                                '<strong>'.$group->getVendorGroupCode().'</strong>'
                            ));
                        }
                    }
                }
            }
            
            
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
            $this->_redirect->redirect($controllerAction->getResponse(), 'checkout/cart');
            $request->setDispatched(true);
            $controllerAction->getActionFlag()->set('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH, true);
        }
    }
}
