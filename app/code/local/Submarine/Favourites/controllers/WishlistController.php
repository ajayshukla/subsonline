<?php

require 'app/code/core/Mage/Wishlist/controllers/IndexController.php';

class Submarine_Favourites_WishlistController extends Mage_Wishlist_IndexController {

    public function addAction()
    {
        $session = Mage::getSingleton('customer/session');
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            $this->_redirect('*/');
            return;
        }

        $productId = (int) $this->getRequest()->getParam('product');
        if (!$productId) {
            $this->_redirect('*/');
            return;
        }

        $itemDescription = (string)$this->getRequest()->getParam('description');
        if (!isset($itemDescription)) {
            $this->_redirect('*/');
            return;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $session->addError($this->__('Cannot specify product.'));
            $this->_redirect('*/');
            return;
        }

        try {
            $requestParams = $this->getRequest()->getParams();
            if ($session->getBeforeWishlistRequest()) {
                $requestParams = $session->getBeforeWishlistRequest();
                if (isset($requestParams['description'])) {
                    $itemDescription = $requestParams['description'];
                }
                $session->unsBeforeWishlistRequest();
            }
            $buyRequest = new Varien_Object($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }

            $itemId = $result->getId();
            $item = Mage::getModel('wishlist/item');
            $item->load($itemId);
            if ($item) {
                $item->setDescription($itemDescription)
                     ->save();
            }

            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist'  => $wishlist,
                    'product'   => $product,
                    'item'      => $result
                )
            );

            $referer = $session->getBeforeFavouritesUrl();
            if ($referer) {
                $session->setBeforeFavouritesUrl(null);
            } else {
                $referer = $this->_getRefererUrl();
            }

            /**
             *  Set referer to avoid referring to the compare popup window
             */

            Mage::helper('wishlist')->calculate();

            $message = $this->__('%1$s has been added to your favourites.');

            $aItem = array();
            $aItem['id'] = $result->getId();
            $aItem['product_id'] = $result->getProductId();
            $aItem['description'] = $itemDescription;
            $aItem['type'] = 'favorite_product';

            $response['status'] = 'SUCCESS';
            $response['message'] = $message;
            $response['sidebar'] = $aItem;
        }
        catch (Mage_Core_Exception $e) {
            $session->addError($this->__('An error occurred while adding item to favourites: %s', $e->getMessage()));
        }
        catch (Exception $e) {
            $session->addError($this->__('An error occurred while adding item to favourites.'));
        }

		$this->cleanCachePageCategory();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }
    
    public function getFavoriteProductAction() {
        return $this->getResponse()->setBody(Mage::helper('favourites')->getWishlistJson());
    }
}

?>
