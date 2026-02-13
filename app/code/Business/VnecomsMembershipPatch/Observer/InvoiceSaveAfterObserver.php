<?php
namespace Business\VnecomsMembershipPatch\Observer;

use Magento\Framework\Event\ObserverInterface;
use Vnecoms\VendorsMembership\Model\Product\Type\Membership;
use Vnecoms\VendorsMembership\Model\Source\DurationUnit;
use Vnecoms\Vendors\Model\Vendor;

class InvoiceSaveAfterObserver implements ObserverInterface
{
    protected $_customerFactory;
    protected $_vendorFactory;
    protected $_transactionFactory;
    protected $_date;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Vnecoms\Vendors\Model\VendorFactory $vendorFactory,
        \Vnecoms\VendorsMembership\Model\TransactionFactory $transactionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_vendorFactory = $vendorFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_date = $date;
    }

  public function execute(\Magento\Framework\Event\Observer $observer)
{
    $invoice = $observer->getInvoice();
    if (!$invoice) return;

    if ($invoice->getData('membership_processed')) {
        return;
    }

    $order = $invoice->getOrder();
    if (!$order || !$order->getTotalPaid() || $order->getTotalPaid() <= 0) {
        return;
    }

    $this->processBuyMembership($invoice);

    $invoice->setData('membership_processed', 1);
    $invoice->save();
}


    public function processBuyMembership(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $customerId = $order->getCustomerId();
        if (!$customerId) return;

        $customer = $this->_customerFactory->create()->load($customerId);
        if (!$customer->getId()) return;

        $vendor = $this->_vendorFactory->create()->loadByCustomer($customer);
        if (!$vendor->getId()) return;

        foreach ($invoice->getAllItems() as $item) {

            $orderItem = $item->getOrderItem();
            if ($orderItem->getParentItemId()) continue;
            if ($orderItem->getProductType() != Membership::TYPE_CODE) continue;

            $product = $orderItem->getProduct();
            if (!$product) continue;

            // 🔧 PATCH START
            $options = $orderItem->getProductOptions();
            if (!is_array($options)) {
                $options = @unserialize($options);
            }

            if (!isset($options['membership'])) {
                $options['membership'] = [
                    'related_group_id' => $product->getData('vendor_membership_group_id'),
                    'duration'         => (int)$product->getData('duration'),
                    'duration_unit'    => (int)$product->getData('duration_unit')
                ];
            }

            $membership = $options['membership'];
            // 🔧 PATCH END

            if (
                empty($membership['related_group_id']) ||
                empty($membership['duration']) ||
                empty($membership['duration_unit'])
            ) {
                continue;
            }

            $duration = $membership['duration'] * $item->getQty();
            $time = '';

            switch ($membership['duration_unit']) {
                case DurationUnit::DURATION_DAY:
                    $time = "+$duration days"; break;
                case DurationUnit::DURATION_WEEK:
                    $time = "+".($duration * 7)." days"; break;
                case DurationUnit::DURATION_MONTH:
                    $time = "+$duration months"; break;
                case DurationUnit::DURATION_YEAR:
                    $time = "+$duration years"; break;
            }

            $currentTime = $vendor->getExpiryDate() ?: $this->_date->date();
            $expiryTime = strtotime($currentTime . $time);

            $vendor->setGroupId($membership['related_group_id']);
            $vendor->setExpiryDate($expiryTime);

            if ($vendor->getStatus() == Vendor::STATUS_EXPIRED) {
                $vendor->setStatus(Vendor::STATUS_APPROVED);
            }

            $vendor->save();
        }
    }
}
