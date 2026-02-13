<?php
namespace Vendor\VendorsVerification\Ui\Component\Listing\Columns\Admin;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Vendor\VendorsVerification\Model\VendorVerification;

class VerificationActions extends Column
{
    const URL_PATH_APPROVE       = 'vendorverification/index/approve';
    const URL_PATH_VIEW          = 'vendorverification/index/view';
    const URL_PATH_REJECT_ENTIRE = 'vendorverification/index/rejectEntire';
    const URL_PATH_VIEWORDER     = 'sales/order/view';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (!isset($item['verification_id'])) {
                    continue;
                }

                $name = $this->getData('name');

                /** View Detail */
                $item[$name]['view'] = [
                    'href'  => $this->urlBuilder->getUrl(
                        self::URL_PATH_VIEW,
                        ['id' => $item['verification_id']]
                    ),
                    'label' => __('View Detail')
                ];

                /** Approve */
                if (
                    $item['is_verified'] != 1 &&
                    $item['is_verified'] != VendorVerification::STATUS_EXPIRED &&
                    $item['is_paid'] == 1
                ) {
                    $item[$name]['verify'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::URL_PATH_APPROVE,
                            ['id' => $item['verification_id']]
                        ),
                        'label' => __('Approve Verified'),
                        'confirm' => [
                            'title'   => __('Approve Verified'),
                            'message' => __('Are you sure you want to approve this seller verification?')
                        ]
                    ];
                }

                /** View Order */
                if (!empty($item['order_id'])) {
                    $item[$name]['vieworder'] = [
                        'href'   => $this->urlBuilder->getUrl(
                            self::URL_PATH_VIEWORDER,
                            ['order_id' => $item['order_id']]
                        ),
                        'target' => '_blank',
                        'label'  => __('View Order')
                    ];
                }

                /** Reject Entire Verification (NO DELETE) */
                $item[$name]['reject_entire'] = [
                    'href' => $this->urlBuilder->getUrl(
                        self::URL_PATH_REJECT_ENTIRE,
                        ['id' => $item['verification_id']]
                    ),
                    'label' => __('Reject Entire Verification'),
                    'confirm' => [
                        'title'   => __('Reject Entire Verification'),
                        'message' => __(
                            'This will reject the entire verification. Vendor will be required to resubmit the verification again. Are you sure?'
                        )
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
