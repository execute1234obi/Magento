<?php
namespace Custom\VendorList\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\ResourceConnection;

class VendorList extends Template
{
    protected $resource;

    public function __construct(
        Template\Context $context,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->resource = $resource;
        parent::__construct($context, $data);
    }
    public function getVendorDetails()
{
    $connection = $this->resource->getConnection();
    $vendorTable = $this->resource->getTableName('ves_vendor_entity');
    $vendorVarcharTable = $this->resource->getTableName('ves_vendor_entity_varchar');
    $vendorTextTable = $this->resource->getTableName('ves_vendor_entity_text');
    $countryTable = $this->resource->getTableName('directory_country');

    // Replace these with the IDs from your query
    $attrVendorNameId = 183; // c_name
    $attrCategoryId = 175;   // business_category
    $attrDescriptionId = 177; // business_description
    $attrCategory = 176;    // Business category
    $attrCompanyNameId = 174; // company_name
    $attrVendorEmailId = 181; // vendor_email
    $attrVendorId = 143;
    $attrLogoId=187;

    $sql = "
        SELECT 
            e.entity_id,
            name.value AS vendor_name,
            cat.value AS business_category,
            descr.value AS business_description,
            comp.value AS company_name,
            email.value AS vendor_email,
           v_category.value AS business_category ,
           logo.value AS vendor_logo
        FROM {$vendorTable} AS e
        LEFT JOIN {$vendorVarcharTable} AS name
            ON e.entity_id = name.entity_id AND name.attribute_id = {$attrVendorNameId}
        LEFT JOIN {$vendorVarcharTable} AS cat
            ON e.entity_id = cat.entity_id AND cat.attribute_id = {$attrCategoryId}
        LEFT JOIN {$vendorTextTable} AS descr
            ON e.entity_id = descr.entity_id AND descr.attribute_id = {$attrDescriptionId}
        LEFT JOIN {$vendorVarcharTable} AS comp
            ON e.entity_id = comp.entity_id AND comp.attribute_id = {$attrCompanyNameId}
        LEFT JOIN {$vendorVarcharTable} AS email
            ON e.entity_id = email.entity_id AND email.attribute_id = {$attrVendorEmailId}
        -- Business Category
        LEFT JOIN {$vendorVarcharTable} AS v_category
            ON e.entity_id = v_category.entity_id AND v_category.attribute_id = {$attrCategory}
        LEFT JOIN {$vendorVarcharTable} AS logo
    ON e.entity_id = logo.entity_id AND logo.attribute_id = {$attrLogoId}
    ";

    return $connection->fetchAll($sql);
}

    }
