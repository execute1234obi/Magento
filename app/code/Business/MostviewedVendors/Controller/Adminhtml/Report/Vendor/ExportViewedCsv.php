<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Business\MostviewedVendors\Controller\Adminhtml\Report\Vendor;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportViewedCsv extends \Business\MostviewedVendors\Controller\Adminhtml\Report\Vendor
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Business_MostviewedVendors::viewed';

    /**
     * Export vendors most viewed report to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'gcc_vesdors_mostviewed.csv';
        $grid = $this->_view->getLayout()->createBlock(\Business\MostviewedVendors\Block\Adminhtml\Vendor\Viewed\Grid::class);
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getCsvFile(), DirectoryList::VAR_DIR);
    }
}
