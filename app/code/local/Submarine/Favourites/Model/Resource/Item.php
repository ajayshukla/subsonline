<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Submarine_Favourites
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Favourites item model resource
 *
 * @category    Mage
 * @package     Submarine_Favourites
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Submarine_Favourites_Model_Resource_Item extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection and define main table
     *
     */
    protected function _construct()
    {
        $this->_init('favourites/item', 'favourites_item_id');
    }

    /**
     * Load item by favourites, product and shared stores
     *
     * @param Submarine_Favourites_Model_Item $object
     * @param int $favouritesId
     * @param int $productId
     * @param array $sharedStores
     * @return Submarine_Favourites_Model_Resource_Item
     */
    public function loadByProductFavourites($object, $favouritesId, $productId, $sharedStores)
    {
        $adapter = $this->_getReadAdapter();
        $storeWhere = $adapter->quoteInto('store_id IN (?)', $sharedStores);
        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where('favourites_id=:favourites_id AND '
                . 'product_id=:product_id AND '
                . $storeWhere);
        $bind = array(
            'favourites_id' => $favouritesId,
            'product_id'  => $productId
        );
        $data = $adapter->fetchRow($select, $bind);
        if ($data) {
            $object->setData($data);
        }
        $this->_afterLoad($object);

        return $this;
    }
}
