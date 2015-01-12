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

class Space48_FriendlyUrls_Block_Adminhtml_Attributes_Edit_Tabs_Form extends Mage_Adminhtml_Block_Widget_Form 
{
	
	protected function _prepareForm() 
        {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		
		$formData = Mage::registry('friendlyurls_data');                
		$fieldset = $form->addFieldset('attribute_form', array('legend' => Mage::helper('space48friendlyurls')->__('Friendly Url Segment to change')));
                
                /* Unused fields, but commented out for future reference
                $fieldset->addField('id', 'hidden', array(
			'label'		=> Mage::helper('space48friendlyurls')->__('Id'),
			'name'		=> 'id',
		));
                
                $fieldset->addField('attribute_name', 'note', array(
                        'label'     => Mage::helper('space48friendlyurls')->__('Attribute Name'),
                        'text'      => Mage::helper('space48friendlyurls')->__($formData->getData('attribute_name')),
                ));
                
                $fieldset->addField('attribute_option_id', 'note', array(
                        'label'		=> Mage::helper('space48friendlyurls')->__('Friendly Url Attribute Option Id'),
                        'text'          => Mage::helper('space48friendlyurls')->__($formData->getData('attribute_option_id')),
                ));   
                */
                
                
                $fieldset->addField('original_attribute_name', 'note', array(
                        'label'     => Mage::helper('space48friendlyurls')->__('Original Attribute Values'),
                        'text'      => Mage::helper('space48friendlyurls')->__($formData->getData('attribute_name') . "=" . $formData->getData('attribute_option_id')),
                ));

                $fieldset->addField('friendly_url_attribute_name', 'text', array(
			'label'		=> Mage::helper('space48friendlyurls')->__('Friendly Url Segment'),
			'class'		=> 'required-entry',
			'required'	=> TRUE,
			'name'		=> 'friendly_url_attribute_name',
		));
                
		if(Mage::getSingleton('adminhtml/session')->getFriendlyUrlData()) {
                    $form->setValues(Mage::getSingleton('adminhtml/session')->getFriendlyUrlData());
		} elseif (Mage::registry('friendlyurls_data')) {
                    $form->setValues(Mage::registry('friendlyurls_data')->getData());
		}
                
		return parent::_prepareForm();
	}
	
}