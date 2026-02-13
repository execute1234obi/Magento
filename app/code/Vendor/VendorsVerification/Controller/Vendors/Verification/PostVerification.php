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
            /* ================= Vendor Check ================= */
            $vendor = $this->_vendorSession->getVendor();
            if (!$vendor || !$vendor->getId()) {
                $this->messageManager->addError(__('Vendor not found.'));
                return $this->_redirect('vendorverification/verification/new');
            }

            $vendorId = (int)$vendor->getId();
            $vendorCustomer = $vendor->getCustomer();
            $vendorCountry = $vendor->getCountryId();

            if (!$vendorCountry) {
                
                $this->messageManager->addError(__('Vendor country is not set.'));
                return $this->_redirect('vendorverification/verification/new');
            }

            /* ================= Config Values ================= */
            $feesConfig = $this->helper->getConfig(
                'Vendor_vendorverification/config/fees',
                $this->storeManager->getStore()->getCode()
            );

            $months = (int)$this->helper->getConfig(
                'Vendor_vendorverification/config/months_duration',
                $this->storeManager->getStore()->getCode()
            );

            if ($months <= 0) {
                $this->messageManager->addError(__('Verification duration not configured.'));
                return $this->_redirect('vendorverification/verification/new');
            }

            /* ================= Fees Calculation ================= */
            $verificationFees = 0;
            if ($feesConfig) {
                $feesArr = $this->helper->jsonToArray($feesConfig);
                foreach ($feesArr as $row) {
                    if (isset($row['countrycode']) && strtoupper($row['countrycode']) === strtoupper($vendorCountry)) {
                        $verificationFees = (float)$row['fees'];
                        break;
                    }
                }
            }

            if ($verificationFees <= 0) {
                $this->messageManager->addError(__('Verification fees not available.'));
                return $this->_redirect('vendorverification/verification/new');
            }

            /* ================= Already Applied Check ================= */
            if ($this->helper->isAlreadyApplied($vendorId)) {
                $this->messageManager->addError(__('You have already applied for verification.'));
                return $this->_redirect('vendors');
            }

            /* ================= Increment ID Generate (FIXED) ================= */
            $collection = $this->vendorsVerificationFactory->create()->getCollection();
            $collection->setOrder('verification_id', 'DESC');
            $lastItem = $collection->getFirstItem();

            if ($lastItem && $lastItem->getVerificationId()) {
                $nextId = (int)$lastItem->getVerificationId() + 1;
                $incId = 'VRFY' . sprintf('%05d', $nextId);
            } else {
                $incId = 'VRFY00001';
            }

            /* ================= Main Verification Save ================= */
            $vendorVerification = $this->vendorsVerificationFactory->create();
            $vendorVerification->setVendorId($vendorId);
            $vendorVerification->setCustomerId($vendorCustomer->getId());
            $vendorVerification->setIncId($incId);
            $vendorVerification->setWebsiteId($vendorCustomer->getWebsiteId());
            $vendorVerification->setStoreId($this->storeManager->getStore()->getId());
            $vendorVerification->setMonthsBooked($months);
            $vendorVerification->setCountry($vendorCountry);
            $vendorVerification->setAmount($verificationFees);
            $vendorVerification->setIsPaid(0);
            $vendorVerification->setIsActive(1);
            $vendorVerification->setIsVerified(0);
            $vendorVerification->setStatus(
                \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS_PENDING
            );
            $vendorVerification->setCreatedAt($this->date->date());
            $vendorVerification->save();

            $verificationId = (int)$vendorVerification->getVerificationId();

            /* ================= POST DATA ================= */
            $data = $this->getRequest()->getPostValue();

            /* ================= Vendor Info ================= */
            $vendorInfo = [
                'company'              => $vendor->getCompany(),
                'business_name'        => $vendor->getData('b_name'),
                'business_description' => $vendor->getData('business_descriptions'),
                'website'              => $vendor->getData('website'),
                'business_type'        => $vendor->getData('business_type'),
                'business_category'    => $vendor->getData('business_category'),
                'country_id'           => $vendor->getCountryId(),
                'country_code'         => $vendor->getData('c_code'),
                'business_phone'       => $vendor->getData('b_ph'),
                'business_email'       => $vendor->getData('b_email'),
            ];

            $this->saveInfoGroup(
                $verificationId,
                \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION,
                $vendorInfo
            );

            /* ================= Address ================= */
            $vendorAddress = [
                'street'    => $vendor->getStreet(),
                'city'      => $vendor->getCity(),
                'state'     => $vendor->getRegion(),
                'state_alt' => $vendor->getData('b_state'),
                'postcode'  => $vendor->getPostcode(),
                'map'       => $vendor->getData('map'),
            ];

            $this->saveInfoGroup(
                $verificationId,
                \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_ADDRESS,
                $vendorAddress
            );

            /* ================= Contact ================= */
            $vendorContact = [
                'contact_name'  => $vendor->getData('c_name'),
                'country_code'  => $vendor->getData('country_code'),
                'phone'         => $vendor->getData('contact_phone'),
                'email'         => $vendor->getData('contact_email'),
            ];

            $this->saveInfoGroup(
                $verificationId,
                \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_CONTACT,
                $vendorContact
            );

            /* ================= Certificates ================= */
            $docs = [
                'certificate' => $data['vendorregistcert'][0] ?? '',
                'documents'   => $data['vendoradditionaldocs'] ?? ''
            ];

            $this->saveInfoGroup(
                $verificationId,
                \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS,
                $docs
            );

            /* ================= Redirect to Add To Cart ================= */
          $queryParams = ['id' => $verificationId];

/* cleanup (optional but safe, same as old code) */
 /** vendor Verification  Data : Vendor Activity **/
            $dataGroupId =  \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_ACTIVITY;
            $verificationInfo = $this->verificationInfoFactory->create();
            $verificationInfo->setVerificationId($verificationId);
            $verificationInfo->setDatagroupId($dataGroupId);
            $verificationInfo->setVendorData(null);
            $verificationInfo->setApproval(0);
            $verificationInfo->setStatus(\Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS_PENDING);        
            $verificationInfo->setCreatedAt($this->date->date());
            $verificationInfo->save();
            unset($verificationInfo);
            
            $queryParams = ['id' => $verificationId];		            
return $this->_redirect('*/quotes/addtocart', ['id' => $verificationId]);



        } catch (\Exception $e) {
            // Error aane par agar main row create ho gayi ho toh rollback logic yahan dal sakte hain
            $this->messageManager->addError($e->getMessage());
            return $this->_redirect('vendorverification/verification/new');
        }
    }

    protected function saveInfoGroup($verificationId, $dataGroupId, $data)
    {
        $verificationInfo = $this->verificationInfoFactory->create();

        $verificationInfo->setVerificationId($verificationId);
        $verificationInfo->setDatagroupId($dataGroupId);

        if ($data !== null) {
            $verificationInfo->setVendorData(json_encode($data));
        } else {
            $verificationInfo->setVendorData(null);
        }

        $verificationInfo->setApproval(0);
        $verificationInfo->setStatus(
            \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS_PENDING
        );
        $verificationInfo->setCreatedAt($this->date->date());
        $verificationInfo->save();
    }
}