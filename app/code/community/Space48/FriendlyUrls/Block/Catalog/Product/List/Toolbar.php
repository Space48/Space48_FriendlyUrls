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

class Space48_FriendlyUrls_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    public function getPagerUrl($params=array())
    {  
        $urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        $url = $this->getUrl('*/*/*', $urlParams);
        $currentUrl =  Mage::helper('core/url')->getCurrentUrl();
        $queryStringRight = strstr($url,'?');
        $newUrl = strstr($currentUrl,'?',TRUE);
        if ($newUrl == false) {
            $newUrl = $currentUrl;
        }
        return $newUrl . $queryStringRight; 
    }    
}