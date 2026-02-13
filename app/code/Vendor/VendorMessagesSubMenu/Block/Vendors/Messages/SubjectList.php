<?php
namespace Vendor\VendorMessagesSubMenu\Block\Vendors\Messages;

use Magento\Framework\View\Element\Template;
use Vnecoms\Vendors\Model\Session as VendorSession;
//use Vendor\VendorMessages\Model\ResourceModel\Subject\CollectionFactory as SubjectCollectionFactory;
use Vendor\VendorMessages\Model\ResourceModel\Subject\CollectionFactory as SubjectCollectionFactory;

class SubjectList extends Template
{
    /**
     * @var VendorSession
     */
    protected $vendorSession;

    /**
     * @var SubjectCollectionFactory
     */
    protected $subjectCollectionFactory;

    public function __construct(
    Template\Context $context,
    VendorSession $vendorSession,
    SubjectCollectionFactory $subjectCollectionFactory,
    array $data = []
) {
    $this->vendorSession = $vendorSession;
    $this->subjectCollectionFactory = $subjectCollectionFactory;
    parent::__construct($context, $data);
}


    
    /**
     * Get vendor message subject collection
     */
    public function getSubjectCollection()
    {
        
        $vendor = $this->vendorSession->getVendor();
        if (!$vendor || !$vendor->getId()) {
            return null;
        }

        $collection = $this->subjectCollectionFactory->create();
        $collection->addFieldToFilter('vendor_id', $vendor->getId());
        $collection->setOrder('created_at', 'DESC');

        return $collection;
    }

    /**
     * Subject URL
     */
    public function getSubjectUrl($subjectId)
    {
        return $this->getUrl('vendormessages/message/view', ['id' => $subjectId]);
    }
}
