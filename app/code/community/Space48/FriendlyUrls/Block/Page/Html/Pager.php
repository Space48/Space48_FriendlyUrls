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

class Space48_FriendlyUrls_Block_Page_Html_Pager extends Mage_Page_Block_Html_Pager
{
    public function getPagerUrl($params=array())
    {
        $urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        $url = $this->getUrl('*/*/*', $urlParams);
        $currentRouter = Mage::app()->getFrontController()->getRequest()->getRouteName();
        // only apply the pager rewrite if the current router is 'catalog'.  We don't need to apply changes to the catalogsearch
        if ($currentRouter !== 'catalog') {
            return $url;
        }
        
        
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $parsedCurrentUrl = parse_url($currentUrl); 
        $currentUrlPath = $parsedCurrentUrl['path']; // get the category and friendly url paths
        
        $queryStringParams = strstr($url,'?'); // get the querystring parameters after the ? in the URL
        $newUrlParams = $currentUrlPath . $queryStringParams;
        $newUrlParams = ltrim($newUrlParams, '/');
        
        return Mage::getUrl('', array('_direct' => $newUrlParams));  
    }   
}