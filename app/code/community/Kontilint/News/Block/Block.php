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

class Kontilint_News_Block_Block extends Mage_Core_Block_Template
{

    public function getItems($limit = 3, $category = 0)     
	{ 
		$coll = Mage::getModel('knews/news')->getCollection()
							->addStoreFilter(Mage::app()->getStore(true)->getId())
							->addFieldToFilter('main_table.is_active', 1);
		if ($category > 0) $coll->addFieldToFilter('main_table.category', $category);
		$coll->addOrder('main_table.date', 'desc');

		if ($limit > 0) $coll->setPageSize($limit);
		$collection = $coll->getData();
		return $collection;
        
	}
    
}
