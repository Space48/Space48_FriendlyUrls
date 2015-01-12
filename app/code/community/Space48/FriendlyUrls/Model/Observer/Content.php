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

class Space48_FriendlyUrls_Model_Observer_Content
{
    /**
     * Update the category view meta tags and the canonical url
     *
     * @param Varien_Event_Observer $observer
     */
    public function _updateMetaData(Varien_Event_Observer $observer)
    {
       $routeName = Mage::app()->getRequest()->getRouteName();
       if (!(Mage::registry('brand'))) {
            if ($routeName != 'space48_salesection') {
	        	if ($routeName != 'space48_newproductssection') {
                    $this->_updateMetaContent(Mage::app()->getLayout());

                    // if 'Use Canonical Link Meta Tag For Categories' feature enabled
                    // format the canonical as required
                    if ($configValue = Mage::getStoreConfig('catalog/seo/category_canonical_tag')) {
                        $this->_updateCanonicalUrl();
                    }
		        }
            }
       } else {
           $this->_addCanonicalUrl();
       }
    }

    /**
     * Update the cms page canonical url
     *
     * @param Varien_Event_Observer $observer
     */
    public function _updateCmsPageCanonical(Varien_Event_Observer $observer)
    {
        $homePageName = Mage::getStoreConfig('web/default/cms_home_page');
        $cmsPageName = str_replace('/', '', Mage::app()->getRequest()->getRequestUri());
        $headBlock = Mage::app()->getLayout()->getBlock('head');
        $urlString = Mage::helper('core/url')->getCurrentUrl();

        if ($homePageName == $cmsPageName) {
            // if home page, use base url
            $url = Mage::getSingleton('core/url')->parseUrl($urlString);
            $headBlock->addLinkRel('canonical', $url->getBaseUrl());
        } else {
            $headBlock->addLinkRel('canonical', $urlString);
        }
    }

    protected function _isSalesSectionEnabled()
    {
        $rewrite = Mage::getModel('core/url_rewrite');
        $collection = $rewrite->getCollection();
        $collection->addFieldToFilter('id_path','space48_sale_section');
        if (!empty($collection)) {
          return true;
        }
        return false;
    }

    protected function _updateMetaContent($layout)
    {
        $appliedFilters = Mage::getSingleton('catalog/layer')->getState()->getFilters();
        $this->manufacturerAttribute = '';
        $attributesValues = array();
        if ($appliedFilters) {
            foreach ($appliedFilters as $item) {
                $attributeCode = $item->getFilter()->getRequestVar();
                if ($attributeCode == 'manufacturer') {
                    $optionValue = $item->getValue();
                    $productModel = Mage::getModel('catalog/product');
                    $attributes = $productModel->getResource()->getAttribute($attributeCode);
                    if ($attributes->usesSource()) {
                        $this->manufacturerAttribute = $attributes->getSource()->getOptionText($optionValue);
                    }
                } else if ($attributeCode == 'price') {
                    $layout->getBlock("head")->setRobots("NOINDEX,FOLLOW");
                } else {
                    $optionValue = $item->getValue();
                    $productModel = Mage::getModel('catalog/product');
                    $attributes = $productModel->getResource()->getAttribute($attributeCode);
                    if ($attributes->usesSource()) {
                        $attributesValues[] = $attributes->getSource()->getOptionText($optionValue);
                    }
                }
            }
        }

        $this->currentCategory = Mage::registry('current_category');
        $this->appliedFilters = Mage::registry('current_category');
        if (count($this->appliedFilters)) {
            $this->currentCategoryName = $this->currentCategory->getName();
            $this->headerTitle = trim($this->manufacturerAttribute ." ". $this->currentCategoryName);

            $this->titleAttributes = '';
            if ($attributesValues) {
                foreach ($attributesValues as $attributeValue) {
                    $this->headerTitle .= ' - '. $attributeValue;
                    $this->titleAttributes .= ' ' . $attributeValue;
                }
            }

            $this->_setCategoryMetaTitleData($layout);
        }
    }

