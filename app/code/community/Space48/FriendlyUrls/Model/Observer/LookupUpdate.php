<?php
/**
 * Space48
 *
 * Space48 Search Engine Friendly Urls, which rewrites any catalog category urls into a SEO friendly format.  
 *
 * @package Space48_FriendlyUrls
 */
/**
 * Space48 Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.space48.com/license.html
 *
 * @category   Space48
 * @package    Space48_Custom_Landing_Page
 * @version    0.1.1
 * @copyright  Copyright (c) 2013-2013 Space48 Ltd. (http://www.space48.com)
 * @license    http://www.space48.com/license.html
 * @company    Space48
 * @author     Steven Wan (steven.wan@space48.com)
 * @link       http://wiki.space48.com/modules/friendly_urls
 */

class Space48_FriendlyUrls_Model_Observer_LookupUpdate
{
    public function saveAttributes(Varien_Event_Observer $observer)
    {   
        //$object = $observer->getEvent()->getDataObject();
        
        /* build up a list of useful variables and arrays used in our CRUD actions */
        $options = $observer->getEvent()->getDataObject()->getOption('value');
        $attributeName = $observer->getEvent()->getDataObject()->getData('attribute_code');
       // $oldFrontendLabel = $observer->getDataObject()->getOrigData('frontend_label');
        $frontendLabel = $observer->getEvent()->getDataObject()->getData('frontend_label');   
        $attributesValuesToDelete = $observer->getEvent()->getDataObject()->getOption('delete');
         
        /* get an array of attribute values that were deleted */
        $valuesToDelete = array(); 
        foreach ($attributesValuesToDelete as $k => $v) {
            if ($v == 1) {
                $valuesToDelete[] = $k;
            } 
        }

        // get attribute options with existing friendly urls
        $existingFriendlyUrls = Mage::getModel('space48friendlyurls/attributes')->getCollection()
                                    ->addFieldToFilter('attribute_name', $attributeName)
                                    ->toArray(array("attribute_option_id"));
        // extract just the items from the array
        $existingFriendlyUrls = $existingFriendlyUrls["items"];
        // build a simple array of the used attribute options in order to be able to do an is "in_array"
        $usedAttributeOptions = array();
        foreach ($existingFriendlyUrls as $existingFriendlyUrl) {
            $usedAttributeOptions[] = $existingFriendlyUrl["attribute_option_id"];
        }

        foreach ($options as $optionKey => $optionValues) {   
                $attributeOptionId = $optionKey;
                $friendlyUrlOptionLabel = $optionValues[0];

                /* if $attributeOptionId is INT and it exists as a current friendly url, then UPDATE or DELETE */
                if (is_int($attributeOptionId) && in_array($attributeOptionId, $usedAttributeOptions)) {
                    /* if INT is in Delete array, then DELETE from lookup table! */
                    
                    if (in_array($attributeOptionId, $valuesToDelete)) { 
                        $lookupModel = Mage::getModel('space48friendlyurls/attributes')->getCollection();
                        $lookupModel->addFieldToFilter('attribute_option_id', $attributeOptionId);
                        $item = $lookupModel->getFirstItem();
                        $item->delete();
                        $item->save();
                    }
                    /* UPDATE instead */
                    else {
                        //$friendlyUrl = strtolower($frontendLabel . '-' . $friendlyUrlOptionLabel);
                        //$friendlyUrl = Mage::helper('space48friendlyurls')->cleanUrlSegment($friendlyUrl);
                        $lookupModel = Mage::getModel('space48friendlyurls/attributes')->getCollection();
                        $lookupModel->addFieldToFilter('attribute_option_id', $attributeOptionId);
                        $item = $lookupModel->getFirstItem();
                        //$item->setData('friendly_url_attribute_name', $friendlyUrl);
                        $item->setAttributeName($attributeName);
                        //$item->setAttributeOptionId($attributeOptionId);
                        $item->setFriendlyUrlOptionLabel($friendlyUrlOptionLabel);
                        $item->save();
                    }
                }
                /* else ADD attribute value */
                else {            
                    $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
                    $query = "SELECT option_id FROM eav_attribute_option_value where value = '" . $friendlyUrlOptionLabel . "'" ;

                    $attributeOptionId = $readConnection->fetchOne($query);             
                    $friendlyUrl = strtolower($frontendLabel . '-' . $friendlyUrlOptionLabel);
                    //$searchTerms = array(' ', '/');
                    //$friendlyUrl = str_replace($searchTerms, '-', $friendlyUrl);        
                    $friendlyUrl = Mage::helper('space48friendlyurls')->cleanUrlSegment($friendlyUrl);
                    $lookupModel = Mage::getModel('space48friendlyurls/attributes');
                    $lookupModel->setFriendlyUrlAttributeName($friendlyUrl);
                    $lookupModel->setAttributeName($attributeName);
                    $lookupModel->setAttributeOptionId($attributeOptionId);
                    $lookupModel->setFriendlyUrlOptionLabel($friendlyUrlOptionLabel);
                    $lookupModel->save();  
                }       
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('space48friendlyurls')->__('Friendly SEO Data have been updated.'));
    }
    
    
    /*
     * When someone deletes an attribute, then it will 
     * clear the associated values in the lookup table also.  
     * 
     */
    public function deleteAttributes(Varien_Event_Observer $observer)
    {
        //$object = $observer->getEvent()->getDataObject();   
        $attributeName = $observer->getEvent()->getDataObject()->getData('attribute_code');
        $lookupModel = Mage::getModel('space48friendlyurls/attributes')->getCollection();
        $attributes = $lookupModel->addFieldToFilter('attribute_name', $attributeName);
        foreach ($attributes as $attribute) {
            $item = Mage::getModel('space48friendlyurls/attributes')->load($attribute->getId());
            $item->delete();
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('space48friendlyurls')->__('Deleted from friendly URLs'));
    }  
}
?>
