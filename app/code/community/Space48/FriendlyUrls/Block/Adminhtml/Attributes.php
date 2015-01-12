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

class Space48_FriendlyUrls_Block_Adminhtml_Attributes extends Mage_Adminhtml_Block_Widget_Grid_Container 
{

    public function __construct() 
    {
        $this->_controller = 'adminhtml_attributes'; //path to controller
        $this->_blockGroup = 'space48friendlyurls'; //module
		
        /*
        Test this is correct as it should be the path to your grid
        sd($this->_blockGroup.'/' . $this->_controller . '_grid');
        */
		
        $this->_headerText = Mage::helper('space48friendlyurls')->__('Friendly Url Attributes');
        parent::__construct();
        $this->_removeButton('add');
    }
}