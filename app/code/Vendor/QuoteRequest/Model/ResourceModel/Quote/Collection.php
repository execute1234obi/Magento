<?php
namespace Vendor\QuoteRequest\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
//use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
//class Collection extends SearchResult
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'quote_id';
    protected function _construct()
    {
        $this->_init(
            \Vendor\QuoteRequest\Model\Quote::class,
            \Vendor\QuoteRequest\Model\ResourceModel\Quote::class
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        // ✅ Only Quote-level joins allowed
         //die("Collection reached");
       // Customer Name Join
    $this->getSelect()->joinLeft(
        ['ce' => $this->getTable('customer_entity')],
        'main_table.customer_id = ce.entity_id',
        ['customer_name' => new \Zend_Db_Expr("CONCAT(ce.firstname, ' ', ce.lastname)")]
    );

    // Vendor Name Join
    $this->getSelect()->joinLeft(
        ['ve' => $this->getTable('ves_vendor_entity')],
        'main_table.vendor_id = ve.entity_id',
        ['vendor_name' => 've.company']
    );
// 🔥 SQL PRINT KARNE KE LIYE YE DO LINES ADD KAREIN
     //echo $this->getSelect()->__toString(); 
     //die;
        return $this;
    }

}