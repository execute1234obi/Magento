<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vendor\VendorsVerification\Ui\Component\Listing\Columns\Admin;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Vendor\VendorsVerification\Model\VendorVerification;

/**
 * Class ProductActions
 */
class VerificationActions extends Column
{
    
    const URL_PATH_APPROVE = 'vendorverification/index/approve';
    const URL_PATH_VIEW = 'vendorverification/index/view';
    const URL_PATH_DELETE = 'vendorverification/index/delete';
    const URL_PATH_VIEWORDER = 'sales/order/view';
    

    
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
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

    
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['verification_id'])) {
										
		         //View Detail Action     
		         $item[$name]['view'] = [
                        'href' => $this->urlBuilder->getUrl(
                                self::URL_PATH_VIEW, [
                                    'id' => $item['verification_id']
                                ]
                        ),
                        'label' => __('View Detail')                        
                        
                    ];
                    
               //Approve Action     
               if ($item['is_verified'] != 1 && $item['is_verified'] != VendorVerification::STATUS_EXPIRED  && $item['is_paid'] == 1){
                      $item[$name]['verify'] = [
                          'href' => $this->urlBuilder->getUrl(
                                self::URL_PATH_APPROVE, [
                                    'id' => $item['verification_id']
                                ]
                        ),
                        'label' => __('Approve Verified'),
                        'confirm' => [
                            'title' => __('Approve Verified'),
                            'message' => __('Are you sure you wan\'t to Approve Verification this Seller ?')
                        ]
                    ];
				}
				
				//View Order Action
				if (isset($item['order_id']) && $item['order_id']>0) {                    
                    $item[$name]['vieworder'] = [                        
                            'href' => $this->urlBuilder->getUrl(
                                 self::URL_PATH_VIEWORDER, [
                                    'order_id' => $item['order_id']
                                ]),
                             'target' => '_blank',                                
                            'label' => __('View Order')
                        
                    ];
                    
                }
				
				//Delete Action
				$item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                                self::URL_PATH_DELETE, [
                                    'id' => $item['verification_id']
                                ]
                        ),
                        'label' => __('Reject Entire Verification'),
                        'confirm' => [
                            'title' => __('Reject Entire Verification'),
                            'message' => __('Verification entry will be remove from system and vendor need to go through the whole verification process again.')
                        ]
                        
                    ];
                    
                    
                }
            }
        }

        return $dataSource;
    }
}
