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

class Space48_FriendlyUrls_Block_Adminhtml_Attributes_Edit_Form extends Mage_Adminhtml_Block_Widget_Form 
{
	
	protected function _prepareForm() 
        {
		$form = new Varien_Data_Form(array(
			'id'	=> 'edit_form',
			'action'	=> $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
			'method'	=> 'post',
		));
		
		$form->setUseContainer(TRUE);
		$this->setForm($form);
                
		return parent::_prepareForm();
	}
	
}