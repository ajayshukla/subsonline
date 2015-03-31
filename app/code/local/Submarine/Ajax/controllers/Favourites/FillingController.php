<?php


class Submarine_Ajax_Favourites_FillingController extends Submarine_Favourites_Controller_Abstract {
	
	protected function _getFavourites()
    {
        $favourites = Mage::registry('favourites');
        if ($favourites) {
            return $favourites;
        }

        try {
            $favourites = Mage::getModel('favourites/favourites')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
            Mage::register('favourites', $favourites);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('favourites/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('favourites/session')->addException($e,
                Mage::helper('favourites')->__('Cannot create favourites.')
            );
            return false;
        }
        return $favourites;
    }

	public function addAction()
    {
		$response = $this->getResponse();
		$result   = new Varien_Object();

        $session = Mage::getSingleton('customer/session');
		if ($session->getBeforeFavouritesRequest()) {
			$session->unsBeforeFavouritesRequest();
		}

        $requestParams = $this->getRequest()->getParams();
		$session->setBeforeFavouritesRequest($requestParams);

		$session->setBeforeFavouritesUrl($this->_getRefererUrl());
		$result->status = "OK";

		return $response->setBody($result->toJSON());
    }

}

?>
