<?php
/**
 * @copyright Copyright (c) 2013 Space 48 (http://www.space48.com).
 * @author Steven Wan <steven.wan@space48.com>
 * @version $Rev$
 */
class Space48_FriendlyUrls_Model_Attributes extends Mage_Core_Model_Abstract 
{

	public function _construct() 
    {
		parent::_construct();
		$this->_init('space48friendlyurls/attributes');
	}
}