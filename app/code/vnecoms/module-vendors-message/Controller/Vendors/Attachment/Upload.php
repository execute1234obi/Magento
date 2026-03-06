<?php

namespace Vnecoms\VendorsMessage\Controller\Vendors\Attachment;

use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Vnecoms\VendorsMessage\Helper\Data
     */
    protected $messageHelper;
    
    /**
     * @param \Vnecoms\Vendors\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Vnecoms\VendorsMessage\Helper\Data $messageHelper
     */
    public function __construct(
        \Vnecoms\Vendors\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Vnecoms\VendorsMessage\Helper\Data $messageHelper
    ) {
        parent::__construct($context);
        $this->messageHelper = $messageHelper;
        $this->_localeDate = $localeDate;
        $this->resultRawFactory = $resultRawFactory;
    }
    
    /**
     * @return void
     */
    public function execute()
    {
        try {
            $uploader = $this->_objectManager->create(
                'Magento\MediaStorage\Model\File\Uploader',
                ['fileId' => 'image']
            );
            $allowedExtensions = $this->messageHelper->getAllowedExtensions();
            $extensions = explode(',', $allowedExtensions);
            if (is_array($extensions) && count($extensions) > 0) $uploader->setAllowedExtensions($extensions);
            
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                ->getDirectoryRead(DirectoryList::MEDIA);
    
            $path = 'ves_vendorsmessage';
    
            $result = $uploader->save($mediaDirectory->getAbsolutePath(
                $path
            ));

            $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
            $result['url'] = $storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path.'/' . $result['file'];
            
            $result['last_modify'] = $this->_localeDate->formatDate(
                date("Y-m-d H:i:s", filemtime($result['path'])),
                \IntlDateFormatter::SHORT,
                true
            );
            unset($result['tmp_name']);
            unset($result['path']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
    
        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }
}
