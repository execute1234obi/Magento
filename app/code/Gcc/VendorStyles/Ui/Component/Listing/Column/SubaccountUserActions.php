<?php

namespace Gcc\VendorStyles\Ui\Component\Listing\Column;

class SubaccountUserActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Add Edit/Delete actions for each sub account user row.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (empty($item['customer_id'])) {
                continue;
            }

            $userId = (int)$item['customer_id'];
            $name = trim(($item['firstname'] ?? '') . ' ' . ($item['lastname'] ?? ''));

            $item[$this->getData('name')] = [
                'edit' => [
                    'href' => $this->urlBuilder->getUrl(
                        'subaccount/user/edit',
                        ['user_id' => $userId]
                    ),
                    'label' => __('Edit'),
                ],
                'delete' => [
                    'href' => $this->urlBuilder->getUrl(
                        'subaccount/user/delete',
                        ['user_id' => $userId]
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete User'),
                        'message' => __('Are you sure you want to delete "%1"?', $name ?: __('this user')),
                    ],
                ],
            ];
        }

        return $dataSource;
    }
}
