<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Business\VisitorcountryReport\Controller\Adminhtml\Report\Country;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportViewedExcel extends \Business\VisitorcountryReport\Controller\Adminhtml\Report\Countryvisited
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Business_VisitorcountryReport::downloads';

    /**
     * Export vendors most viewed report to XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {               
        $fileName = 'gcc_visitor_country.xml';
        $grid = $this->_view->getLayout()->createBlock(\Business\VisitorcountryReport\Block\Adminhtml\Visitorcountry\Visited\Grid::class);
        $this->_initReportAction($grid);
        return $this->_fileFactory->create(
            $fileName,
            $grid->getExcelFile($fileName),
            DirectoryList::VAR_DIR
        );
        
    }
}
