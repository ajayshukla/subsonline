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
 * @copyright  Copyright (c) 2009 Kontilint Agency SA
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Kontilint
 * @package    Kontilint_News
 * @author     Mattias Bomelin <mattias@kontilint.se>
 */

class Kontilint_News_Model_News extends Mage_Core_Model_Abstract
{
	
    public function _construct()
    {
        parent::_construct();
        $this->_init('knews/news');
    }

    /**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param   string $identifier
     * @param   int $storeId
     * @return  int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }
	public function checkCategoryIdentifier($identifier, $storeId)
	{
		return $this->_getResource()->checkCategoryIdentifier($identifier, $storeId);
	}
	public function getCategorys() {
		return $this->_getResource()->getCategorys();
	}
    
}


