<?php
namespace Vnecoms\VendorsMessage\Model\Message;

class Detail extends \Magento\Framework\Model\AbstractModel
{

    const ENTITY = 'vendor_message_detail';
    
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'vendor_message_detail';
    
    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'vendor_message_detail';

    /**
     * @var AttachmentFactory
     */
    protected $attachmentFactory;
    
    /**
     * Initialize customer model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Vnecoms\VendorsMessage\Model\ResourceModel\Message\Detail');
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Vnecoms\VendorsMessage\Model\Message\AttachmentFactory $attachmentFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->attachmentFactory = $attachmentFactory;
    }

    /**
     * Get Attachment Collection
     *
     * @return \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Attachment\Collection
     */
    public function getAttachmentCollection()
    {
        if(!$this->getData('attachment_collection')){
            $collection = $this->attachmentFactory->create()->getCollection();
            $collection->addFieldToFilter('detail_id', $this->getId())->setOrder('attachment_id','ASC');
            $this->setData('attachment_collection', $collection);
        }
        return $this->getData('attachment_collection');
    }

    /**
     * Save attachment files
     *
     * @return $this
     */
    public function afterSave()
    {
        parent::afterSave();
        $attachments = $this->getAttachments();
        if (!is_array($attachments)) return $this;
        foreach($attachments as $attachment){
            $attachmentObj = $this->attachmentFactory->create();
            $attachmentObj->setData([
                'detail_id' => $this->getId(),
                'file_name' => $attachment,
            ])->save();
        }
    }

    /**
     * @return string
     */
    public function getAttachments()
    {
        return $this->getData('attachments');
    }

    /**
     * @param $t
     * @return $this
     */
    public function setAttachment($t)
    {
        return $this->setData('attachments', $t);
    }
}
