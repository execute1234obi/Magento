<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMembership\Cron;

use Vnecoms\Vendors\Model\VendorFactory;
use Vnecoms\Vendors\Model\Vendor;
use Vnecoms\VendorsMembership\Model\Source\ExpiryAction;

class CheckExpiryDate
{
    /**
     * @var \Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory
     */
    private $_vendorFactory;
    
    /**
     * @var \Vnecoms\VendorsMembership\Helper\Data
     */
    protected $_membershipHelper;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    
    /**
     * Constructor
     * 
     * @param AppResource $resource
     */
    public function __construct(
        \Vnecoms\VendorsMembership\Helper\Data $membershipHelper,
        VendorFactory $vendorFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_membershipHelper = $membershipHelper;
        $this->_vendorFactory = $vendorFactory;
        $this->_logger = $logger;
    }
    
    /**
     * Run process send product alerts
     *
     * @return $this
     */
    public function process()
    {
        $resouce = $this->_vendorFactory->create()->getResource();
        $connection = $resouce->getConnection();
        $table = $resouce->getTable('ves_vendor_entity');
        $today = (new \DateTime())->format('Y-m-d');
        
        $expiryDaysBefore = $this->_membershipHelper->getExpiryDayBefore();
        $dateObj = new \DateTime();
        $dateObj->add(new \DateInterval('P'.$expiryDaysBefore.'D'));
        $beforeDays = $dateObj->format('Y-m-d');
        
        $expiryAction = $this->_membershipHelper->getExpiryAction();
        if($expiryAction == ExpiryAction::ACTION_CLOSE){
            /*Set status of expired accounts to Expired*/
            $sql = "UPDATE {$table} SET status=:expired_status WHERE status=:status AND expiry_date IS NOT NULL AND expiry_date < :today";
            $bind = [
                'expired_status' => Vendor::STATUS_EXPIRED,
                'status' => Vendor::STATUS_APPROVED,
                'today' => $today
            ];
        }elseif($expiryAction == ExpiryAction::ACTION_MOVE){
            /*change vendor group of expired accounts*/
            $expiryGroup = $this->_membershipHelper->getExpiryVendorGroup();
            $sql = "UPDATE {$table} SET group_id=:group_id, expiry_date=null WHERE status=:status AND expiry_date IS NOT NULL AND expiry_date < :today";
            $bind = [
                'group_id' => $expiryGroup,
                'status' => Vendor::STATUS_APPROVED,
                'today' => $today
            ];
        }
        
        $connection->query($sql, $bind);        
        $this->_logger->info('Process membership expiry date !');
        
        /*Send Notification Emails*/
        $select = $connection->select();
        $select->from(
            $table,
            ['entity_id']
        )->where(
            'status = :status'
        )->where(
            'expiry_date IS NOT NULL AND expiry_date = :before7day'
        );
        
        $bind = ['status' => Vendor::STATUS_APPROVED, 'before7day' => $beforeDays];
        
        $vendorIds = $connection->fetchCol($select, $bind);

        foreach($vendorIds as $vendorId){
            $vendor = $this->_vendorFactory->create()->load($vendorId);
            $this->_membershipHelper->sendExpiryNotificationEmail($vendor);
        }
    }
}
