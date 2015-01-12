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
 * @version    0.1.0
 * @copyright  Copyright (c) 2013-2013 Space48 Ltd. (http://www.space48.com)
 * @license    http://www.space48.com/license.html
 * @company    Space48
 * @author     Steven Wan (steven.wan@space48.com)
 * @link       http://wiki.space48.com/modules/friendlyurls
 * @see        http://inchoo.net/ecommerce/magento/custom-router-in-magento/
 * 
 */

class Space48_FriendlyUrls_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
    public function initControllerRouters($observer) 
    {
        $front = $observer->getEvent()->getFront();
        $front->addRouter('space48friendlyurls', $this);  
    }
    
    public function match(Zend_Controller_Request_Http $request) 
    {
        /* @var $request Mage_Core_Controller_Request_Http */   
        $urlSegments = $this->_getUrlSegments($request);      

        // trim the category suffix from url 
        if (Mage::helper('catalog/category')->getCategoryUrlSuffix()) {  
            foreach ($urlSegments as $k => $urlSegment) {
                $urlSegments[$k] = Mage::helper('space48friendlyurls')->removeCategorySuffixFromUrl($urlSegment);    
            } 
        }
        /* ######################## */

        $urlSegmentsAsString = implode('/', array_reverse($urlSegments));
        $params = array();
        
        foreach ($urlSegments as $urlSegment) {
            $attributeData = $this->_getLookupCollection($urlSegment);
            // if url segment is in the lookup table...
            if ($attributeData) {
                $params[$attributeData->getAttributeName()] = $attributeData->getAttributeOptionId();
                $urlSegmentsAsString = str_replace($urlSegment, '', $urlSegmentsAsString);
            } else if (!$attributeData) { // if url segment is not in lookup table, then it is the url_key for a category...
                //$category = Mage::getModel('catalog/category')->loadByAttribute('url_key', $urlSegment);
                $categoryModel = Mage::getModel('catalog/category')->getCollection();
                $categories = $categoryModel->addAttributeToFilter('url_key', $urlSegment);
       
                if ($categories) {          
                    foreach($categories as $category) {
                        $cat = Mage::getModel('catalog/category')->load($category->getId());
                        $urlPath = $cat->getUrlpath();
                        $urlPath = Mage::helper('space48friendlyurls')->removeCategorySuffixFromUrl($urlPath); 
                        
                        /* trimming forward slashes to allow for better comparisons.. */
                        $urlPathForComparison = str_replace('/','',$urlPath);
                        $urlSegmentsAsStringForComparison = str_replace('/','',$urlSegmentsAsString);        

                        if ($urlPathForComparison === $urlSegmentsAsStringForComparison) {
                            $categoryId = $cat->getId();
                            $params['id'] = $categoryId; 
                            break;
                        }    
                    }  
                }      
            }       
        }
        if (empty($params)) {
            return false;
        }   
        $request->initForward();
        $request->setModuleName('catalog');
        $request->setControllerName('category');
        $request->setActionName('view');
        if ($params) {
            $request->setParams($params);
        }  
        $request->setDispatched(false);
        
        return true;   
    }
    
    protected function _getUrlSegments($request)
    {   
        if ($request) {  
            $requestString = trim(str_replace('/', ' ', $request->getRequestString()));    
            $urlSegments = array_reverse(explode(" ", $requestString)); 
            
            return $urlSegments;
        }
        
        return false;
    }
    
    protected function _getLookupCollection($attribute = NULL) 
    {
        $lookupModel = Mage::getModel('space48friendlyurls/attributes');
        $collection = $lookupModel->getCollection();
        if ($attribute) {
            $collection->addFieldToFilter('friendly_url_attribute_name', $attribute);
        }
        if ($collection->getFirstItem()->hasData()) {
            
            return $collection->getFirstItem();
        }
        return null;
    }
}