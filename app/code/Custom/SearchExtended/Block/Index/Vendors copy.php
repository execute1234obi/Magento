<?php
namespace Custom\SearchExtended\Block\Index;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\Http;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Service\IndexService;
use Mirasvit\Search\Block\Index\Base;
use Vnecoms\VendorsPage\Helper\Data as VendorsPageHelper;
use Business\VendorsVerification\Helper\Data as VendorsVerificationHelper;
use Mirasvit\Search\Api\Data\IndexInterfaceFactory;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\App\RequestInterface;

class Vendors extends Base
{
    protected $_template = 'Custom_SearchExtended::index/vendors.phtml';

    private $indexService;
    private $objectManager;
    private $helperData;
    private $vendorsVerificationHelper;
    private $request;
    private $indexFactory;
    private $registry;
//old code
//    public function __construct(
//     IndexService $indexService,
//     ObjectManagerInterface $objectManager,
//     \Vnecoms\VendorsPage\Helper\Data $helperData,
//     \Business\VendorsVerification\Helper\Data $vendorsVerificationHelper,
//     \Magento\Framework\App\Request\Http $request,
//     IndexInterfaceFactory $indexFactory,
//     Registry $registry,
//     Context $context,
//     array $data = []
// ) {
//     $this->indexService = $indexService;
//     $this->objectManager = $objectManager;
//     $this->helperData = $helperData;
//     $this->vendorsVerificationHelper = $vendorsVerificationHelper;
//     $this->request = $request;
//     $this->indexFactory = $indexFactory;
//     $this->registry = $registry;

//     parent::__construct(
//         $indexService,
//         $objectManager,
//         $context,
//         $indexFactory,
//         $registry,
//         $data
//     );
// }
//end old code
 // All dependencies are now handled with property promotion
    public function __construct(
        Context $context,
        IndexService $indexService,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        IndexInterfaceFactory $indexFactory,
        RequestInterface $request,
        VendorsPageHelper $helperData,
        VendorsVerificationHelper $vendorsVerificationHelper,
        VendorCollectionFactory $vendorCollectionFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        array $data = []
    ) {
        $this->request = $request;
        $this->helperData = $helperData;
        $this->vendorsVerificationHelper = $vendorsVerificationHelper;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;

        // Parent constructor call, passing the correct dependencies
        parent::__construct(
            $indexService,
            $objectManager,
            $context,
            $indexFactory,
            $registry,
            $data
        );
    }
    
      private VendorCollectionFactory $vendorCollectionFactory;
    private AttributeCollectionFactory $attributeCollectionFactory;
     /**
     * This is the method your vendors.phtml template expects.
     * @return \Magento\Framework\Data\Collection
     */
    public function getSearchCollection()
    {
        $collection = $this->vendorCollectionFactory->create();
        $query = $this->request->getParam('q');

        $collection->addAttributeToSelect(['vendor_id', 'c_name', 'upload_logo', 'business_descriptions', 'company', 'b_name']);

        if ($query) {
            $collection->addAttributeToFilter([
                ['attribute' => 'c_name', 'like' => '%' . $query . '%'],
                ['attribute' => 'business_descriptions', 'like' => '%' . $query . '%'],
                ['attribute' => 'b_name', 'like' => '%' . $query . '%']
            ]);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function stripTags($data, $allowableTags = null, $allowHtmlEntities = false)
    {
        $data = preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/', '$1', $data);
        $data = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $data);
        $data = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $data);
        $data = str_replace('>', '> ', $data); #adding space after tag <h1>..</h1><p>...</p>

        return parent::stripTags($data, $allowableTags, $allowHtmlEntities);
    }

    /**
     * Truncate text
     *
     * @param string $string
     *
     * @return string
     */
    public function truncate($string)
    {
        if (strlen($string) > 512) {
            $string = strip_tags($string);
            $string = substr($string, 0, 512) . '...';
        }

        return $string;
    }

    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Return pager html for current collection.
     * @return string
     */
    public function getPager()
    {
        $pager = $this->getChildBlock('pager');

        if (!$pager) {
            return '';
        }

        if (!$pager->getCollection()) {			
			
            $pager->setCollection($this->getCollection());
        }

        return $pager->toHtml();
    }

    /**
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        return $this->indexService->getSearchCollection($this->getIndex());
    }
    
    public function getHelper(){
		return $this->helperData;
	}
	
	public function isVerifiedVendor($vendorId){
		return $this->vendorsVerificationHelper->IsVerifiedVendor($vendorId);
	}
	
	 public function getIndexId()
    {   
		$indexId =  $this->request->getParam('index');
        return (isset($indexId))? $this->request->getParam('index'):0;
    }
}
