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
 * Favourites Product collection
 * Deprecated because after Magento 1.4.2.0 it's impossible
 * to use product collection in favourites
 *
 * @deprecated after 1.4.2.0
 * @category   Mage
 * @package    Submarine_Favourites
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Submarine_Favourites_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Add days in whishlist filter of product collection
     *
     * @var boolean
     */
    protected $_addDaysInFavourites  = false;

    /**
     * Favourites item table alias
     * @var string
     */
    protected $_favouritesItemTableAlias         = 't_wi';

    /**
     * Get add days in whishlist filter of product collection flag
     *
     * @return boolean
     */
    public function getDaysInFavourites()
    {
        return $this->_addDaysInFavourites;
    }

    /**
     * Set add days in whishlist filter of product collection flag
     *
     * @param unknown_type $flag
     * @return Submarine_Favourites_Model_Resource_Product_Collection
     */
    public function setDaysInFavourites($flag)
    {
        $this->_addDaysInFavourites = (bool) $flag;
        return $this;
    }

    /**
     * Add favourites filter to collection
     *
     * @param Submarine_Favourites_Model_Favourites $favourites
     * @return Submarine_Favourites_Model_Resource_Product_Collection
     */
    public function addFavouritesFilter(Submarine_Favourites_Model_Favourites $favourites)
    {
        $this->joinTable(
            array($this->_favouritesItemTableAlias => 'favourites/item'),
            'product_id=entity_id',
            array(
                'product_id'                => 'product_id',
                'favourites_item_description' => 'description',
                'item_store_id'             => 'store_id',
                'added_at'                  => 'added_at',
                'favourites_id'               => 'favourites_id',
                'favourites_item_id'          => 'favourites_item_id',
            ),
            array(
                'favourites_id'               => $favourites->getId(),
                'store_id'                  => array('in' => $favourites->getSharedStoreIds())
            )
        );

        $this->_productLimitationFilters['store_table']  = $this->_favouritesItemTableAlias;

        $this->setFlag('url_data_object', true);
        $this->setFlag('do_not_use_category_id', true);

        return $this;
    }

    /**
     * Add favourites sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Submarine_Favourites_Model_Resource_Product_Collection
     */
    public function addWishListSortOrder($attribute = 'added_at', $dir = 'desc')
    {
        $this->setOrder($attribute, $dir);
        return $this;
    }

    /**
     * Reset sort order
     *
     * @return Submarine_Favourites_Model_Resource_Product_Collection
     */
    public function resetSortOrder()
    {
        $this->getSelect()->reset(Zend_Db_Select::ORDER);
        return $this;
    }

    /**
     * Add store data (days in favourites)
     *
     * @return Submarine_Favourites_Model_Resource_Product_Collection
     */
    public function addStoreData()
    {
        $adapter = $this->getConnection();
        if (!$this->getDaysInFavourites()) {
            return $this;
        }

        $this->setDaysInFavourites(false);

        $resourceHelper = Mage::getResourceHelper('core');
        $nowDate = $adapter->formatDate(Mage::getSingleton('core/date')->date());

        $this->joinField('store_name', 'core/store', 'name', 'store_id=item_store_id');
        $this->joinField('days_in_favourites',
            'favourites/item',
            $resourceHelper->getDateDiff($this->_favouritesItemTableAlias . '.added_at', $nowDate),
            'favourites_item_id=favourites_item_id'
        );

        return $this;
    }

    /**
     * Rewrite retrieve attribute field name for favourites attributes
     *
     * @param string $attributeCode
     * @return Submarine_Favourites_Model_Resource_Product_Collection
     */
    protected function _getAttributeFieldName($attributeCode)
    {
        if ($attributeCode == 'days_in_favourites') {
            return $this->_joinFields[$attributeCode]['field'];
        }
        return parent::_getAttributeFieldName($attributeCode);
    }

    /**
     * Prevent loading collection because after Magento 1.4.2.0 it's impossible
     * to use product collection in favourites
     *
     * @return bool
     */
    public function load($printQuery = false, $logQuery = false)
    {
        return $this;
    }
}
