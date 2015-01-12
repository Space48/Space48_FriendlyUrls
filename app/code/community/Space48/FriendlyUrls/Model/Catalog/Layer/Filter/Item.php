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

if (!(string)Mage::getConfig()->getModuleConfig('Space48_CustomLandingPage')->active == 'true')
{
    class Space48_CustomLandingPage_Model_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item{}
}
    class Space48_FriendlyUrls_Model_Catalog_Layer_Filter_Item extends Space48_CustomLandingPage_Model_Layer_Filter_Item
{
    public function getUrl()
    {
        $routeName = Mage::app()->getRequest()->getRouteName();
        $category = Mage::registry('current_category');
        if (Mage::registry('brand') || !$category || $routeName == 'catalogsearch') {
            return parent::getUrl();
        }

        $categoryUrlPath = $category->getUrlPath();

        /* @var $categoryUrlPath Space48_FriendlyUrls_Helper_Data */
        $categoryUrlPath = Mage::helper('space48friendlyurls')->removeCategorySuffixFromUrl($categoryUrlPath);
        $params = Mage::app()->getRequest()->getParams();
         $urlSegment = '';
         $priceSegment = '';
         // get params, but ignore id, which is the category one...
         foreach ($params as $k => $v) {
             if ($k != 'id') {
                $lookupModel = Mage::getModel('space48friendlyurls/attributes');
                $collection = $lookupModel->getCollection();
                $collection->addFieldToFilter('attribute_name', $k);
                $collection->addFieldToFilter('attribute_option_id', $v);
                if ($collection->getFirstItem()->hasData()) {
                    $attributeData = $collection->getFirstItem();
                    $urlSegment .= $attributeData->getFriendlyUrlAttributeName() . '/';
                }
             }
             if ($k == 'price') {
                $priceSegment = '?'. $k . '=' . $v;
             }
         }

        $query = array(
            $this->getFilter()->getRequestVar()=>$this->getValue(),
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
        // get the value for the link to select, but rewrite it!
        $catUrlSegment = '';
        foreach ($query as $k => $v) {
            if ($k != 'p') {
                $lookupModel = Mage::getModel('space48friendlyurls/attributes');
                $collection = $lookupModel->getCollection();
                $collection->addFieldToFilter('attribute_name', $k);
                $collection->addFieldToFilter('attribute_option_id', $v);
                if ($collection->getFirstItem()->hasData()) {
                    $attributeData = $collection->getFirstItem();
                    $urlSegment .= $attributeData->getFriendlyUrlAttributeName() . '/';
                }
                else {
                    if ($k == 'cat') {
                        $categoryModel = Mage::getModel('catalog/category')->load($v);
                        $catUrlSegment .=  '/' . $categoryModel->getUrlKey();
                    }
                    else {
                        $urlSegment .= '?'. $k . '=' . $v;
                    }
                }
            }
        }
        $catUrlSegment .= '/';


        if (Mage::helper('catalog/category')->getCategoryUrlSuffix()) {
             /* if segment has a ?, then add the suffix before this */
            if (strpos($urlSegment, '?')) {
                $urlSegment = str_replace('/?', Mage::helper('catalog/category')->getCategoryUrlSuffix() . '?', $urlSegment);
            }
            else {
                if (!$urlSegment) {
                    $catUrlSegment = rtrim($catUrlSegment, '/');

                    $urlSegment = Mage::helper('catalog/category')->getCategoryUrlSuffix();
                }
                else {
                    $urlSegment = rtrim($urlSegment, '/') . Mage::helper('catalog/category')->getCategoryUrlSuffix();
                }
            }
        }

        //$url = $baseUrl . $categoryUrlPath . $catUrlSegment . '/'. $urlSegment . $priceSegment;
        $urlParams = $categoryUrlPath . $catUrlSegment . $urlSegment . $priceSegment;

        $url = Mage::getUrl('/', array('_direct' => $urlParams));
        return $url;
    }

    public function getRemoveUrl()
    {
        $routeName = Mage::app()->getRequest()->getRouteName();
        if ($routeName == 'space48_salesection'|| $routeName == 'space48_newproductssection' || Mage::registry('brand') || $routeName == 'catalogsearch') {
                return parent::getRemoveUrl();
        }

        $query = array($this->getFilter()->getRequestVar()=>$this->getFilter()->getResetValue());
        $attributeKeyToDelete = key($query);
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $currentUrl = $_SERVER['REQUEST_URI'];
        $params = Mage::app()->getRequest()->getParams();


        if (array_key_exists($attributeKeyToDelete, $params)) {
            if ($attributeKeyToDelete == 'price') {
                $currentUrl = str_replace(array('?price=', $params['price']),'',$currentUrl);
                if (Mage::helper('catalog/category')->getCategoryUrlSuffix()) {
                    // replace /.html with .html
                    $currentUrl = str_replace('/'.Mage::helper('catalog/category')->getCategoryUrlSuffix(), Mage::helper('catalog/category')->getCategoryUrlSuffix(), $currentUrl);
                }

                return $currentUrl;
            }
            $lookupModel = Mage::getModel('space48friendlyurls/attributes');
            $collection = $lookupModel->getCollection();
            $collection->addFieldToFilter('attribute_name', $attributeKeyToDelete);
            $collection->addFieldToFilter('attribute_option_id', $params[$attributeKeyToDelete]);
            if ($collection->getFirstItem()->hasData()) {
                $attributeData = $collection->getFirstItem();
                $urlSegmentToDelete = $attributeData->getFriendlyUrlAttributeName();
                $newUrlParams = str_replace($urlSegmentToDelete, '', $currentUrl);
                /* trim and sanitise url (remove double slashes) */
                $baseUrl = Mage::getBaseUrl();
                $newUrlParams = str_replace($baseUrl, '', $newUrlParams);
                $newUrlParams = str_replace('//', '/', $newUrlParams);


                // add add suffixes and clean up again...
                if (Mage::helper('catalog/category')->getCategoryUrlSuffix()) {
                    $newUrlParams = str_replace('/'.Mage::helper('catalog/category')->getCategoryUrlSuffix(), Mage::helper('catalog/category')->getCategoryUrlSuffix(), $newUrlParams);
                }

                $newUrlParams = ltrim($newUrlParams, '/');
                return Mage::getUrl('', array('_direct'=>$newUrlParams));
            }
        }
    }
}
