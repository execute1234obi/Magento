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
    
    // --- Table Names ---
    $vendorTable = $this->resource->getTableName('ves_vendor_entity');
    $vendorVarcharTable = $this->resource->getTableName('ves_vendor_entity_varchar');
    $vendorTextTable = $this->resource->getTableName('ves_vendor_entity_text');
    $vendorIntTable = $this->resource->getTableName('ves_vendor_entity_int');
    $eavOptionValueTable = $this->resource->getTableName('eav_attribute_option_value'); // Added for Category Label
    
    // --- Attribute IDs (Confirmed from eav_attribute table) ---
    // NOTE: Using 195 for category as confirmed by the database output.
    $attrVendorNameId = 183;      // c_name
    $attrDescriptionId = 177;     // business_description
    $attrCategoryId = 195;        // Business Category (Select Box / INT table)
    $attrCompanyNameId = 174;     // company_name
    $attrVendorEmailId = 181;     // vendor_email
    $attrLogoId=187;              // upload_logo
    
    // Attribute 143 (vendor_id) is static, so no EAV ID needed for joining.

    // $sql = "
    //     SELECT 
    //         e.entity_id,
    //         e.vendor_id AS custom_vendor_id,                     -- *** FIX 1: Retrieve STATIC attribute directly ***
    //         name.value AS vendor_name,
    //         descr.value AS business_description,
    //         comp.value AS company_name,
    //         email.value AS vendor_email,
    //         logo.value AS vendor_logo,

            
    //         -- *** FIX 2: Retrieve BOTH ID and Name for Select Box ***
    //         cat_int.value AS business_category_id,               -- The integer Option ID
    //         cat_label.value AS business_category_name            -- The readable Label
            
    //     FROM {$vendorTable} AS e
         
    //     -- REMOVED: LEFT JOIN for vendor_id_attr (since vendor_id is STATIC)
            
    //     LEFT JOIN {$vendorVarcharTable} AS name
    //         ON e.entity_id = name.entity_id AND name.attribute_id = {$attrVendorNameId}
        
    //     LEFT JOIN {$vendorTextTable} AS descr
    //         ON e.entity_id = descr.entity_id AND descr.attribute_id = {$attrDescriptionId}
            
    //     LEFT JOIN {$vendorVarcharTable} AS comp
    //         ON e.entity_id = comp.entity_id AND comp.attribute_id = {$attrCompanyNameId}
            
    //     LEFT JOIN {$vendorVarcharTable} AS email
    //         ON e.entity_id = email.entity_id AND email.attribute_id = {$attrVendorEmailId}
            
    //     LEFT JOIN {$vendorVarcharTable} AS logo
    //         ON e.entity_id = logo.entity_id AND logo.attribute_id = {$attrLogoId}
            
    //     -- 1. Get the Option ID from the INT table (Attribute ID 195)
    //     LEFT JOIN {$vendorIntTable} AS cat_int 
    //         ON e.entity_id = cat_int.entity_id AND cat_int.attribute_id = {$attrCategoryId}
            
    //     -- 2. Get the Label from the EAV Option Value table
    //     LEFT JOIN {$eavOptionValueTable} AS cat_label
    //         ON cat_label.option_id = cat_int.value  
    //         AND cat_label.store_id = 0             -- Use store_id=0 for default label

    // ";
  $sql=  "SELECT 
    e.entity_id,
    e.vendor_id AS custom_vendor_id,
    name.value AS vendor_name,
    descr.value AS business_description,
    comp.value AS company_name,
    email.value AS vendor_email,
    logo.value AS vendor_logo,
    cat_int.value AS business_category_id,
    cat_label.value AS business_category_name,
    e.country_id
FROM ves_vendor_entity AS e
LEFT JOIN ves_vendor_entity_varchar AS name 
    ON e.entity_id = name.entity_id AND name.attribute_id = 183
LEFT JOIN ves_vendor_entity_text AS descr 
    ON e.entity_id = descr.entity_id AND descr.attribute_id = 177
LEFT JOIN ves_vendor_entity_varchar AS comp 
    ON e.entity_id = comp.entity_id AND comp.attribute_id = 174
LEFT JOIN ves_vendor_entity_varchar AS email 
    ON e.entity_id = email.entity_id AND email.attribute_id = 181
LEFT JOIN ves_vendor_entity_varchar AS logo 
    ON e.entity_id = logo.entity_id AND logo.attribute_id = 187
LEFT JOIN ves_vendor_entity_int AS cat_int 
    ON e.entity_id = cat_int.entity_id AND cat_int.attribute_id = 195
LEFT JOIN eav_attribute_option_value AS cat_label 
    ON cat_label.option_id = cat_int.value AND cat_label.store_id = 0;

";

    // IMPORTANT: Remove or comment out the echo/exit lines before running in production!
     //echo $sql;
     //exit(); 
    
    return $connection->fetchAll($sql);
}

    }
