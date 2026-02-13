<?php
namespace Vendor\VendorsVerification\Service;

use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Vendor\VendorsVerification\Helper\Data;
use Vendor\VendorsVerification\Model\Source\Status;
use Vendor\VendorsVerification\Model\Source\InfoGroup;
use Magento\Framework\App\ResourceConnection;
use Vnecoms\Vendors\Model\Vendor;

class VerificationUpdateService
{
    protected $verificationFactory;
    protected $verificationInfoFactory;
    protected $helper;
    protected $resource;

    public function __construct(
        VendorVerificationFactory $verificationFactory,
        VerificationInfoFactory $verificationInfoFactory,
        Data $helper,
        ResourceConnection $resource
    ) {
        $this->verificationFactory = $verificationFactory;
        $this->verificationInfoFactory = $verificationInfoFactory;
        $this->helper = $helper;
        $this->resource = $resource;
    }

    public function update(
        Vendor $vendor,
        int $verificationId,
        int $typeId,
        int $detailId,
        array $groupData,
        bool $resetVerification = true
    ) {
        $connection = $this->resource->getConnection();
        $connection->beginTransaction();

        try {
            $verification = $this->verificationFactory->create()->load($verificationId);
            $verificationInfo = $this->verificationInfoFactory->create()->load($detailId);

            // Save verification snapshot
            $verificationInfo->setVendorData($this->helper->arrayToJson($groupData));
            $verificationInfo->setApproval(0);
            $verificationInfo->setStatus(Status::VENDOR_VERIFICATION_STATUS_PENDING);
            $verificationInfo->save();

            // Reset verification if needed
            if ($resetVerification) {
                $verification->setStatus(Status::VENDOR_VERIFICATION_STATUS_PENDING);
                $verification->setApproval(0);
                $verification->save();
            }

            // Sync vendor table
            $this->syncVendorFromVerification($vendor, $typeId, $groupData);
            $vendor->save();

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    private function syncVendorFromVerification(Vendor $vendor, int $typeId, array $data)
    {
         switch ($typeId) {
            case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION:
                $vendor->setData('b_name', $data['business-name'] ?? null);
                $vendor->setData('business_descriptions', $data['business-description'] ?? null);
                $vendor->setData('business_type', $data['business-type'] ?? null);
                $vendor->setData('website', $data['business-website'] ?? null);
                $vendor->setData('country_id', $data['country_id'] ?? null);
                $vendor->setData('b_ph', $data['business-phone'] ?? null);
                $vendor->setData('b_email', $data['business-email'] ?? null);
                break;
             /** ✅ BUSINESS CONTACT SYNC */
        case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_CONTACT:
            $vendor->setData('c_name', $data['contact_name'] ?? null);
            $vendor->setData('contact_phone', $data['contact_phone'] ?? null);
            $vendor->setData('contact_email', $data['contact_email'] ?? null);
            $vendor->setData('country_code', $data['country_code'] ?? null);
            break;
              /** ================= BUSINESS ADDRESS ✅ ================= */
        case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_ADDRESS:
            $vendor->setData('street', $data['street'] ?? null);
            $vendor->setData('city', $data['city'] ?? null);
            $vendor->setData('region', $data['state'] ?? null);
            $vendor->setData('postcode', $data['postcode'] ?? null);
            $vendor->setData('map', $data['map'] ?? null);
            break;
         /** ================= CERTIFICATES & DOCUMENTS ✅ ================= */
        case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS:

            // Single certificate
            if (!empty($data['certificate'])) {
                $vendor->setData('registration_certificate', $data['certificate']);
            }

            // Multiple documents (comma separated)
            if (!empty($data['documents'])) {
                $vendor->setData('additional_documents', $data['documents']);
            }

            break;
        }
        
    
    }
}
