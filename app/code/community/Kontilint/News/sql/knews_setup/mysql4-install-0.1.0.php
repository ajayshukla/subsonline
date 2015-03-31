<?php
/**
 * Kontilint_News extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Kontilint
 * @package    Kontilint_News
 * @copyright  Copyright (c) 2010 Kontilint Data AB
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Kontilint
 * @package    Kontilint_News
 * @author     Mattias Bomelin <mattias@kontilint.se>
 */

$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('kontilint_news')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('kontilint_news')}` (
  `news_id` smallint(6) NOT NULL auto_increment,
  `is_active` tinyint(1) NOT NULL default '1',
  `title` varchar(255) NOT NULL,
  `identifier` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `short_description` text NOT NULL,
  `description` text NOT NULL,
  `image` varchar(200) NOT NULL default '',
  `category` int unsigned not null default 0,
  `meta_keywords` text NOT NULL,
  `meta_description` text NOT NULL,
  PRIMARY KEY  (`news_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Kontilint News' AUTO_INCREMENT=1;

-- DROP TABLE IF EXISTS `{$this->getTable('kontilint_news_store')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('kontilint_news_store')}` (
  `news_id` smallint(6) NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`news_id`,`store_id`),
  KEY `FK_KONTILINT_NEWS_STORE_STORE` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Kontilint News Stores';

-- DROP TABLE IF EXISTS `{$this->getTable('kontilint_news_cat')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('kontilint_news_cat')}` (
  `identifier` int unsigned not null auto_increment primary key,
  `label` varchar(100) NOT NULL default '',
  `url_key` varchar(50) NOT NULL default ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Kontilint News Categories';

INSERT INTO `kontilint_news_cat` (`identifier`, `label`, `url_key`) VALUES (1, 'News', 'news');
");

$installer->getConnection()->addConstraint('FK_KONTILINT_NEWS_STORE_STORE',
    $installer->getTable('kontilint_news_store'), 'store_id',
    $installer->getTable('core_store'), 'store_id',
    'CASCADE', 'CASCADE', true
);

$installer->getConnection()->addConstraint('kontilint_news_cat_ibfk_1',
    $installer->getTable('kontilint_news'), 'category',
    $installer->getTable('kontilint_news_cat'), 'identifier',
    'CASCADE', 'CASCADE', true
);
$installer->getConnection()->addConstraint('kontilint_news_store_ibfk_1',
    $installer->getTable('kontilint_news_store'), 'news_id',
    $installer->getTable('kontilint_news'), 'news_id',
    'CASCADE', 'CASCADE', true
);

$installer->setConfigData('knews/list/items_per_page', '10');
$installer->setConfigData('knews/list/page_title', 'News');
$installer->setConfigData('knews/list/identifier', 'news');
$installer->setConfigData('knews/detail/title_prefix', 'News - ');

$installer->endSetup(); 
