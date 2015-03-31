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
 * Favourites Data Helper
 *
 * @category   Mage
 * @package    Submarine_Favourites
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Submarine_Favourites_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Config key 'Display Favourites Summary'
     */
    const XML_PATH_WISHLIST_LINK_USE_QTY = 'favourites/favourites_link/use_qty';

    /**
     * Config key 'Display Out of Stock Products'
     */
    const XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK = 'cataloginventory/options/show_out_of_stock';

    /**
     * Customer Favourites instance
     *
     * @var Submarine_Favourites_Model_Favourites
     */
    protected $_favourites = null;

    /**
     * Favourites Product Items Collection
     *
     * @var Submarine_Favourites_Model_Mysql4_Product_Collection
     */
    protected $_productCollection = null;

    /**
     * Favourites Items Collection
     *
     * @var Submarine_Favourites_Model_Mysql4_Item_Collection
     */
    protected $_favouritesItemCollection = null;

    /**
     * Retreive customer session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Retrieve customer login status
     *
     * @return bool
     */
    protected function _isCustomerLogIn()
    {
        return $this->_getCustomerSession()->isLoggedIn();
    }

    /**
     * Retrieve logged in customer
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCurrentCustomer()
    {
        return $this->_getCustomerSession()->getCustomer();
    }

    /**
     * Retrieve favourites by logged in customer
     *
     * @return Submarine_Favourites_Model_Favourites
     */
    public function getFavourites()
    {
        if (is_null($this->_favourites)) {
            if (Mage::registry('shared_favourites')) {
                $this->_favourites = Mage::registry('shared_favourites');
            }
            elseif (Mage::registry('favourites')) {
                $this->_favourites = Mage::registry('favourites');
            }
            else {
                $this->_favourites = Mage::getModel('favourites/favourites');
                if ($this->_getCustomerSession()->isLoggedIn()) {
                    $this->_favourites->loadByCustomer($this->_getCustomerSession()->getCustomer());
                }
            }
        }
        return $this->_favourites;
    }

    /**
     * Retrieve favourites items availability
     *
     * @deprecated after 1.6.0.0
     *
     * @return bool
     */
    public function hasItems()
    {
        return $this->getFavourites()->getItemsCount() > 0;
    }

    /**
     * Retrieve favourites item count (include config settings)
     * Used in top link menu only
     *
     * @return int
     */
    public function getItemCount()
    {
        $storedDisplayType = $this->_getCustomerSession()->getFavouritesDisplayType();
        $currentDisplayType = Mage::getStoreConfig(self::XML_PATH_WISHLIST_LINK_USE_QTY);

        $storedDisplayOutOfStockProducts = $this->_getCustomerSession()->getDisplayOutOfStockProducts();
        $currentDisplayOutOfStockProducts = Mage::getStoreConfig(self::XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK);
        if (!$this->_getCustomerSession()->hasFavouritesItemCount()
                || ($currentDisplayType != $storedDisplayType)
                || $this->_getCustomerSession()->hasDisplayOutOfStockProducts()
                || ($currentDisplayOutOfStockProducts != $storedDisplayOutOfStockProducts)) {
            $this->calculate();
        }

        return $this->_getCustomerSession()->getFavouritesItemCount();
    }

    /**
     * Retrieve favourites product items collection
     *
     * alias for getProductCollection
     *
     * @deprecated after 1.4.2.0
     * @see Submarine_Favourites_Model_Favourites::getItemCollection()
     *
     * @return Submarine_Favourites_Model_Mysql4_Product_Collection
     */
    public function getItemCollection()
    {
        return $this->getProductCollection();
    }


    /**
     * Retrieve favourites items collection
     *
     * @return Submarine_Favourites_Model_Mysql4_Item_Collection
     */
    public function getFavouritesItemCollection()
    {
        if (is_null($this->_favouritesItemCollection)) {
            $this->_favouritesItemCollection = $this->getFavourites()
                ->getItemCollection();
        }
        return $this->_favouritesItemCollection;
    }


    /**
     * Retrieve favourites product items collection
     *
     * @deprecated after 1.4.2.0
     * @see Submarine_Favourites_Model_Favourites::getItemCollection()
     *
     * @return Submarine_Favourites_Model_Mysql4_Product_Collection
     */
    public function getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $this->_productCollection = $this->getFavourites()
                ->getProductCollection();

            Mage::getSingleton('catalog/product_visibility')
                ->addVisibleInSiteFilterToCollection($this->_productCollection);
        }
        return $this->_productCollection;
    }

    /**
     * Retrieve Item Store for URL
     *
     * @param Mage_Catalog_Model_Product|Submarine_Favourites_Model_Item $item
     * @return Mage_Core_Model_Store
     */
    protected function _getUrlStore($item)
    {
        $storeId = null;
        $product = null;
        if ($item instanceof Submarine_Favourites_Model_Item) {
            $product = $item->getProduct();
        } elseif ($item instanceof Mage_Catalog_Model_Product) {
            $product = $item;
        }
        if ($product) {
            if ($product->isVisibleInSiteVisibility()) {
                $storeId = $product->getStoreId();
            }
            else if ($product->hasUrlDataObject()) {
                $storeId = $product->getUrlDataObject()->getStoreId();
            }
        }
        return Mage::app()->getStore($storeId);
    }

    /**
     * Retrieve URL for removing item from favourites
     *
     * @param Mage_Catalog_Model_Product|Submarine_Favourites_Model_Item $item
     * @return string
     */
    public function getRemoveUrl($item)
    {
        return $this->_getUrl('favourites/index/remove',
            array('item' => $item->getFavouritesItemId())
        );
    }

    /**
     * Retrieve URL for removing item from favourites
     *
     * @param Mage_Catalog_Model_Product|Submarine_Favourites_Model_Item $item
     * @return string
     */
    public function getConfigureUrl($item)
    {
        return $this->_getUrl('favourites/index/configure', array(
            'item' => $item->getFavouritesItemId()
        ));
    }

    /**
     * Retrieve url for adding product to favourites
     *
     * @param Mage_Catalog_Model_Product|Submarine_Favourites_Model_Item $item
     *
     * @return  string|bool
     */
    public function getAddUrl($item)
    {
        return $this->getAddUrlWithParams($item);
    }
    
    public function getAddWishlistUrl($item)
    {
        $productId = null;
        if ($item instanceof Mage_Catalog_Model_Product) {
            $productId = $item->getEntityId();
        }
        if ($item instanceof Submarine_Favourites_Model_Item) {
            $productId = $item->getProductId();
        }

        if ($productId) {
            $params['product'] = $productId;
            return $this->_getUrlStore($item)->getUrl('favourites/wishlist/add', $params);
        }

        return false;
    }
    
    public function getFavoriteProductUrl($item)
    {
        return $this->_getUrlStore($item)->getUrl('favourites/wishlist/getfavoriteproduct');
    }
	
	public function getAjaxFavoriteProductUrl($item)
    {
        return $this->_getUrlStore($item)->getUrl('ajax/favourites_product/add');
    }
    
    public function getFavoriteFillingUrl($item)
    {
        return $this->_getUrlStore($item)->getUrl('favourites/index/getfavoritefilling');
    }
	
	public function getAjaxFavoriteFillingUrl($item)
    {
        return $this->_getUrlStore($item)->getUrl('ajax/favourites_filling/add');
    }

    /**
     * Retrieve url for updating product in favourites
     *
     * @param Mage_Catalog_Model_Product|Submarine_Favourites_Model_Item $item
     *
     * @return  string|bool
     */
    public function getUpdateUrl($item)
    {
        $itemId = null;
        if ($item instanceof Mage_Catalog_Model_Product) {
            $itemId = $item->getFavouritesItemId();
        }
        if ($item instanceof Submarine_Favourites_Model_Item) {
            $itemId = $item->getId();
        }

        if ($itemId) {
            $params['id'] = $itemId;
            return $this->_getUrlStore($item)->getUrl('favourites/index/updateItemOptions', $params);
        }

        return false;
    }

    /**
     * Retrieve url for adding product to favourites with params
     *
     * @param Mage_Catalog_Model_Product|Submarine_Favourites_Model_Item $item
     * @param array $params
     *
     * @return  string|bool
     */
    public function getAddUrlWithParams($item, array $params = array())
    {
        $productId = null;
        if ($item instanceof Mage_Catalog_Model_Product) {
            $productId = $item->getEntityId();
        }
        if ($item instanceof Submarine_Favourites_Model_Item) {
            $productId = $item->getProductId();
        }

        if ($productId) {
            $params['product'] = $productId;
            return $this->_getUrlStore($item)->getUrl('favourites/index/add', $params);
        }

        return false;
    }

    /**
     * Retrieve URL for adding item to shoping cart
     *
     * @param string|Mage_Catalog_Model_Product|Submarine_Favourites_Model_Item $item
     * @return  string
     */
    public function getAddToCartUrl($item)
    {
        $urlParamName = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $continueUrl  = Mage::helper('core')->urlEncode(
            Mage::getUrl('*/*/*', array(
                '_current'      => true,
                '_use_rewrite'  => true,
                '_store_to_url' => true,
            ))
        );

        $urlParamName = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $params = array(
            'item' => is_string($item) ? $item : $item->getFavouritesItemId(),
            $urlParamName => $continueUrl
        );
        return $this->_getUrlStore($item)->getUrl('favourites/index/cart', $params);
    }

    /**
     * Retrieve URL for adding item to shoping cart from shared favourites
     *
     * @param string|Mage_Catalog_Model_Product|Submarine_Favourites_Model_Item $item
     * @return  string
     */
    public function getSharedAddToCartUrl($item)
    {
        $continueUrl  = Mage::helper('core')->urlEncode(Mage::getUrl('*/*/*', array(
            '_current'      => true,
            '_use_rewrite'  => true,
            '_store_to_url' => true,
        )));

        $urlParamName = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $params = array(
            'item' => is_string($item) ? $item : $item->getFavouritesItemId(),
            $urlParamName => $continueUrl
        );
        return $this->_getUrlStore($item)->getUrl('favourites/shared/cart', $params);
    }

    /**
     * Retrieve url for adding item to shoping cart with b64 referer
     *
     * @deprecated
     * @param   Mage_Catalog_Model_Product|Submarine_Favourites_Model_Item $item
     * @return  string
     */
    public function getAddToCartUrlBase64($item)
    {
        return $this->getAddToCartUrl($item);
    }

    /**
     * Retrieve customer favourites url
     *
     * @return string
     */
    public function getListUrl()
    {
        return $this->_getUrl('favourites');
    }

    /**
     * Check is allow favourites module
     *
     * @return bool
     */
    public function isAllow()
    {
        if ($this->isModuleOutputEnabled() && Mage::getStoreConfig('favourites/general/active')) {
            return true;
        }
        return false;
    }

    /**
     * Check is allow favourites action in shopping cart
     *
     * @return bool
     */
    public function isAllowInCart()
    {
        return $this->isAllow() && $this->_isCustomerLogIn();
    }

    /**
     * Retrieve customer name
     *
     * @return string
     */
    public function getCustomerName()
    {
        return $this->_getCurrentCustomer()->getName();
    }

    /**
     * Retrieve RSS URL
     *
     * @return string
     */
    public function getRssUrl()
    {
        $customer = $this->_getCurrentCustomer();
        $key = $customer->getId().','.$customer->getEmail();
        return $this->_getUrl(
            'rss/index/favourites',
            array(
                'data' => Mage::helper('core')->urlEncode($key),
                '_secure' => false
            )
        );
    }

    /**
     * Is allow RSS
     *
     * @return bool
     */
    public function isRssAllow()
    {
        return Mage::getStoreConfigFlag('rss/favourites/active');
    }

    /**
     * Retrieve default empty comment message
     *
     * @return string
     */
    public function defaultCommentString()
    {
        return $this->__('Please, enter your comments...');
    }

    /**
     * Calculate count of favourites items and put value to customer session.
     * Method called after favourites modifications and trigger 'favourites_items_renewed' event.
     * Depends from configuration.
     *
     * @return Submarine_Favourites_Helper_Data
     */
    public function calculate()
    {
        $session = $this->_getCustomerSession();
        $count = 0;
        if ($this->_isCustomerLogIn()) {
            $collection = $this->getFavouritesItemCollection()->setInStockFilter(true);
            if (Mage::getStoreConfig(self::XML_PATH_WISHLIST_LINK_USE_QTY)) {
                $count = $collection->getItemsQty();
            } else {
                $count = $collection->getSize();
            }
            $session->setFavouritesDisplayType(Mage::getStoreConfig(self::XML_PATH_WISHLIST_LINK_USE_QTY));
            $session->setDisplayOutOfStockProducts(
                Mage::getStoreConfig(self::XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK)
            );
        }
        $session->setFavouritesItemCount($count);
        Mage::dispatchEvent('favourites_items_renewed');
        return $this;
    }

    /**
     * Returns array of bundle options in json format
     *
     * @return json
     */
    public function getFavouritesJson()
    {
        $result = 'null';
        $favItemCollection = $this->getFavourites()->getItemCollection();
        if (count($favItemCollection)){
            $items = array();
            $helper =  Mage::helper('favourites/configuration');
            foreach($favItemCollection as $item){
                $itemData = array();
                $itemData['name'] = $item->getName();
                $itemData['fillingid'] = $item->getId();
                $itemData['description'] = $item->getDescription();
                $optionIds = $helper->getBundleOptionsIds($item);
                $itemData['optionsids'] = ($optionIds ? $optionIds : array());
                $items[] = $itemData;
            }
            if (isset($items) && count($items)>0) {
                $result = json_encode($items);
            }
        }
        return $result;
    }
    
    public function getWishlistJson()
    {
//        $result = 'null';
//
//        $helper =  Mage::helper('wishlist');
//        $wishlistItemCollection = $helper->getWishlist()->getItemCollection();
//        if ($helper->hasItems()) {
//            $helperConfiguration =  Mage::helper('favourites/configuration');
//            foreach($wishlistItemCollection as $item){
//                $itemData = array();
//                $itemData['id'] = $item->getId();
//                $itemData['product_id'] = $item->getProductId();
//                $itemData['name'] = $item->getName();
//                $itemData['description'] = $item->getDescription();
//                $optionIds = $helperConfiguration->getBundleOptionsForWishList($item);
//                $itemData['optionsids'] = ($optionIds ? $optionIds : array());
//                $items[] = $itemData;
//            }
//            if (isset($items) && count($items)>0) {
//                $result = json_encode($items);
//            }
//        }
//        return $result;
        
        
        
        return json_encode($this->getWishlistData());
    }
    
    public function getWishlistData()
    {
        $result = 'null';

        $helper =  Mage::helper('wishlist');
        
        $helper->getWishlist()->loadByCustomer($this->_getCustomerSession()->getCustomer());
        $wishlistItemCollection = $helper->getWishlist()->getItemCollection();
        if ($helper->hasItems()) {
            $helperConfiguration =  Mage::helper('favourites/configuration');
            foreach($wishlistItemCollection as $item){
                $itemData = array();
                $itemData['id'] = $item->getId();
                $itemData['product_id'] = $item->getProductId();
                $itemData['name'] = $item->getName();
                $itemData['description'] = $item->getDescription();
                $optionIds = $helperConfiguration->getBundleOptionsForWishList($item);
                $itemData['optionsids'] = ($optionIds ? $optionIds : array());
                $items[] = $itemData;
            }
            if (isset($items) && count($items)>0) {
                $result = $items;
            }
        }
        return $result;
    }
}
