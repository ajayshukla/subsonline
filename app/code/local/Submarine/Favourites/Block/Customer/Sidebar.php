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
 * Favourites sidebar block
 *
 * @category   Mage
 * @package    Submarine_Favourites
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Submarine_Favourites_Block_Customer_Sidebar extends Submarine_Favourites_Block_Abstract
{
    /**
     * Add sidebar conditions to collection
     *
     * @param  Submarine_Favourites_Model_Resource_Item_Collection $collection
     * @return Submarine_Favourites_Block_Customer_Favourites
     */
    protected function _prepareCollection($collection)
    {
        $collection->setCurPage(1)
            ->setPageSize(3)
            ->setInStockFilter(true)
            ->setOrder('added_at');

        return $this;
    }

    /**
     * Prepare before to html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (($this->getCustomFavourites() && $this->getItemCount()) || $this->hasFavouritesItems()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * Can Display favourites
     *
     * @return bool
     */
    public function getCanDisplayFavourites()
    {
        return $this->_getCustomerSession()->isLoggedIn();
    }

    /**
     * Retrieve URL for removing item from favourites
     *
     * @deprecated back compatibility alias for getItemRemoveUrl
     * @param  Submarine_Favourites_Model_Item $item
     * @return string
     */
    public function getRemoveItemUrl($item)
    {
        return $this->getItemRemoveUrl($item);
    }

    /**
     * Retrieve URL for adding product to shopping cart and remove item from favourites
     *
     * @deprecated
     * @param  Mage_Catalog_Model_Product|Submarine_Favourites_Model_Item $product
     * @return string
     */
    public function getAddToCartItemUrl($product)
    {
        return $this->getItemAddToCartUrl($product);
    }

    /**
     * Retrieve Favourites model
     *
     * @return Submarine_Favourites_Model_Favourites
     */
    protected function _getFavourites()
    {

        if (!$this->getCustomFavourites() || !is_null($this->_favourites)) {
            return parent::_getFavourites();
        }

        $this->_favourites = $this->getCustomFavourites();
        return $this->_favourites;
    }

    /**
     * Return favourites items count
     *
     * @return int
     */
    public function getItemCount()
    {
        if ($this->getCustomFavourites()) {
            return $this->getCustomFavourites()->getItemsCount();
        }

        return $this->getFavouritesItemsCount();
    }
}
