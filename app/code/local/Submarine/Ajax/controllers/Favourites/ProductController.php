<?php

class Submarine_Ajax_Favourites_ProductController extends Mage_Wishlist_Controller_Abstract {
	
	protected function _getWishlist()
    {
        $wishlist = Mage::registry('wishlist');
        if ($wishlist) {
            return $wishlist;
        }

        try {
            $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('wishlist/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('wishlist/session')->addException($e,
                Mage::helper('wishlist')->__('Cannot create wishlist.')
            );
            return false;
        }

        return $wishlist;
    }
	
	public function addAction()
	{
		$response = $this->getResponse();
		$result   = new Varien_Object();
		
        $session = Mage::getSingleton('customer/session');
		if ($session->getBeforeWishlistRequest()) {
			$session->unsBeforeWishlistRequest();
		}

        $requestParams = $this->getRequest()->getParams();
		$session->setBeforeWishlistRequest($requestParams);

		$session->setBeforeFavouritesUrl($this->_getRefererUrl());
		$result->status = "OK";
		return $response->setBody($result->toJSON());
	}
}

?>
