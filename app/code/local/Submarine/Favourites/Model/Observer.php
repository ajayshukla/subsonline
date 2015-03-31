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
 * Shopping cart operation observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Submarine_Favourites_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Get customer favourites model instance
     *
     * @param   int $customerId
     * @return  Submarine_Favourites_Model_Favourites || false
     */
    protected function _getFavourites($customerId)
    {
        if (!$customerId) {
            return false;
        }
        return Mage::getModel('favourites/favourites')->loadByCustomer($customerId, true);
    }

    /**
     * Check move quote item to favourites request
     *
     * @param   Varien_Event_Observer $observer
     * @return  Submarine_Favourites_Model_Observer
     */
    public function processCartUpdateBefore($observer)
    {
        $cart = $observer->getEvent()->getCart();
        $data = $observer->getEvent()->getInfo();
        $productIds = array();

        $favourites = $this->_getFavourites($cart->getQuote()->getCustomerId());
        if (!$favourites) {
            return $this;
        }

        /**
         * Collect product ids marked for move to favourites
         */
        foreach ($data as $itemId => $itemInfo) {
            if (!empty($itemInfo['favourites'])) {
                if ($item = $cart->getQuote()->getItemById($itemId)) {
                    $productId  = $item->getProductId();
                    $buyRequest = $item->getBuyRequest();

                    if (isset($itemInfo['qty']) && is_numeric($itemInfo['qty'])) {
                        $buyRequest->setQty($itemInfo['qty']);
                    }
                    $favourites->addNewItem($productId, $buyRequest);

                    $productIds[] = $productId;
                    $cart->getQuote()->removeItem($itemId);
                }
            }
        }

        if (!empty($productIds)) {
            $favourites->save();
            Mage::helper('favourites')->calculate();
        }
        return $this;
    }

    public function processAddToCart($observer)
    {
        $request = $observer->getEvent()->getRequest();
        $sharedFavourites = Mage::getSingleton('checkout/session')->getSharedFavourites();
        $messages = Mage::getSingleton('checkout/session')->getFavouritesPendingMessages();
        $urls = Mage::getSingleton('checkout/session')->getFavouritesPendingUrls();
        $favouritesIds = Mage::getSingleton('checkout/session')->getFavouritesIds();
        $singleFavouritesId = Mage::getSingleton('checkout/session')->getSingleFavouritesId();

        if ($singleFavouritesId) {
            $favouritesIds = array($singleFavouritesId);
        }

        if (count($favouritesIds) && $request->getParam('favourites_next')){
            $favouritesId = array_shift($favouritesIds);

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $favourites = Mage::getModel('favourites/favourites')
                        ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
            } else if ($sharedFavourites) {
                $favourites = Mage::getModel('favourites/favourites')->loadByCode($sharedFavourites);
            } else {
                return;
            }


            $favourites->getItemCollection()->load();

            foreach($favourites->getItemCollection() as $favouritesItem){
                if ($favouritesItem->getId() == $favouritesId)
                    $favouritesItem->delete();
            }
            Mage::getSingleton('checkout/session')->setFavouritesIds($favouritesIds);
            Mage::getSingleton('checkout/session')->setSingleFavouritesId(null);
        }

        if ($request->getParam('favourites_next') && count($urls)) {
            $url = array_shift($urls);
            $message = array_shift($messages);

            Mage::getSingleton('checkout/session')->setFavouritesPendingUrls($urls);
            Mage::getSingleton('checkout/session')->setFavouritesPendingMessages($messages);

            Mage::getSingleton('checkout/session')->addError($message);

            $observer->getEvent()->getResponse()->setRedirect($url);
            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
        }
    }

    /**
     * Customer login processing
     *
     * @param Varien_Event_Observer $observer
     * @return Submarine_Favourites_Model_Observer
     */
    public function customerLogin(Varien_Event_Observer $observer)
    {
        Mage::helper('favourites')->calculate();

        return $this;
    }

    /**
     * Customer logout processing
     *
     * @param Varien_Event_Observer $observer
     * @return Submarine_Favourites_Model_Observer
     */
    public function customerLogout(Varien_Event_Observer $observer)
    {
        Mage::getSingleton('customer/session')->setFavouritesItemCount(0);

        return $this;
    }

}
