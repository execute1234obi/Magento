<?php

namespace Vendor\VendorsVerification\Cron;

use Vnecoms\Vendors\Model\VendorFactory;
//use Vnecoms\Vendors\Model\Vendor;
use Vendor\VendorsVerification\Model\VendorVerification;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\ResourceModel\VendorVerification\CollectionFactory;     

class CheckExpiryDate
{
	
	 /**
     * @var \Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory
     */
    private $_vendorFactory;
    
	 /**
     * @var \Vendor\VendorsVerification\Model\VendorVerificationFactory
     */
    private $vendorsVerificationFactory;
    
    
    /**
     * @var \Vendor\VendorsVerification\Model\ResourceModel\VendorVerification\CollectionFactory
     */
     
    protected $verifictionCollectionFactory;
    
    /**
     * @var \Vendor\VendorsVerification\Helper\Data
     */
    protected $_vendorsVerificationHelper;
    
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
        VendorVerificationFactory $vendorsVerificationFactory,   
        CollectionFactory $verifictionCollectionFactory,
        VendorFactory $vendorFactory,
        \Vendor\VendorsVerification\Helper\Data  $helper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->vendorsVerificationFactory = $vendorsVerificationFactory;         
        $this->verifictionCollectionFactory = $verifictionCollectionFactory;
        $this->_vendorFactory = $vendorFactory;
        $this->_vendorsVerificationHelper = $helper;
        $this->_logger = $logger;
    }
    
    /**
     * Run process send product alerts
     *
     * @return $this
     */
    public function process()    
    {
	  try {
        $resouce = $this->_vendorFactory->create()->getResource();
        $connection = $resouce->getConnection();
        $table = $resouce->getTable('Vendor_vendor_verification');
        $today = (new \DateTime())->format('Y-m-d');        
        $expiryDaysBefore = 0;
        $dateObj = new \DateTime();
        $dateObj->add(new \DateInterval('P'.$expiryDaysBefore.'D'));
        $beforeDays = $dateObj->format('Y-m-d');
        
        $sql = "UPDATE {$table} SET is_verified=:expired_status,is_active=:inactive WHERE is_verified=:is_verified AND to_date IS NOT NULL AND to_date < :today";
            $bind = [
                'expired_status' => VendorVerification::STATUS_EXPIRED,
                'inactive'=> 0,
                'is_verified'=> 1,                
                'today' => $today
            ];
        
        $connection->query($sql, $bind);
        $this->updateVendorVerificationStatus();//Update Vendor attribute 'is_verified' 
        $this->_logger->info('Process Vendor Verification expiry date !');
        
        /*Send Notification Emails*/
        $select = $connection->select();
        $select->from(
            $table,
            ['verification_id',]
        )->where(
            'is_verified = :expired_status'
        )->where(
            'to_date IS NOT NULL AND to_date <= :before7day'
        );
        
        $bind = ['expired_status' => VendorVerification::STATUS_EXPIRED, 'before7day' => $beforeDays];
        
        $verificationIds = $connection->fetchCol($select, $bind);
        

       foreach($verificationIds as $verificationId){
		   $vendorVerifiation =  $this->vendorsVerificationFactory->create()->load($verificationId);
		   $vendorId = $vendorVerifiation->getVendorId();
           $vendor = $this->_vendorFactory->create()->load($vendorId);
           $this->_vendorsVerificationHelper->sendExpiryNotificationEmail($vendorVerifiation, $vendor);
          }
	   } catch (\Exception $e) {                            
            $this->_logger->info("VendorVerification Cron DisableAd Error ".$e->getMessage());
       }
    }
    
    protected function updatevendorVerificationStatus(){
		$today = (new \DateTime())->format('Y-m-d');       
		$verificationCollection = $this->verifictionCollectionFactory->create();
		$verificationCollection->addFieldToFilter('to_date', array('lt'=>$today));
		
		//$verificationCollection->addFieldToFilter('is_verified', array('eq'=>0));
		foreach($verificationCollection as $expeiredVerifiation){			
			//echo "<pre>".print_r($expeiredVerifiation->getData(), 1)."</pre>";
			$vendorId = $expeiredVerifiation->getData('vendor_id');
			if(isset($vendorId) && $vendorId>0){
				$vendor = $this->_vendorFactory->create()->load($vendorId); 				
				$is_verified_value = $vendor->getData('is_verified');
				if($is_verified_value==1){
				   //echo $vendor->getData('vendor_id').'=>'.$vendor->getData('is_verified')."<br />";			    
				   $vendorData['is_verified'] = 0;          
                   $vendor->addData($vendorData);              
                   $vendor->save();  
                }
                unset($vendor);
			}
			
		}
		
	}
    
}
