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
 * Favourites front controller
 *
 * @category    Mage
 * @package     Submarine_Favourites
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Submarine_Favourites_IndexController extends Submarine_Favourites_Controller_Abstract
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');

    /**
     * If true, authentication in this controller (favourites) could be skipped
     *
     * @var bool
     */
    protected $_skipAuthentication = false;

    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->_skipAuthentication && !Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
            if(!Mage::getSingleton('customer/session')->getBeforeFavouritesUrl()) {
                Mage::getSingleton('customer/session')->setBeforeFavouritesUrl($this->_getRefererUrl());
            }
            Mage::getSingleton('customer/session')->setBeforeFavouritesRequest($this->getRequest()->getParams());
        }
        if (!Mage::getStoreConfigFlag('favourites/general/active')) {
            $this->norouteAction();
            return;
        }
    }

    /**
     * Set skipping authentication in actions of this controller (favourites)
     *
     * @return Submarine_Favourites_IndexController
     */
    public function skipAuthentication()
    {
        $this->_skipAuthentication = true;
        return $this;
    }

    /**
     * Retrieve favourites object
     *
     * @return Submarine_Favourites_Model_Favourites|bool
     */
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

    /**
     * Display customer favourites
     */
    public function indexAction()
    {
        $this->_getFavourites();
        $this->loadLayout();


        $session = Mage::getSingleton('customer/session');
        $block   = $this->getLayout()->getBlock('customer.favourites');
        $referer = $session->getAddActionReferer(true);
        if ($block) {
            $block->setRefererUrl($this->_getRefererUrl());
            if ($referer) {
                $block->setRefererUrl($referer);
            }
        }

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('favourites/session');

        $this->renderLayout();
    }

    /**
     * Adding new item
     */
    public function addAction()
    {
        $session = Mage::getSingleton('customer/session');
        $favourites = $this->_getFavourites();
        if (!$favourites) {
            $this->_redirect('*/');
            return;
        }

        $productId = (int) $this->getRequest()->getParam('product');
        if (!$productId) {
            $this->_redirect('*/');
            return;
        }

        $favItemDescription = (string)$this->getRequest()->getParam('description');
        if (!isset($favItemDescription)) {
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
            if ($session->getBeforeFavouritesRequest()) {
                $requestParams = $session->getBeforeFavouritesRequest();
                if (isset($requestParams['description'])) {
                    $favItemDescription = $requestParams['description'];
                }
                $session->unsBeforeFavouritesRequest();
            }
            $buyRequest = new Varien_Object($requestParams);

            $result = $favourites->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }

            $itemId = $result->getId();
            $item = Mage::getModel('favourites/item');
            $item->load($itemId);
            if ($item) {
                $item->setDescription($favItemDescription)
                     ->save();
            }

            $favourites->save();

            Mage::dispatchEvent(
                'favourites_add_product',
                array(
                    'favourites'  => $favourites,
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

            Mage::helper('favourites')->calculate();
            
            $message = $this->__('%1$s has been added to your favourites.');
            
            $aItem = array();
            $aItem['id'] = $result->getId();
            $aItem['product_id'] = $result->getProductId();
            $aItem['description'] = $favItemDescription;
            $aItem['type'] = 'favorite_filling';

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

    /**
     * Action to reconfigure favourites item
     */
    public function configureAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $favourites = $this->_getFavourites();
        /* @var $item Submarine_Favourites_Model_Item */
        $item = $favourites->getItem($id);

        try {
            if (!$item) {
                throw new Exception($this->__('Cannot load favourites item'));
            }

            Mage::register('favourites_item', $item);

            $params = new Varien_Object();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);

            $buyRequest = $item->getBuyRequest();
            if (!$buyRequest->getQty() && $item->getQty()) {
                $buyRequest->setQty($item->getQty());
            }
            if ($buyRequest->getQty() && !$item->getQty()) {
                $item->setQty($buyRequest->getQty());
                Mage::helper('favourites')->calculate();
            }
            $params->setBuyRequest($buyRequest);

            Mage::helper('catalog/product_view')->prepareAndRender($item->getProductId(), $this, $params);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('customer/session')->addError($e->getMessage());
            $this->_redirect('*');
            return;
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__('Cannot configure product'));
            Mage::logException($e);
            $this->_redirect('*');
            return;
        }
    }

    /**
     * Action to accept new configuration for a favourites item
     */
    public function updateItemOptionsAction()
    {
        $session = Mage::getSingleton('customer/session');
        $favourites = $this->_getFavourites();
        if (!$favourites) {
            $this->_redirect('*/');
            return;
        }

        $productId = (int) $this->getRequest()->getParam('product');
        if (!$productId) {
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
            $id = (int) $this->getRequest()->getParam('id');
            $buyRequest = new Varien_Object($this->getRequest()->getParams());

            $favourites->updateItem($id, $buyRequest)
                ->save();

            Mage::helper('favourites')->calculate();
            Mage::dispatchEvent('favourites_update_item', array(
                'favourites' => $favourites, 'product' => $product, 'item' => $favourites->getItem($id))
            );

            Mage::helper('favourites')->calculate();

            $message = $this->__('%1$s has been updated in your favourites.', $product->getName());
            $session->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addError($this->__('An error occurred while updating favourites.'));
            Mage::logException($e);
        }
        $this->_redirect('*/*');
    }

    /**
     * Update favourites item comments
     */
    public function updateAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }
        $post = $this->getRequest()->getPost();
        if($post && isset($post['description']) && is_array($post['description'])) {
            $favourites = $this->_getFavourites();
            $updatedItems = 0;

            foreach ($post['description'] as $itemId => $description) {
                $item = Mage::getModel('favourites/item')->load($itemId);
                if ($item->getFavouritesId() != $favourites->getId()) {
                    continue;
                }

                // Extract new values
                $description = (string) $description;
                if (!strlen($description)) {
                    $description = $item->getDescription();
                }

                $qty = null;
                if (isset($post['qty'][$itemId])) {
                    $qty = $this->_processLocalizedQty($post['qty'][$itemId]);
                }
                if (is_null($qty)) {
                    $qty = $item->getQty();
                    if (!$qty) {
                        $qty = 1;
                    }
                } elseif (0 == $qty) {
                    try {
                        $item->delete();
                    } catch (Exception $e) {
                        Mage::logException($e);
                        Mage::getSingleton('customer/session')->addError(
                            $this->__('Can\'t delete item from favourites')
                        );
                    }
                }

                // Check that we need to save
                if (($item->getDescription() == $description) && ($item->getQty() == $qty)) {
                    continue;
                }
                try {
                    $item->setDescription($description)
                        ->setQty($qty)
                        ->save();
                    $updatedItems++;
                } catch (Exception $e) {
                    Mage::getSingleton('customer/session')->addError(
                        $this->__('Can\'t save description %s', Mage::helper('core')->htmlEscape($description))
                    );
                }
            }

            // save favourites model for setting date of last update
            if ($updatedItems) {
                try {
                    $favourites->save();
                    Mage::helper('favourites')->calculate();
                }
                catch (Exception $e) {
                    Mage::getSingleton('customer/session')->addError($this->__('Can\'t update favourites'));
                }
            }

            if (isset($post['save_and_share'])) {
                $this->_redirect('*/*/share');
                return;
            }
        }
        $this->_redirect('*');
    }

    /**
     * Remove item
     */
    public function removeAction()
    {
        $favourites = $this->_getFavourites();
        $id = (int) $this->getRequest()->getParam('item');
        $item = Mage::getModel('favourites/item')->load($id);

        if($item->getFavouritesId() == $favourites->getId()) {
            try {
                $item->delete();
                $favourites->save();
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('customer/session')->addError(
                    $this->__('An error occurred while deleting the item from favourites: %s', $e->getMessage())
                );
            }
            catch(Exception $e) {
                Mage::getSingleton('customer/session')->addError(
                    $this->__('An error occurred while deleting the item from favourites.')
                );
            }
        }

        Mage::helper('favourites')->calculate();

		$this->cleanCachePageCategory();

        $this->_redirectReferer(Mage::getUrl('*/*'));
    }

    /**
     * Add favourites item to shopping cart and remove from favourites
     *
     * If Product has required options - item removed from favourites and redirect
     * to product view page with message about needed defined required options
     *
     */
    public function cartAction()
    {
        $favourites   = $this->_getFavourites();
        if (!$favourites) {
            return $this->_redirect('*/*');
        }

        $itemId = (int) $this->getRequest()->getParam('item');

        /* @var $item Submarine_Favourites_Model_Item */
        $item = Mage::getModel('favourites/item')->load($itemId);

        if (!$item->getId() || $item->getFavouritesId() != $favourites->getId()) {
            return $this->_redirect('*/*');
        }

        // Set qty
        $qty = $this->getRequest()->getParam('qty');
        if (is_array($qty)) {
            if (isset($qty[$itemId])) {
                $qty = $qty[$itemId];
            } else {
                $qty = 1;
            }
        }
        $qty = $this->_processLocalizedQty($qty);
        if ($qty) {
            $item->setQty($qty);
        }

        /* @var $session Submarine_Favourites_Model_Session */
        $session    = Mage::getSingleton('favourites/session');
        $cart       = Mage::getSingleton('checkout/cart');

        $redirectUrl = Mage::getUrl('*/*');

        try {
            $options = Mage::getModel('favourites/item_option')->getCollection()
                    ->addItemFilter(array($itemId));
            $item->setOptions($options->getOptionsByItem($itemId));

            $buyRequest = Mage::helper('catalog/product')->addParamsToBuyRequest(
                $this->getRequest()->getParams(),
                array('current_config' => $item->getBuyRequest())
            );

            $item->mergeBuyRequest($buyRequest);
            $item->addToCart($cart, true);
            $cart->save()->getQuote()->collectTotals();
            $favourites->save();

            Mage::helper('favourites')->calculate();

            if (Mage::helper('checkout/cart')->getShouldRedirectToCart()) {
                $redirectUrl = Mage::helper('checkout/cart')->getCartUrl();
            } else if ($this->_getRefererUrl()) {
                $redirectUrl = $this->_getRefererUrl();
            }
            Mage::helper('favourites')->calculate();
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() == Submarine_Favourites_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
                $session->addError(Mage::helper('favourites')->__('This product(s) is currently out of stock'));
            } else if ($e->getCode() == Submarine_Favourites_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
                Mage::getSingleton('catalog/session')->addNotice($e->getMessage());
                $redirectUrl = Mage::getUrl('*/*/configure/', array('id' => $item->getId()));
            } else {
                Mage::getSingleton('catalog/session')->addNotice($e->getMessage());
                $redirectUrl = Mage::getUrl('*/*/configure/', array('id' => $item->getId()));
            }
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('favourites')->__('Cannot add item to shopping cart'));
        }

        Mage::helper('favourites')->calculate();

        return $this->_redirectUrl($redirectUrl);
    }

    public function shareAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('favourites/session');
        $this->renderLayout();
    }

    public function sendAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }

        $emails  = explode(',', $this->getRequest()->getPost('emails'));
        $message = nl2br(htmlspecialchars((string) $this->getRequest()->getPost('message')));
        $error   = false;
        if (empty($emails)) {
            $error = $this->__('Email address can\'t be empty.');
        }
        else {
            foreach ($emails as $index => $email) {
                $email = trim($email);
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    $error = $this->__('Please input a valid email address.');
                    break;
                }
                $emails[$index] = $email;
            }
        }
        if ($error) {
            Mage::getSingleton('favourites/session')->addError($error);
            Mage::getSingleton('favourites/session')->setSharingForm($this->getRequest()->getPost());
            $this->_redirect('*/*/share');
            return;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        try {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $favourites = $this->_getFavourites();

            /*if share rss added rss feed to email template*/
            if ($this->getRequest()->getParam('rss_url')) {
                $rss_url = $this->getLayout()->createBlock('favourites/share_email_rss')->toHtml();
                $message .=$rss_url;
            }
            $favouritesBlock = $this->getLayout()->createBlock('favourites/share_email_items')->toHtml();

            $emails = array_unique($emails);
            /* @var $emailModel Mage_Core_Model_Email_Template */
            $emailModel = Mage::getModel('core/email_template');

            $sharingCode = $favourites->getSharingCode();
            foreach($emails as $email) {
                $emailModel->sendTransactional(
                    Mage::getStoreConfig('favourites/email/email_template'),
                    Mage::getStoreConfig('favourites/email/email_identity'),
                    $email,
                    null,
                    array(
                        'customer'      => $customer,
                        'salable'       => $favourites->isSalable() ? 'yes' : '',
                        'items'         => $favouritesBlock,
                        'addAllLink'    => Mage::getUrl('*/shared/allcart', array('code' => $sharingCode)),
                        'viewOnSiteLink'=> Mage::getUrl('*/shared/index', array('code' => $sharingCode)),
                        'message'       => $message
                    )
                );
            }

            $favourites->setShared(1);
            $favourites->save();

            $translate->setTranslateInline(true);

            Mage::dispatchEvent('favourites_share', array('favourites'=>$favourites));
            Mage::getSingleton('customer/session')->addSuccess(
                $this->__('Your Favourites has been shared.')
            );
            $this->_redirect('*/*');
        }
        catch (Exception $e) {
            $translate->setTranslateInline(true);

            Mage::getSingleton('favourites/session')->addError($e->getMessage());
            Mage::getSingleton('favourites/session')->setSharingForm($this->getRequest()->getPost());
            $this->_redirect('*/*/share');
        }
    }

    /**
     * Custom options download action
     *
     * @return void
     */
    public function downloadCustomOptionAction()
    {
        try {
            $optionId = $this->getRequest()->getParam('id');
            $option   = Mage::getModel('favourites/item_option')->load($optionId);
            $hasError = false;

            if ($option->getId() && $option->getCode() !== 'info_buyRequest') {
                $info      = unserialize($option->getValue());
                $filePath  = Mage::getBaseDir() . $info['quote_path'];
                $secretKey = $this->getRequest()->getParam('key');

                if ($secretKey == $info['secret_key']) {
                    $this->_prepareDownloadResponse($info['title'], array(
                        'value' => $filePath,
                        'type'  => 'filename'
                    ));
                }
            }
        } catch(Exception $e) {
            $this->_forward('noRoute');
        }
        exit(0);
    }
    
    public function getFavoriteFillingAction() {
        return $this->getResponse()->setBody(Mage::helper('favourites')->getFavouritesJson());
    }

	/**
	 * remove cache - private page category with form composition
	 *
	 * @return
	 */
	public function cleanCachePageCategory(){
		$session = Mage::getSingleton('customer/session');
		if ($session->isLoggedIn()) {
			Mage::app()->cleanCache('store_'.Mage::app()->getStore()->getId().'_category_user_'.$session->getCustomer()->getId());
			Mage::app()->removeCache('store_'.Mage::app()->getStore()->getId().'_category_user_'.$session->getCustomer()->getId());
		}
	}
    
}
