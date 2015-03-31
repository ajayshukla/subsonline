<?php

/**
 * Ajax Shopping cart controller
 */

require 'app/code/core/Mage/Checkout/controllers/CartController.php';
class Submarine_Ajax_Checkout_CartController extends Mage_Checkout_CartController
{
    public function addAction() {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();

        if(isset($params['isAjax']) == 1) {
            $response = array();
            try {
                if (isset($params['qty'])) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }

                $product = $this->_initProduct();
                $related = $this->getRequest()->getParam('related_product');

                /**
                * Check product availability
                */
                if (!$product) {
                    $response['status'] = 'ERROR';
                    $response['message'] = $this->__('Unable to find Product ID');
                }

                $cart->addProduct($product, $params);
                if (!empty($related)) {
                    $cart->addProductsByIds(explode(',', $related));
                }

                $cart->save();

                $this->_getSession()->setCartWasUpdated(true);

                /**
                * @todo remove wishlist observer processAddToCart
                */
                Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );

                if (!$this->_getSession()->getNoCartRedirect(true)) {
                    if (!$cart->getQuote()->getHasError()) {
                        //$this->cleanUserSidebarCart();
                        
                        $response['status']  = 'SUCCESS';
                        $response['message'] = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
                        $response['locale']  = array(
                            'Basket' => $this->__('Basket'),
                        );
                        $response['cart_items_count'] = $cart->getItemsQty();

                        $this->loadLayout();
                        $response['sidebar'] = $this->getLayout()->getBlock('cart_sidebar')->toHtml();

                    }
                }
            } catch (Mage_Core_Exception $e) {
                $msg = "";
                if ($this->_getSession()->getUseNotice(true)) {
                    $msg = $e->getMessage();
                } else {
                    $msg = implode(" \n", array_unique(explode("\n", $e->getMessage())));
                }
                $response['status'] = 'ERROR';
                $response['message'] = $msg;
            } catch (Exception $e) {
                $response['status'] = 'ERROR';
                $response['message'] = $this->__('Cannot add the item to shopping cart.');
                Mage::logException($e);
            }
        } else {
            $this->_redirectReferer(Mage::getUrl('*/*'));
            return;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

    public function optionsAction(){
        $productId = $this->getRequest()->getParam('product_id');
        // Prepare helper and params
        $viewHelper = Mage::helper('catalog/product_view');

        $params = new Varien_Object();
        $params->setCategoryId(false);
        $params->setSpecifyOptions(false);

        $oProductHelper = new Mage_Catalog_Helper_Product();
        $oProductSimple = $oProductHelper->getProduct($productId, null, 'id');

        $aSKU = explode('-', $oProductSimple->getSKU());
        $oProductBundle = Mage::getModel('catalog/product');
        $productId = $oProductBundle->getIdBySku($aSKU[0]);

        // Render page
        try {
            $viewHelper->prepareAndRender($productId, $this, $params);
        } catch (Exception $e) {
            if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
                if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                    $this->_redirect('');
                } elseif (!$this->getResponse()->isRedirect()) {
                    $this->_forward('noRoute');
                }
            } else {
                Mage::logException($e);
                $this->_forward('noRoute');
            }
        }
    }

}
