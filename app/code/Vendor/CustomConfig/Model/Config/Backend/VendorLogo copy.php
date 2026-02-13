<?php
namespace Vendor\CustomConfig\Model\Config\Backend;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\ResourceConnection;
use Vnecoms\Vendors\Helper\Data as VendorHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\File\UploaderFactory;

use Vnecoms\Vendors\Model\Session as VendorSession;
// ⚠️ Extend the Vnecoms base config model for easier dependency management
class VendorLogo extends \Vnecoms\VendorsConfig\Model\Config\Backend\File
{
    protected $resourceConnection;
    protected $vendorHelper;
    protected $filesystem;
    protected $uploaderFactory;
    protected $vendorSession;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Vnecoms\VendorsConfig\Model\ResourceModel\Config $resource = null,
        \Vnecoms\VendorsConfig\Model\ResourceModel\Config\Collection $resourceCollection = null,
        \Vnecoms\VendorsConfig\Helper\Data $configHelper,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        ResourceConnection $resourceConnection, // Your required dependency
        VendorHelper $vendorHelper, // Your required dependency
        Filesystem $filesystem,
        VendorSession $vendorSession,
        UploaderFactory $uploaderFactory,
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->vendorHelper = $vendorHelper;
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->vendorSession = $vendorSession;
        // Pass the Vnecoms-specific arguments to the parent constructor
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $configHelper,
            $serialize,
            $data
        );
    }

    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png'];  // Allowed file types for logo upload
    }


//     protected function _afterSave()
// {
//     $value = $this->getValue();
 
//     // Check if the value is meant for deletion
//     if (is_array($value) && !empty($value['delete'])) {
//         $this->saveToVendorTable($this->vendorHelper->getVendor()->getId(), ''); // Delete logic
//         return $this; // Stop here, don't call parent save
//     }

//     // Check if a file was uploaded or exists
//     if (!empty($_FILES)) {
//         $file = $_FILES['groups']['fields'][$this->getField()['id']]['value'];
        
//         if (isset($file['name']) && $file['name']) {
//             try {
//                 // Define target path (needs to match your system.xml upload_dir)
//                 $uploadDir = 'ves_vendors/attribute/upload_logo/'; // Your specific media path
                
//                 $uploader = $this->uploaderFactory->create(['fileId' => $file]);
//                 $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
//                 $uploader->setAllowRenameFiles(true);
                
//                 $result = $uploader->save(
//                     $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath($uploadDir)
//                 );
                
//                 $filename = $uploadDir . ltrim($result['file'], "/");

//                 // 🔥 CRITICAL STEP: Save the new path ONLY to EAV
//                 $this->saveToVendorTable($this->vendorHelper->getVendor()->getId(), $filename);

//             } catch (\Exception $e) {
//                 throw new LocalizedException(__('Error saving vendor logo: %1', $e->getMessage()));
//             }
//         }
//     }
    
//     // DO NOT call parent::_afterSave()
//     return $this;
// }
       // ... inside Vendor\CustomConfig\Model\Config\Backend\VendorLogo
// Vendor\CustomConfig\Model\Config\Backend\VendorLogo.php

public function save()
    {
        // 1. Vendor ID प्राप्त करें
        /** @var VendorSession $vendorSession */
        $vendorId = $this->vendorSession->getVendorId();
        
        if (!$vendorId) {
            $this->unsValue();
            return $this; 
        }
        
        // 2. सुरक्षित रूप से फ़ाइल डेटा प्राप्त करें
        $fieldId = $this->getField()['id']; // e.g., 'upload_logo'
        
        // सुरक्षित पहुँच (Safety check for $_FILES array structure)
        $fileData = [];
        if (isset($_FILES['groups']['fields'][$fieldId]['value']) && is_array($_FILES['groups']['fields'][$fieldId]['value'])) {
            $fileData = $_FILES['groups']['fields'][$fieldId]['value']; 
        }
        
        $value = $this->getValue();
        $isSavedToEAV = false;
        $filename = null; // $filename को initialize करें

        // 3. delete logic
        if (is_array($value) && !empty($value['delete'])) {
            $this->saveToVendorTable($vendorId, '');
            $isSavedToEAV = true;
        } 
        // 4. upload logic
        elseif (isset($fileData['name']) && $fileData['name']) {
            try {
                $uploadDir = 'ves_vendors/attribute/upload_logo/'; // 

                // Uploader Fact
                $uploader = $this->uploaderFactory->create(['fileId' => $fileData]);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(true);
                
                $result = $uploader->save(
                    $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath($uploadDir)
                );
                
                $filename = $uploadDir . ltrim($result['file'], "/"); 

            } catch (\Exception $e) {
                 // अपलोड एरर को थ्रो करें
                 throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }

            // अगर अपलोड सफल हुआ, तो EAV में सेव करें
            if ($filename) { 
                $this->saveToVendorTable($vendorId, $filename);
                $isSavedToEAV = true;
            }
        }
        
        // 🛑 CRITICAL STEP: Vnecoms/Parent Save को ब्लॉक करें
        // Vnecoms Plugin को overide करने का एकमात्र तरीका
        if ($isSavedToEAV) {
            $this->setValue(''); 
            $this->unsValue();
        } else {
            // यदि कोई फ़ाइल अपलोड नहीं हुई है, लेकिन फ़ॉर्म सेव हो रहा है, 
            // तो भी डिफ़ॉल्ट सेविंग को रोकें ताकि EAV वैल्यू सुरक्षित रहे।
            $this->unsValue(); 
        }
        
        return $this;
    }
protected function saveToVendorTable($vendorId, $path)
{
    if (!$vendorId) {
        return $this;
    }
    
    $connection = $this->resourceConnection->getConnection();
    $tableName = $this->resourceConnection->getTableName('ves_vendor_entity_varchar');
    $attributeId = 187; // Your target EAV attribute ID
    
    // Check if the row exists
    $select = $connection->select()
        ->from($tableName, 'value_id')
        ->where('entity_id = ?', $vendorId)
        ->where('attribute_id = ?', $attributeId);
        
    $valueId = $connection->fetchOne($select);

    if ($valueId) {
        // Update existing row
        $connection->update(
            $tableName,
            ['value' => $path],
            ['value_id = ?' => $valueId]
        );
    } else {
        // Insert new row
        $connection->insert($tableName, [
            'entity_id' => $vendorId,
            'attribute_id' => $attributeId,
            'value' => $path,
            'store_id' => 0 // Assuming store 0 (global)
        ]);
    }
    
    return $this;
}
}