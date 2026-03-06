<?php

namespace Vnecoms\VendorsMembership\Controller\Index;

use Magento\Framework\Exception\NotFoundException;

class Css extends \Magento\Framework\App\Action\Action
{
    /**
     * Display customer wishlist.
     *
     * @return \Magento\Framework\View\Result\Page
     *
     * @throws NotFoundException
     */
    public function execute()
    {
        $this->getResponse()->setHeader('Content-type', 'text/css', true);
        $block = $this->_view->getLayout()->createBlock('Vnecoms\VendorsMembership\Block\Css')->setTemplate('css.phtml');
        $this->getResponse()->setBody($block->toHtml());
    }
}
