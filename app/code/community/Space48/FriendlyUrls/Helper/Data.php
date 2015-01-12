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

class Space48_FriendlyUrls_Helper_Data extends Mage_Core_Helper_Abstract 
{
	public function isEnabled()
	{
		return Mage::getStoreConfig('space48friendlyurls/settings/enabled');
	}

	/* clean up url, and replace any illegal characters with a '-' */
	public function cleanUrlSegment($urlString)
	{
		$illegalUrlCharacters = array(' ', '/');
		
                return str_replace($illegalUrlCharacters, '-', $urlString);
	}
        
        public function removeCategorySuffixFromUrl($urlString)          
        {
            /* Get category suffix info */
            $categorySuffix = Mage::helper('catalog/category')->getCategoryUrlSuffix();
            if (!$categorySuffix) {
                return $urlString;
            }  
            return str_replace($categorySuffix, '', $urlString); 
        }
        
}