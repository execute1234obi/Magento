<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Priority
 */
class Content extends Column
{

    /**
     * @var \Vnecoms\VendorsMessage\Model\ResourceModel\Pattern\CollectionFactory
     */
    protected $_pattern;
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Vnecoms\VendorsMessage\Model\ResourceModel\Pattern\CollectionFactory $pattern,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_pattern = $pattern;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $content = $item[$this->getData('name')];
                    $content = $this->_processContentMessageWarning($content);
                    $item[$this->getData('name')] = $content;
                }
            }
        }

        return $dataSource;
    }

    /**
     * show waring message
     * @param $content
     * @return mixed
     */
    protected function _processContentMessageWarning($content){
        $patterns = $this->_pattern->create()->addFieldToFilter("action",1)->addFieldToFilter("status",1);
        $warning = ["flag"=>false];
        foreach ($patterns as $pattern){
            // var_dump($message);exit;
            if(preg_match("/".$pattern->getPattern()."/is",$content)){
                $content =  preg_replace("/".$pattern->getPattern()."/is","<span class='vnecoms-message-warning'>$0</span>",$content);
            }
        }
        return $content;
    }
}
