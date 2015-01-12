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

class Space48_FriendlyUrls_Block_Adminhtml_Attributes_Grid extends Mage_Adminhtml_Block_Widget_Grid  
{
    public function __construct()
    {
    	$this->unsetChild('search_button');
        parent::__construct();
        $this->setId('friendlyUrlsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
    	$this->setCollection($this->_getCollection());
    	return parent::_prepareCollection();
    }

    protected function _prepareLayout()  
    {
        parent::_prepareLayout();
        $this->getChild('reset_filter_button')->setLabel('Reset');
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('space48friendlyurls')->__('id'),
            'align'  	=>'left',
            'index'  	=> 'id',
            'width'     => '80px',
        ));
        $this->addColumn('friendly_url_attribute_name', array(
            'header'    => Mage::helper('space48friendlyurls')->__('Friendly URL Segment'),
            'align'  	=>'left',
            'index'  	=> 'friendly_url_attribute_name',
        ));
        $this->addColumn('attribute_name', array(
            'header'    => Mage::helper('space48friendlyurls')->__('Attribute Name'),
            'align'  	=>'left',
            'index'  	=> 'attribute_name',
        ));
    	$this->addColumn('attribute_option_id', array(
            'header'    => Mage::helper('space48friendlyurls')->__('Attribute Option Id'),
            'align'  	=>'left',
            'index'  	=> 'attribute_option_id',
            'width'	=> '80px',
        ));
        $this->addColumn('friendly_url_option_label', array(
            'header'    => Mage::helper('space48friendlyurls')->__('Attribute Option Label Value'),
            'align'  	=>'left',
            'index'  	=> 'friendly_url_option_label',
        ));
        
        return parent::_prepareColumns();
    }

    protected function _getCollection()  
    {
	$lookupModel = Mage::getModel('space48friendlyurls/attributes')->getCollection();
	
        return $lookupModel;
    }
    
    public function getRowUrl($row) 
    {    
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}