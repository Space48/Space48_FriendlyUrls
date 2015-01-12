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

class Space48_FriendlyUrls_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() 
        {
		$this->loadLayout();
		$this->_setActiveMenu('friendlyUrls');
		$this->_addBreadcrumb(	Mage::helper('space48friendlyurls')->__('space48friendlyurls'),
                                        Mage::helper('space48friendlyurls')->__('space48friendlyurls'));
		return $this;
	}

	public function indexAction() 
        {
		$this->_initAction();
		$this->renderLayout();
	}
        
        public function editAction() 
        {
		$id = $this->getRequest()->getParam('id');                
		$lookupModel = Mage::getModel('space48friendlyurls/attributes')->load($id);
		
		if($lookupModel->getId()) { 
			Mage::register('friendlyurls_data', $lookupModel);
			$this->_initAction();
			$this->getLayout()->getBlock('head')->setCanLoadExternalJs(TRUE);
			$this->_addContent($this->getLayout()->createBlock('space48friendlyurls/adminhtml_attributes_edit'));
			$this->_addLeft($this->getLayout()->createBlock('space48friendlyurls/adminhtml_attributes_edit_tabs'));
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('space48friendlyurls')->__('Record does not exist'));
			$this->_redirect('*/*/');
		}
	}
	
	public function saveAction() 
        {
		if($this->getRequest()->getPost()) {
			try {
                            $postData = $this->getRequest();
                            $id =  $postData->getParam('id');
                            $friendlyUrlAttributeName =  $postData->getParam('friendly_url_attribute_name');
                            $lookupModel = Mage::getModel('space48friendlyurls/attributes')->load($id);            
                            /* TODO Add name validation */                                                  
                            if ($this->_isDuplicateFriendlyUrlSegment($friendlyUrlAttributeName))
                            {
                                Mage::getSingleton('adminhtml/session')->addError('Saving aborted: Friendly URL Segment must be unique!');
                                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                                return;
                            }
                            $lookupModel->setId($this->getRequest()->getParam('id'));
                            $lookupModel->setFriendlyUrlAttributeName($friendlyUrlAttributeName);
                            $lookupModel->save();
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('space48friendlyurls')->__('Friendly Url Segment Saved!'));
                            Mage::getSingleton('adminhtml/session')->setFriendlyUrlData(FALSE);
                            $this->_redirect('*/*/');
                            return;
			} catch(Exception $e) {
                            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                            Mage::getSingleton('adminhtml/session')->setFriendlyUrlData($this->getRequest()->getPost());
                            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                            return;
			}
		}
		$this->_redirect('*/*/');
	}
        
        protected function _isDuplicateFriendlyUrlSegment($friendlyUrlAttributeName)
        {
            $lookupModel = Mage::getModel('space48friendlyurls/attributes')->getCollection();
            $lookupModel->addFieldToFilter('friendly_url_attribute_name', $friendlyUrlAttributeName);
            if ($lookupModel->getFirstItem()->hasData()) {
                return true;
            }
            return false;
        }
}