    /**
     * Set Category/Meta titles and descriptions
     *
     * @param $layout layout object
     */
    protected function _setCategoryMetaTitleData($layout)
    {
        $_metaData = $this->_getCategoryMetaTitleOverrides();
        $_categoryTitle = $_metaData['category_name'] ? $_metaData['category_name'] : Mage::helper('space48friendlyurls')->__($this->headerTitle);
        $this->currentCategory->setName($_categoryTitle);

        $_categoryDescription = $_metaData['category_description'] ? $_metaData['category_description'] : Mage::helper('space48friendlyurls')->__('Below you can compare and buy: %s', $this->headerTitle);
        $this->currentCategory->setDescription($_categoryDescription);

        if (!$_headTitle = $_metaData['head_title']) {
            $_headTitle = str_replace('-', '', trim($this->manufacturerAttribute ." ". $this->currentCategoryName . $this->titleAttributes));
            $_headTitle = Mage::helper('space48friendlyurls')->__($_headTitle);
        }

        if (!$_headDescription = $_metaData['head_description']) {
            $_headDescription = trim('Find a wide range of '.$_headTitle.' at '.Mage::app()->getStore()->getName().'.');
        }

        $layout->getBlock("head")->setTitle($_headTitle);
        $layout->getBlock("head")->setDescription($_headDescription);
    }

    /**
     * Retrieve category title and description overrides
     *
     * @return array
     */
    protected function _getCategoryMetaTitleOverrides()
    {

    }

    protected function _updateCanonicalUrl()
    {
        /*
         * If we are in catalog category view, then use canonical url override...
         */
        $category = Mage::registry('current_category');
        /* only do this if the store isn't Admin (store id 0) */
        if ($category && $category->getStoreId() != 0) {
            if ($url = $this->_getCanonicalUrl()) {
                $headBlock = Mage::app()->getLayout()->getBlock('head');
                $headBlock->removeItem('link_rel', $category->getUrl());
                $headBlock->addLinkRel('canonical', $url);
            }
        }
    }

    /**
     * Add new canonical to non category pages such as custom landing/brand pages
     */
    protected function _addCanonicalUrl()
    {
        if ($url = $this->_getCanonicalUrl()) {
            $headBlock = Mage::app()->getLayout()->getBlock('head');
            $headBlock->addLinkRel('canonical', $url);
        }
    }

    /* return the canonical url for a catalog category page */
    protected function _getCanonicalUrl()
    {
        $url = Mage::helper('core/url')->getCurrentUrl();
        $parsedUrl = parse_url($url);

        /* we only want the scheme, host and path, and not the query keys (see parse_url function) */
        $canonicalUrl = '';
        foreach ($parsedUrl as $k => $urlSegment) {
            if ($k != 'query') {
                if ($urlSegment == 'http') {
                    $urlSegment .= "://";
                }
                $canonicalUrl .= $urlSegment;
            }
        }

        return $this->_removeFiltersFromCanonicalUrl($canonicalUrl);
    }

    /**
     * Remove non required selected filters from canonical URL
     *
     * @param $canonicalUrl full url including filters
     * @return string canonical with filters removed
     */
    public function _removeFiltersFromCanonicalUrl($canonicalUrl)
    {
        // Modify the canonical only when filters have been applied
        if ($appliedFilters = Mage::getSingleton('catalog/layer')->getState()->getFilters()) {

            // attributes that are to be maintained in the canonical
            $attrToKeepInCanonicalUrl = explode(',', Mage::getStoreConfig('space48friendlyurls/settings/attributes'));

            // remove non filter (url/category) values from url that we want to keep as standard
            $categoryUrl = Mage::registry('current_category')->getUrl();
            $canonicalUrl = str_replace($categoryUrl . "/", "", $canonicalUrl);

            if ($urlSegments = explode("/", $canonicalUrl)) {
                foreach ($urlSegments as $urlSegment) {
                    if ($urlSegment) {
                        $lookupModel = Mage::getModel('space48friendlyurls/attributes')->getCollection()
                            ->addFieldToFilter('friendly_url_attribute_name', $urlSegment);
                        if ($item = $lookupModel->getFirstItem()) {
                            if (!in_array($item->getAttributeName(), $attrToKeepInCanonicalUrl)) {
                                $canonicalUrl = str_replace($urlSegment . "/", '', $canonicalUrl);
                            }
                        }
                    }
                }

                return $categoryUrl . "/" . $canonicalUrl;
            }
        }

        return $canonicalUrl;
    }
}
