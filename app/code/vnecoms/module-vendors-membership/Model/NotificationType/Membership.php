<?php

namespace Vnecoms\VendorsMembership\Model\NotificationType;

use Vnecoms\VendorsProfileNotification\Model\Type\AbstractType;
use Vnecoms\VendorsProfileNotification\Model\Process;
use Vnecoms\Vendors\Model\Vendor;
use Magento\Framework\Data\Form;
use Vnecoms\Vendors\Model\UrlInterface;
use Vnecoms\VendorsMembership\Model\ResourceModel\Transaction\CollectionFactory;

class Membership extends AbstractType
{
    const CODE              = 'type_membership';
    const CONDITION_EXPIRED = 'expired';
    const CONDITION_NOT_PAID= 'not_paid';
    
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * @var \Magento\Framework\Url
     */
    protected $frontendUrl;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * @var \Vnecoms\VendorsMembership\Helper\Data
     */
    protected $helper;
    
    /**
     * @param UrlInterface $urlBuilder
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Url $frontendUrl
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Vnecoms\VendorsMembership\Helper\Data $helper
     */
    public function __construct(
        UrlInterface $urlBuilder,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Url $frontendUrl,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Vnecoms\VendorsMembership\Helper\Data $helper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->frontendUrl = $frontendUrl;
        $this->date = $date;
        $this->helper = $helper;
        parent::__construct($urlBuilder);
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\VendorsProfileNotification\Model\Type\AbstractType::getTitle()
     */
    public function getTitle(){
        return __('Vendor Membership');
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\VendorsProfileNotification\Model\Type\AbstractType::prepareForm()
     */
    public function prepareForm(
        Form $form,
        Process $process
    ){
        $fieldset = $form->getElement('base_fieldset');
        $fieldset->addField(
            'membership',
            'select',
            [
                'name' => 'membership',
                'label' => __('Condition'),
                'title' => __('Condition'),
                'values' => [
                    ['label' => __("Membership will be expired soon."), 'value' => self::CONDITION_EXPIRED],
                    ['label' => __("Not buy any membership yet."), 'value' => self::CONDITION_NOT_PAID],
                ],
                'class' => 'process_type_field '.self::CODE,
                'required' => true
            ],
            'type'
        );
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\VendorsProfileNotification\Model\Type\AbstractType::beforeSaveProcess()
     */
    public function beforeSaveProcess(
        Process $process
    ){
        $condition = $process->getData('membership');
        if(!$condition) return;
        $process->setData('additional_data', $condition);
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\VendorsProfileNotification\Model\Type\AbstractType::afterLoadProcess()
     */
    public function afterLoadProcess(
        Process $process
    ){
        $condition = $process->getData('additional_data');
        $process->setData('membership', $condition);
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\VendorsProfileNotification\Model\Type\AbstractType::isCompletedProcess()
     */
    public function isCompletedProcess(Process $process, Vendor $vendor){
        $condition = $process->getData('additional_data');
        switch($condition){
            case self::CONDITION_EXPIRED:
                if(!$vendor->getExpiryDate()) return true;
                
                $expiryDaysBefore = $this->helper->getExpiryDayBefore();
                $todayTime = $this->date->timestamp();
                $vendorExpiryTime = strtotime($vendor->getExpiryDate());
                if($vendorExpiryTime < $todayTime) return false;
                
                $dateObj = new \DateTime();
                $dateObj->setTimestamp($todayTime);
                $dateObj->add(new \DateInterval('P'.$expiryDaysBefore.'D'));
                $beforeDays = strtotime($dateObj->format('Y-m-d H:i:s'));
                
                return $vendorExpiryTime > $beforeDays;
            case self::CONDITION_NOT_PAID:
                $collection = $this->collectionFactory->create()->addFieldToFilter('vendor_id', $vendor->getId());
                return $collection->count()>0;
        }
        return true;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\VendorsProfileNotification\Model\Type\AbstractType::getUrl()
     */
    public function getUrl(Process $process){
        return $this->frontendUrl->getUrl('vendorsmembership');
    }
}
