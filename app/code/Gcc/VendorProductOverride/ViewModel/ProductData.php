<?php
namespace Gcc\VendorProductOverride\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Vnecoms\Vendors\Model\VendorFactory;
use Magento\Directory\Model\CountryFactory;

class ProductData implements ArgumentInterface
{
    protected $vendorFactory;
    protected $countryFactory;

    public function __construct(
        VendorFactory $vendorFactory,
        CountryFactory $countryFactory
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->countryFactory = $countryFactory;
    }

    public function getVendorData($product)
    {
        $vendorId = (int)$product->getVendorId();

        if (!$vendorId) {
            return [];
        }

        try {
            $vendor = $this->vendorFactory->create()->load($vendorId);

            if (!$vendor->getId()) {
                return [];
            }

            $countryCode = $vendor->getCountryId();
            $country = '';

            if ($countryCode) {
                $country = $this->countryFactory->create()
                    ->loadByCode($countryCode)
                    ->getName();
            }

            return [
                'name' => $vendor->getData('b_name'),
                'country' => $country,
                'country_code' => $countryCode ? strtolower($countryCode) : ''
            ];

        } catch (\Exception $e) {
            return [];
        }
    }
}