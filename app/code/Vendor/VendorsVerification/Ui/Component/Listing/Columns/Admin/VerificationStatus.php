<?php
namespace Vendor\VendorsVerification\Ui\Component\Listing\Columns\Admin;

use Magento\Ui\Component\Listing\Columns\Column;

class VerificationStatus extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {

            $status     = (int)($item['status'] ?? 0);
            $isVerified = (int)($item['is_verified'] ?? 0);

            if ($status === 5) {
                $item[$this->getData('name')] = __('Entire Registration Rejected');
            } else {
                $item[$this->getData('name')] =
                    ($isVerified === 1)
                        ? __('Verified')
                        : __('Not Verified');
            }
        }

        return $dataSource;
    }
}
