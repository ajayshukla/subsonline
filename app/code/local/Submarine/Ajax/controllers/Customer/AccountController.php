<?php
require 'app/code/core/Mage/Customer/controllers/AccountController.php';
class Submarine_Ajax_Customer_AccountController extends Mage_Customer_AccountController
{
    protected $_messages;

    /**
     * Store first level html tag name for messages html output
     *
     * @var string
     */
    protected $_messagesFirstLevelTagName = 'ul';

    /**
     * Store second level html tag name for messages html output
     *
     * @var string
     */
    protected $_messagesSecondLevelTagName = 'li';

    /**
     * Store content wrapper html tag name for messages html output
     *
     * @var string
     */
    protected $_messagesContentWrapperTagName = 'span';

    /**
     * Flag which require message text escape
     *
     * @var bool
     */
    protected $_escapeMessageFlag = false;

	protected $_honeyPotFields = array('site');

    protected function _isHoneyPotFilled()
    {
        $result  = false;
        $request = $this->getRequest();
        foreach ($this->_honeyPotFields as $field) {
            $value = $request->getPost($field);
            if (trim($value)) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    public function loginCustomerAction()
    {
        $response = $this->getResponse();
        $request  = $this->getRequest();
        $result   = new Varien_Object();

        if ($this->_getSession()->isLoggedIn() || !$request->isPost()) {
            $result->redirect = Mage::getUrl('*/*/');
            return $response->setBody($result->toJSON());
        }

        $login = $request->getPost('login');
        if (empty($login['username']) || empty($login['password'])) {
            $result->error = $this->__('Login and password are required.');
            return $response->setBody($result->toJSON());
        }

        $session = $this->_getSession();
        try {
            $session->login($login['username'], $login['password']);
            if ($session->getCustomer()->getIsJustConfirmed()) {
                $this->_welcomeCustomer($session->getCustomer(), true);
            }
			$this->cleanUserSidebarCart();

			if ($session->getBeforeFavouritesRequest()) {
				$requestParams = $session->getBeforeFavouritesRequest();
				$buyRequest = new Varien_Object($requestParams);

				$favourites = Mage::getModel('favourites/favourites')->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
				$productId = (int) $requestParams['product'];
				$product = Mage::getModel('catalog/product')->load($productId);
				
				$result = $favourites->addNewItem($product, $buyRequest);
				$itemDescription = $requestParams['description'];
				$itemId = $result->getId();
				$item = Mage::getModel('favourites/item');
				$item->load($itemId);
				if ($item) {
					$item->setDescription($itemDescription)
						 ->save();
				}
				$session->setBeforeWishlistRequest($requestParams);
				$favourites->save();
			}
			if ($session->getBeforeWishlistRequest()) {
				$requestParams = $session->getBeforeWishlistRequest();
				$buyRequest = new Varien_Object($requestParams);

				if (!$session->getBeforeFavouritesRequest()) {
					$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
					$productId = (int) $requestParams['product'];
					$product = Mage::getModel('catalog/product')->load($productId);

					$result = $wishlist->addNewItem($product, $buyRequest);
					$itemDescription = $requestParams['description'];

					$itemId = $result->getId();
					$item = Mage::getModel('wishlist/item');
					$item->load($itemId);
						if ($item) {
							$item->setDescription($itemDescription)
								 ->save();
						}
					$wishlist->save();
					$session->setBeforeWishlistRequest($item);
				} 
			}

			if (isset($product)) {
				if ($session->isLoggedIn()) {
					$store = 'store_'.Mage::app()->getStore()->getId();
					$category_ids = $product->getCategoryIds();
					$cache_key = $store.'_category_'.current($category_ids);
					$cache_key .= '_user_'.$session->getCustomer()->getId();
					Mage::app()->removeCache($cache_key);
				}
			}

        } catch (Mage_Core_Exception $e) {
            switch ($e->getCode()) {
                case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                    $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                    $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                    break;
                case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                    $message = $e->getMessage();
                    break;
                default:
                    $message = $e->getMessage();
            }
            $result->error = $message;
            $session->setUsername($login['username']);
        } catch (Exception $e) {
        }

        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
            // Set default URL to redirect customer to
            $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
            // Redirect customer to the last page visited after logging in

            if ($session->isLoggedIn()) {
                if (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard')) {
                    $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        $referer = Mage::helper('core')->urlDecode($referer);
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                } else if ($session->getAfterAuthUrl()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }
            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
            }
        } else if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        } else {
            if (!$session->getAfterAuthUrl()) {
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
        }

        $result->redirect = $session->getBeforeAuthUrl(true);
        return $response->setBody($result->toJSON());
    }

    public function _createCustomerAction_()
    {
        $response = $this->getResponse();
        $request  = $this->getRequest();
        $result   = new Varien_Object();
        $session  = $this->_getSession();

        if ($session->isLoggedIn() || !$request->isPost()) {
            $result->redirect = Mage::getUrl('*/*/');
            return $response->setBody($result->toJSON());
        }

        if ($this->_isHoneyPotFilled()) {
            $result->error = $this->__('Invalid data');
            return $response->setBody($result->toJSON());
        }

        if (!$customer = Mage::registry('current_customer')) {
            $customer = Mage::getModel('customer/customer')->setId(null);
        }

        /* @var $customerForm Mage_Customer_Model_Form */
        $customerForm = Mage::getModel('customer/form');
        $customerForm->setFormCode('customer_account_create')
            ->setEntity($customer);

        $request->setPost('confirmation', $request->getPost('password'));
        $customerData = $customerForm->extractData($request);
        /**
         * Initialize customer group id
         */
        $customer->getGroupId();
        $errors = array();

        try {
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors === true) {
                $customerForm->compactData($customerData);
                $customer->setPassword($request->getPost('password'));
                $customer->setConfirmation($request->getPost('confirmation'));
                $customerErrors = $customer->validate();
                if (is_array($customerErrors)) {
                    $errors = array_merge($customerErrors, $errors);
                }
            } else {
                $errors = $customerErrors;
            }

            if (empty($errors)) {
                $customer->save();

                if ($customer->isConfirmationRequired()) {
                    $customer->sendNewAccountEmail('confirmation', $session->getBeforeAuthUrl());
                    $result->error    = $this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail()));
                    $result->redirect = Mage::getUrl('*/*/index', array('_secure'=>true));
                } else {
                    $session->setCustomerAsLoggedIn($customer);
                    $url = $this->_welcomeCustomer($customer);
                    $result->redirect = $url;
                }
            }
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $url = Mage::getUrl('customer/account/forgotpassword');
                $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                $session->setEscapeMessages(false);
            } else {
                $message = $e->getMessage();
            }
            $errors[] = $message;
        } catch (Exception $e) {
            $errors[] = $this->__('Cannot save the customer.');
        }

        if (!empty($errors)) {
            $result->error = $errors;
        }

        return $response->setBody($result->toJSON());
    }
	
	public function editPostAction()
    {
		$response = $this->getResponse();
		$params = $this->getRequest()->getParams();
		$errors = array();
		if(isset($params['isAjax']) && $params['isAjax'] == 1){
			if (!$this->_validateFormKey()) {
				//return $this->_redirect('*/*');
			}
			if ($this->getRequest()->isPost()) {
				/** @var $customer Mage_Customer_Model_Customer */
				$customer = $this->_getSession()->getCustomer();
				$result   = new Varien_Object();

				/** @var $customerForm Mage_Customer_Model_Form */
				$customerForm = Mage::getModel('customer/form');
				$customerForm->setFormCode('customer_account_edit')
					->setEntity($customer);

				$customerData = $customerForm->extractData($this->getRequest());

				$errors = array();
				$customerErrors = $customerForm->validateData($customerData);
				if ($customerErrors !== true) {
					$errors = array_merge($customerErrors, $errors);
				} else {
					$customerForm->compactData($customerData);
					$errors = array();
					
					
					// Save Default Billing Address data
					if (true) {
						$customer = $this->_getSession()->getCustomer();
						/* @var $address Mage_Customer_Model_Address */
						$address  = Mage::getModel('customer/address');
						$addressId = $customer->getDefaultBilling();
						if ($addressId) {
							$existsAddress = $customer->getAddressById($addressId);
							if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
								$address->setId($existsAddress->getId());
							}
						}

						/* @var $addressForm Mage_Customer_Model_Form */
						$addressForm = Mage::getModel('customer/form');
						$addressForm->setFormCode('customer_address_edit')
							->setEntity($address);
						$addressData    = $addressForm->extractData($this->getRequest());
						$addressErrors  = $addressForm->validateData($addressData);
						if ($addressErrors !== true) {
							$errors[] = $addressErrors;
						}

						try {
							$addressForm->compactData($addressData);
							$address->setCustomerId($customer->getId())
								->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
								->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));

							$addressErrors = $address->validate();
							if ($addressErrors !== true) {
								$errors[] = array_merge($errors, $addressErrors);
							}

							if (count($errors) === 0) {
								$address->save();
								//$this->_getSession()->addSuccess($this->__('The address has been saved.'));
							} else {
								$this->_getSession()->setAddressFormData($this->getRequest()->getPost());
								foreach ($errors as $errorMessage) {
									$this->_getSession()->addError($errorMessage);
								}
							}
						} catch (Mage_Core_Exception $e) {
							$this->_getSession()->setAddressFormData($this->getRequest()->getPost())
								->addException($e, $e->getMessage());
						} catch (Exception $e) {
							$this->_getSession()->setAddressFormData($this->getRequest()->getPost())
								->addException($e, $this->__('Cannot save address.'));
						}
					}

					// If password change was requested then add it to common validation scheme
					if ($this->getRequest()->getParam('current_password')
							|| $this->getRequest()->getPost('password')
							|| $this->getRequest()->getPost('confirmation')) {
						$currPass   = $this->getRequest()->getPost('current_password');
						$newPass    = $this->getRequest()->getPost('password');
						$confPass   = $this->getRequest()->getPost('confirmation');

						$oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
						if (Mage::helper('core/string')->strpos($oldPass, ':')) {
							list($_salt, $salt) = explode(':', $oldPass);
						} else {
							$salt = false;
						}

						if ($customer->hashPassword($currPass, $salt) == $oldPass) {
							if (strlen($newPass)) {
								/**
								* Set entered password and its confirmation - they
								* will be validated later to match each other and be of right length
								*/
								$customer->setPassword($newPass);
								$customer->setConfirmation($confPass);
							} else {
								$errors[] = $this->__('New password field cannot be empty.');
							}
						} else {
							$errors[] = $this->__('Invalid current password');
						}
					}

					// Validate account and compose list of errors if any
					$customerErrors = $customer->validate();
					if (is_array($customerErrors)) {
						$errors = array_merge($errors, $customerErrors);
					}
				}

				if (!empty($errors)) {
					$this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
					foreach ($errors as $message) {
						$this->_getSession()->addError($message);
					}
					//$this->_redirect('*/*/edit');
					//$this->_redirect('customer/account');
					
					$result->error = $this->getMessagesArray();
					return $response->setBody($result->toJSON());
				}

				try {
					$customer->setConfirmation(null);
					$customer->save();
					$this->_getSession()->setCustomer($customer)
						->addSuccess($this->__('The account information has been saved.'));
					$result->success = $this->getMessagesArray();
					return $response->setBody($result->toJSON());
				} catch (Mage_Core_Exception $e) {
					$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
						->addError($e->getMessage());
				} catch (Exception $e) {
					$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
						->addException($e, $this->__('Cannot save the customer.'));
				}
				$result->error = $this->getMessagesArray();
				return $response->setBody($result->toJSON());
			}

			//$this->_redirect('*/*/edit');
			//$this->_redirect('customer/account');
			
		}
		return $this->_redirect('*/*');
	}
	
    public function getMessagesArray($clear=true)
    {
        $html = array();
		$messages = $this->_getSession()->getMessages($clear);
		foreach($messages->getItems() as $message)
		{
			$html[] = $message->getText();
		}
        return $html;
    }
}
