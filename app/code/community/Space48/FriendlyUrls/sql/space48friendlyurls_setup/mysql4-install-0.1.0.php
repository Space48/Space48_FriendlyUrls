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

$installer = $this;
$installer->startSetup();

$dropSql = "DROP TABLE IF EXISTS {$this->getTable('space48_friendlyurls')};";
$createTableSql =  "CREATE TABLE {$this->getTable('space48_friendlyurls')} (
                    `id` int(11) unsigned NOT NULL auto_increment, 
                    `friendly_url_attribute_name` varchar(255) NOT NULL default '0', 
                    `attribute_name` varchar(255) NOT NULL default '0', 
                    `attribute_option_id` int(10) NOT NULL default '0', 
                    `friendly_url_option_label` varchar(255),
                    PRIMARY KEY(`id`) 
                    ) ENGINE = INNODB DEFAULT CHARSET=utf8;";

$installer->run($dropSql);
$installer->run($createTableSql);
$installer->endSetup();

