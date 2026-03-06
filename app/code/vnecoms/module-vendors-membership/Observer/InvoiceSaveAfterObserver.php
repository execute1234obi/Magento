<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\VendorsMembership\Observer;

use Magento\Framework\Event\ObserverInterface;
use Vnecoms\VendorsMembership\Model\Product\Type\Membership;
use Vnecoms\VendorsMembership\Model\Source\DurationUnit;
use Vnecoms\Vendors\Model\Vendor;

class InvoiceSaveAfterObserver implements ObserverInterface
{

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Vnecoms\Vendors\Model\VendorFactory
     */
    protected $_vendorFactory;
    
    /**
     * @var \Vnecoms\VendorsMembership\Model\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Vnecoms\Vendors\Model\VendorFactory $vendorFactory,
        \Vnecoms\VendorsMembership\Model\TransactionFactory $transactionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_transactionFactory = $transactionFactory;
        $this->_vendorFactory = $vendorFactory;
        $this->_customerFactory = $customerFactory;
        $this->_date = $date;
    }

    /**
     * Add the notification if there are any vendor awaiting for approval. 
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //print_r("me invoice save after me hu");
        //exit();
        $invoice = $observer->getInvoice();
        $order = $invoice->getOrder();

        /*Return if the invoice is not paid*/
        if ($invoice->getState() != \Magento\Sales\Model\Order\Invoice::STATE_PAID) {
            return;
        }

        /*Buy membership package*/
        $this->processBuyMembership($invoice);
    }

    /**
     * Process buy membership transaction.
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     */
    public function processBuyMembership(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();

        $customerId = $order->getCustomerId();
        if (!$customerId) {
            return;
        }

        $customer = $this->_customerFactory->create();
        $customer->load($customerId);

        if (!$customer->getId()) {
            return;
        }

        $vendor = $this->_vendorFactory->create();
        $vendor->loadByCustomer($customer);
        
        if (!$vendor->getId()) {
            return;
        }
        
        /*Return if the transaction for the invoice is already exist.*/
        $trans = $this->_transactionFactory->create()->getCollection()
            ->addFieldToFilter(
                'additional_info',
                ['like' => 'invoice|'.$invoice->getId()]
            );

        if ($trans->count()) {
            return;
        }

        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->getParentItemId()) {
                continue;
            }

            if ($orderItem->getProductType() != Membership::TYPE_CODE) {
                continue;
            }

            $product = $orderItem->getProduct();
            if (!$product) {
                continue;
            }

            $membershipOption = $orderItem->getProductOptions();
            if (!is_array($membershipOption)) {
                $membershipOption = unserialize($membershipOption);
            }
            $membershipOption = $membershipOption['membership'];

            $relatedGroupId = isset($membershipOption['related_group_id']) ?
                            $membershipOption['related_group_id'] :
                            $product->getData('vendor_membership_group_id');
            $duration = isset($membershipOption['duration']) ? $membershipOption['duration'] : 0;
            $durationUnit = isset($membershipOption['duration_unit']) ? $membershipOption['duration_unit'] : 0;

            if (!$relatedGroupId || !$duration || !$durationUnit) {
                continue;
            }

            $time = '';
            $duration = $duration * $item->getQty();

            switch ($durationUnit) {
                case DurationUnit::DURATION_DAY:
                    $time = "+$duration days";
                    break;
                case DurationUnit::DURATION_WEEK:
                    $duration = $duration * 7;
                    $time = "+$duration days";
                    break;
                case DurationUnit::DURATION_MONTH:
                    $time = "+$duration months";
                    break;
                case DurationUnit::DURATION_YEAR:
                    $time = "+$duration years";
                    break;
            }

            if ($vendor->getGroupId() == $relatedGroupId) {
                /*Renew the current package*/
                $currentTime = $vendor->getExpiryDate();
                if (!$currentTime) {
                    $currentTime = $this->_date->date();
                }
            } else {
                /*Upgrade to new package*/
                $currentTime = $this->_date->date();
            }
            $expiryTime = strtotime($currentTime.$time);

            $vendor->setGroupId($relatedGroupId);
            $vendor->setExpiryDate($expiryTime);
            if($vendor->getStatus() == Vendor::STATUS_EXPIRED){
                $vendor->setStatus(Vendor::STATUS_APPROVED);
            }
            $vendor->save();

            $trans = $this->_transactionFactory->create();
            $trans->setData([
                'vendor_id' => $vendor->getId(),
                'package' => $item->getName(),
                'amount' => $item->getBaseRowTotal(),
                'duration' => $duration,
                'duration_unit' => $durationUnit,
                'additional_info' => 'invoice|'.$invoice->getId().'||item|'.$item->getId(),
                'created_at' => $this->_date->timestamp(),
            ]);
            $trans->save();
        }
    }
}
