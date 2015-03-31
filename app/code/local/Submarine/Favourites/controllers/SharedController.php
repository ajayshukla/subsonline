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
 * Favourites shared items controllers
 *
 * @category    Mage
 * @package     Submarine_Favourites
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Submarine_Favourites_SharedController extends Submarine_Favourites_Controller_Abstract
{
    /**
     * Retrieve favourites instance by requested code
     *
     * @return Submarine_Favourites_Model_Favourites|false
     */
    protected function _getFavourites()
    {
        $code     = (string)$this->getRequest()->getParam('code');
        if (empty($code)) {
            return false;
        }

        $favourites = Mage::getModel('favourites/favourites')->loadByCode($code);
        if (!$favourites->getId()) {
            return false;
        }

        Mage::getSingleton('checkout/session')->setSharedFavourites($code);

        return $favourites;
    }

    /**
     * Shared favourites view page
     *
     */
    public function indexAction()
    {
        $favourites   = $this->_getFavourites();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();

        if ($favourites && $favourites->getCustomerId() && $favourites->getCustomerId() == $customerId) {
            $this->_redirectUrl(Mage::helper('favourites')->getListUrl());
            return;
        }

        Mage::register('shared_favourites', $favourites);

        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('favourites/session');
        $this->renderLayout();
    }

    /**
     * Add shared favourites item to shopping cart
     *
     * If Product has required options - redirect
     * to product view page with message about needed defined required options
     *
     */
    public function cartAction()
    {
        $itemId = (int) $this->getRequest()->getParam('item');

        /* @var $item Submarine_Favourites_Model_Item */
        $item = Mage::getModel('favourites/item')->load($itemId);


        /* @var $session Submarine_Favourites_Model_Session */
        $session    = Mage::getSingleton('favourites/session');
        $cart       = Mage::getSingleton('checkout/cart');

        $redirectUrl = $this->_getRefererUrl();

        try {
            $options = Mage::getModel('favourites/item_option')->getCollection()
                    ->addItemFilter(array($itemId));
            $item->setOptions($options->getOptionsByItem($itemId));

            $item->addToCart($cart);
            $cart->save()->getQuote()->collectTotals();

            if (Mage::helper('checkout/cart')->getShouldRedirectToCart()) {
                $redirectUrl = Mage::helper('checkout/cart')->getCartUrl();
            }
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() == Submarine_Favourites_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
                $session->addError(Mage::helper('favourites')->__('This product(s) is currently out of stock'));
            } else {
                Mage::getSingleton('catalog/session')->addNotice($e->getMessage());
                $redirectUrl = $item->getProductUrl();
            }
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('favourites')->__('Cannot add item to shopping cart'));
        }

        return $this->_redirectUrl($redirectUrl);
    }
}
