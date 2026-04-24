<?php

namespace Custom\VendorRegister\Plugin;

class RegisterPostPlugin
{
    public function beforeExecute(
        \Vnecoms\Vendors\Controller\Seller\RegisterPost $subject
    ) {
        $request = $subject->getRequest();

        // existing vendor data
        $vendorData = $request->getParam('vendor_data') ?? [];

        // 🔥 custom fields from form
        $lat = $request->getParam('vendor_vendor_lat');
        $lng = $request->getParam('vendor_vendor_lng');
        $address = $request->getParam('vendor_address');

        // ✅ main map field (IMPORTANT)
        if (!empty($lat) && !empty($lng)) {
            $vendorData['map'] = $lat . ',' . $lng;
        }

        // optional (only if attributes exist in DB)
        if ($lat) {
            $vendorData['latitude'] = $lat;
        }
        if ($lng) {
            $vendorData['longitude'] = $lng;
        }
        if ($address) {
            $vendorData['address'] = $address;
        }

        // overwrite vendor_data
        $request->setParam('vendor_data', $vendorData);

        // 🔍 debug (optional)
        // file_put_contents(BP . '/var/log/vendor_map.log', print_r($vendorData, true));
    }
}