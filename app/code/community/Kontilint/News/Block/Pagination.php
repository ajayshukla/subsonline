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

class Kontilint_News_Block_Pagination extends Mage_Core_Block_Template
{
    
	public function getPaginator()     
	{ 

		if ( !$this->hasData('items') ) {
			$this->setData('items', Mage::registry('items'));
		}
		return $this->getData('items')->getPages('Sliding');
        
	}
	
}
