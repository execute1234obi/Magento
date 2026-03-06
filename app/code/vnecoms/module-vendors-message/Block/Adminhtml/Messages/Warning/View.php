<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsMessage\Block\Adminhtml\Messages\Warning;

/**
 * Vendor Notifications block
 */
class View extends \Magento\Backend\Block\Template
{

    /**
     * @var \Vnecoms\VendorsMessage\Model\ResourceModel\Pattern\CollectionFactory
     */
    protected $_pattern;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Vnecoms\VendorsMessage\Model\ResourceModel\Pattern\CollectionFactory $pattern,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_pattern = $pattern;
    }

    /**
     * Get message
     *
     * @return \Vnecoms\VendorsMessage\Model\Message
     */
    public function getMessage(){

        return $this->_coreRegistry->registry('message');
    }

    /**
     * Get message
     *
     * @return \Vnecoms\VendorsMessage\Model\Message\Detail
     */
    public function getDetailMessage(){
        return $this->_coreRegistry->registry('detail_message');
    }

    /**
     * Get Back URL
     *
     * @return string
     */
    public function getBackUrl(){
        return $this->getUrl("vendors/message_warning");
    }


    /**
     * Get Delete URL
     *
     * @return string
     */
    public function getDeleteUrl(){
        return $this->getUrl("vendors/message_warning/delete",array("id"=>$this->getWarning()->getId()));
    }



    /**
     * Get Block URL
     *
     * @return string
     */
    public function getWarning(){
        return $this->_coreRegistry->registry('warning');
    }


    /**
     * Get Back URL
     *
     * @return string
     */
    public function getBlockUrl(){
        return $this->getUrl("vendors/message_warning/block",array("id"=>$this->getWarning()->getId()));
    }

    /**
     * show waring message
     * @param $content
     * @return mixed
     */
    public function processContentMessageWarning($content){
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
