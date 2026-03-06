<?php
/**
 * Copyright © 2018 Vnecoms. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace Vnecoms\VendorsMessage\Model\Message;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class with class map capability
 *
 * ...
 */
class Attachment extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'message_attachment';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'attachment';


    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vnecoms\VendorsMessage\Model\ResourceModel\Message\Attachment');
    }

    public function __construct (
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Attachment $resource,
        \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Attachment\Collection $resourceCollection,
        \Magento\Framework\Filesystem $filesystem,
        array $data = []
    ) {
        $this->filesystem = $filesystem;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get file path
     *
     * @return string
     */
    public function getFilePath() {
        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
        ->getAbsolutePath('ves_vendorsmessage/'.trim($this->getFileName(),'/'));
    }

    /**
     * Get file content
     *
     * @return string
     */
    public function getFileContent() {
        return file_get_contents($this->getFilePath());
    }

    /**
     * Get Mime Type
     *
     * @return string
     */
    public function getMimeType() {
        return mime_content_type($this->getFilePath());
    }

    /**
     * Get file name
     *
     * @return string
     */
    public function getName() {
        $name = explode("/", $this->getFileName());
        $name = end($name);
        return $name;
    }
}
