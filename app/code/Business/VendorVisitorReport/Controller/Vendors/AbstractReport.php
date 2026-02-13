<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Admin abstract reports controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace Business\VendorVisitorReport\Controller\Vendors;

//use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Controller\AbstractAccount;
use Vnecoms\Vendors\App\Action\Frontend\Context;
use Vnecoms\Vendors\Helper\Data as VendorHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Reports api controller
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
//abstract class AbstractReport extends \Vnecoms\Vendors\Controller\Vendors\Action
abstract class AbstractReport extends \Vnecoms\Vendors\Controller\AbstractAction

{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Vnecoms_Vendors::vendor';

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;    

    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        TimezoneInterface $timezone        
    ) {
        parent::__construct($context);
        //$this->_coreRegistry = $context->getCoreRegsitry();
        $this->_dateFilter = $dateFilter;
        //$this->_config = $context->getConfig();
        //$this->_localeResolver = $context->getLocaleResolver();
        
        $this->_fileFactory = $fileFactory;
        $this->_dateFilter = $dateFilter;
        $this->timezone = $timezone;
    }

    

    /**
     * Retrieve admin session model
     *
     * @return \Vnecoms\Vendors\Model\Session
     */
    protected function _getSession()
    {
        /*if ($this->_vendorSession === null) {
            $this->_vendorSession = $this->_vendorSession ;//$this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class);
        }
        return $this->_vendorSession;*/
        return $this->_vendorSession;
    }

    /**
     * Add report breadcrumbs
     *
     * @return $this
     */
    public function _initAction()
    {
        $this->_view->loadLayout();
        //$this->_addBreadcrumb(__('Reports'), __('Reports'));
        return $this;
    }

    /**
     * Report action init operations
     *
     * @param array|\Magento\Framework\DataObject $blocks
     * @return $this
     */
    public function _initReportAction($blocks)
    {
        if (!is_array($blocks)) {
            $blocks = [$blocks];
        }

        $params = $this->initFilterData();

        foreach ($blocks as $block) {
            if ($block) {
                $block->setPeriodType($params->getData('period_type'));
                $block->setFilterData($params);
            }
        }

        return $this;
    }

    /**
     * Add refresh statistics links
     *
     * @param string $flagCode
     * @param string $refreshCode
     * @return $this
     */
    protected function _showLastExecutionTime($flagCode, $refreshCode)
    {
        $flag = $this->_objectManager->create(\Magento\Reports\Model\Flag::class)
            ->setReportFlagCode($flagCode)
            ->loadSelf();
        $updatedAt = __('Never');
        if ($flag->hasData()) {
            $updatedAt = $this->timezone->formatDate(
                $flag->getLastUpdate(),
                \IntlDateFormatter::MEDIUM,
                true
            );
        }

        $refreshStatsLink = $this->getUrl('reports/report_statistics');
        $directRefreshLink = $this->getUrl('reports/report_statistics/refreshRecent');

        $this->messageManager->addNotice(
            __(
                'Last updated: %1. To refresh last day\'s <a href="%2">statistics</a>, ' .
                'click <a href="#2" data-post="%3">here</a>.',
                $updatedAt,
                $refreshStatsLink,
                str_replace(
                    '"',
                    '&quot;',
                    json_encode(['action' => $directRefreshLink, 'data' => ['code' => $refreshCode]])
                )
            )
        );
        return $this;
    }

    /**
     * Init filter data
     *
     * @return \Magento\Framework\DataObject
     */
    private function initFilterData(): \Magento\Framework\DataObject
    {
        $requestData = $this->backendHelper
            ->prepareFilterString(
                $this->getRequest()->getParam('filter')
            );

        $filterRules = ['from' => $this->_dateFilter, 'to' => $this->_dateFilter];
        $inputFilter = new \Zend_Filter_Input($filterRules, [], $requestData);

        $requestData = $inputFilter->getUnescaped();
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        $requestData['group'] = $this->getRequest()->getParam('group');
        $requestData['website'] = $this->getRequest()->getParam('website');

        $params = new \Magento\Framework\DataObject();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }
        return $params;
    }
}
