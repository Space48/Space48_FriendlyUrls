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

class Space48_FriendlyUrls_Block_Adminhtml_Attributes_Edit extends Mage_Adminhtml_Block_Widget_Form_Container 
{
	
	public function __construct() 
        {
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'space48friendlyurls';
		$this->_controller = 'adminhtml_attributes';
		$this->_updateButton('save', 'label', Mage::helper('space48friendlyurls')->__('Save'));
                $this->_removeButton('delete');
	}
	
        
	public function getHeaderText() 
        {
		if (Mage::registry('friendlyurls_data') && Mage::registry('friendlyurls_data')->getId()) {
                    return Mage::helper('space48friendlyurls')->__('Edit Friendly Url Segment  %s ', $this->htmlEscape(Mage::registry('friendlyurls_data')->getFriendlyUrlAttributeName()));
		} 
	}
	
}