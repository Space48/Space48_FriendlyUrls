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

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/*
 * 1.  Get list of all categories
 * 2.  for each categories, get all the attributes and add them to the Space48 attribute lookup table
 */
$collection = Mage::getResourceModel('catalog/product_attribute_collection');
$collection->addVisibleFilter();

foreach ($collection as $productAttributes) {
    if ($productAttributes->getData('is_filterable') == 1 && $productAttributes->getData('attribute_code') != 'price') {
                $attributeOption = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $productAttributes->getData('attribute_code'));
                if ($attributeOption->usesSource()) {
                    $options = $attributeOption->getSource()->getAllOptions(false);
                    foreach ($options as $option) {
                        $friendlyUrl = strtolower($productAttributes->getData('frontend_label') .'-' .$option['label']);
                        //$searchTerms = array(' ', '/');
                        //$friendlyUrl = str_replace($searchTerms, '-', $friendlyUrl);

                        $checkIfExists = Mage::getModel('space48friendlyurls/attributes')->getCollection()
                                            ->addFieldToFilter('friendly_url_attribute_name', array('eq' => $friendlyUrl))
                                            ->addFieldToSelect('friendly_url_attribute_name');
                        $checkIfExists = $checkIfExists->getData();

                        if (!$checkIfExists) {
                            $friendlyUrl = Mage::helper('space48friendlyurls')->cleanUrlSegment($friendlyUrl);
                            $lookupModel = Mage::getModel('space48friendlyurls/attributes');
                            $lookupModel->setFriendlyUrlAttributeName($friendlyUrl);
                            $lookupModel->setAttributeName($productAttributes->getData('attribute_code'));
                            $lookupModel->setAttributeOptionId($option['value']);
                            $lookupModel->setFriendlyUrlOptionLabel($option['label']);
                            $lookupModel->save();
                        }
                    }
                }
    }
}

$installer->endSetup();