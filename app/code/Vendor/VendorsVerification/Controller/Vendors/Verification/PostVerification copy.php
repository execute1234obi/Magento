<?php

namespace Vendor\VendorsVerification\Controller\Vendors\Verification;

use Vnecoms\Vendors\App\Action\Context;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\View\Result\PageFactory;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class PostVerification extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    protected $_aclResource = 'Vnecoms_Vendors::vendorverification_action_save';

    /** @var VendorSession */
    protected $_vendorSession;

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var DataObjectHelper */
    protected $dataObjectHelper;

    /** @var VendorVerificationFactory */
    protected $vendorsVerificationFactory;

    /** @var VerificationInfoFactory */
    protected $verificationInfoFactory;

    /** @var CustomerRepositoryInterface */
    protected $_customerRepositoryInterface;

    /** @var \Vendor\VendorsVerification\Helper\Data */
    protected $helper;

    /** @var \Vnecoms\Vendors\Helper\Data */
    protected $_vendorHelper;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var \Magento\Store\Api\StoreRepositoryInterface */
    protected $storeRepository;

    /** @var TimezoneInterface */
    protected $timezoneInterface;

    /** @var DateTime */
    protected $date;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $_messageManager;

    /** @var \Magento\Framework\Registry */
    protected $_coreRegistry;

    public function __construct(
        Context $context,
        VendorSession $vendorSession,
        PageFactory $resultPageFactory,
        CustomerRepositoryInterface $customerRepository,
        VendorVerificationFactory $vendorsVerificationFactory,
        VerificationInfoFactory $verificationInfoFactory,
        TimezoneInterface $timezoneInterface,
        DateTime $date,
        \Magento\Framework\Registry $coreRegistry,
        \Vnecoms\Vendors\Helper\Data $vendorHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Vendor\VendorsVerification\Helper\Data $helper,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->_vendorSession = $vendorSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_coreRegistry = $coreRegistry;
        $this->_vendorHelper = $vendorHelper;
        $this->vendorsVerificationFactory = $vendorsVerificationFactory;
        $this->verificationInfoFactory = $verificationInfoFactory;
        $this->_customerRepositoryInterface = $customerRepository;
        $this->storeRepository = $storeRepository;
        $this->storeManager = $storeManager;
        $this->timezoneInterface = $timezoneInterface;
        $this->date = $date;
        $this->helper = $helper;
        $this->_messageManager = $context->getMessageManager();

        parent::__construct($context);
    }

    public function execute()
    {
        try {
    $vendor = $this->_vendorSession->getVendor();
    if (!$vendor || !$vendor->getId()) {
        $this->_messageManager->addError(__('Vendor not found.'));
        return $this->_redirect('vendorverification/verification/new/');
    }

    // ... verification saving logic ...
    $vendorCustomer = $vendor->getCustomer();

$vendorVerification = $this->vendorsVerificationFactory->create();
$vendorVerification->setVendorId($vendor->getId());
$vendorVerification->setCustomerId($vendorCustomer->getId());
$vendorVerification->setIncId($incId);
$vendorVerification->setWebsiteId($vendorCustomer->getWebsiteId());
$vendorVerification->setStoreId($this->storeManager->getStore()->getId());
$vendorVerification->setCountry($vendor->getCountryId());
$vendorVerification->setMonthsBooked($verificationMonths);
$vendorVerification->setAmount($verificationFees);
$vendorVerification->setOrderId(null);
$vendorVerification->setIsPaid(0);
$vendorVerification->setIsActive(1);
$vendorVerification->setIsVerified(0);
$vendorVerification->setStatus(
    \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS_PENDING
);
$vendorVerification->setCreatedAt($this->date->date());
$vendorVerification->save();

$verificationId = $vendorVerification->getVerificationId();

$data = $this->getRequest()->getPostValue();

$vendorInfo = [
    'company'               => $vendor->getCompany(),
    'business_name'         => $vendor->getData('b_name'),
    'business_description'  => $vendor->getData('business_descriptions'),
    'website'               => $vendor->getData('website'),
    'business_type'         => $vendor->getData('business_type'),
    'business_category'     => $vendor->getData('business_category'),
    'country_id'            => $vendor->getCountryId(),
    'country_code'          => $vendor->getData('c_code'),
    'business_phone'        => $vendor->getData('b_ph'),
    'business_email'        => $vendor->getData('b_email'),
];

$this->saveVerificationInfo(
    $verificationId,
    \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_VENDOR_INFORMATION,
    $vendorInfo
);

$vendorAddress = [
    'street'    => $vendor->getStreet(),
    'city'      => $vendor->getCity(),
    'state'     => $vendor->getRegion(),
    'state_alt' => $vendor->getData('b_state'),
    'postcode'  => $vendor->getPostcode(),
    'map'       => $vendor->getData('map'),
];

$this->saveVerificationInfo(
    $verificationId,
    \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_VENDOR_ADDRESS,
    $vendorAddress
);
$vendorContact = [
    'contact_name'  => $vendor->getData('c_name'),
    'country_code'  => $vendor->getData('country_code'),
    'phone'         => $vendor->getData('contact_phone'),
    'email'         => $vendor->getData('contact_email'),
];

$this->saveVerificationInfo(
    $verificationId,
    \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_VENDOR_CONTACT,
    $vendorContact
);
$vendorDocs = [
    'certificate' => $vendor->getData('certificate'),
    'logo'        => $vendor->getData('upload_logo'),
];

$this->saveVerificationInfo(
    $verificationId,
    \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS,
    $vendorDocs
);
$this->saveVerificationInfo(
    $verificationId,
    \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_ACTIVITY,
    null
);


    // After successful saving, redirect to the "check out / view verification" page
    $queryParams = [
        'id' => $verificationId];		            
    return $this->_redirect(
        'vendorverification/quotes/addtocart',
        ['_current' => true, '_use_rewrite' => true, '_query' => $queryParams]
    );
    
} catch (\Magento\Framework\Exception\LocalizedException $e) {
    $this->_messageManager->addError($e->getMessage());
} catch (\Exception $e) {
    $this->_messageManager->addError($e->getMessage());                                
}

// Fallback redirect only if exception occurs
return $this->_redirect('vendorverification/verification/new/');
    }
}
