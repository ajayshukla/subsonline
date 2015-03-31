<?php
require_once 'Mage/Checkout/controllers/OnepageController.php';
class Excellence_Remove_OnepageController extends Mage_Checkout_OnepageController
{
	public function saveBillingAction()
	{
		if ($this->_expireAjax()) {
			return;
		}
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost('billing', array());
			$customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

			if (isset($data['email'])) {
				$data['email'] = trim($data['email']);
			}

            $shippingData = $data;
            $shippingData['same_as_billing'] = 1;
            $shippingData['save_in_address_book'] = 1;

			$saveBillingResult = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($saveBillingResult['error'])) {
                $saveShippingResult = $this->getOnepage()->saveShipping($shippingData, $customerAddressId);
                $this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod('freeshipping_freeshipping');

                if(!isset($saveShippingResult['error'])){
                    $savePaymentResult = $this->getOnepage()->savePayment(array('method'=>'free'));

                    if(!isset($savePaymentResult['error'])){
                        $this->loadLayout('checkout_onepage_review');
                        $allResult['goto_section'] = 'review';
                        $allResult['update_section'] = array(
                            'name' => 'review',
                            'html' => $this->_getReviewHtml()
                        );
                    }else{
                        $allResult['error'] = $savePaymentResult['error'];
                    }
                }else{
                    $allResult['error'] = $saveShippingResult['error'];
                }
            }else{
                $allResult['error'] = $saveBillingResult['error'];
                $allResult['message'] = $this->__($saveBillingResult['message'][0]);//!!!!!!!!!!!!!!!!!!!!!!!
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($allResult));
		}
	}

	public function saveShippingAction()
	{
		if ($this->_expireAjax()) {
			return;
		}
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost('shipping', array());
			$customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
			$result = $this->getOnepage()->saveShipping($data, $customerAddressId);

			if (!isset($result['error'])) {
				$method = 'freeshipping_freeshipping';
				$result = $this->getOnepage()->saveShippingMethod($method);

				if (!isset($result['error'])) {

					try{
						$data = array('method'=>'free');
						$result = $this->getOnepage()->savePayment($data);
						$redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
						if (empty($result['error']) && !$redirectUrl) {
							$this->loadLayout('checkout_onepage_review');
							$result['goto_section'] = 'review';
							$result['update_section'] = array(
	                    'name' => 'review',
	                    'html' => $this->_getReviewHtml()
							);
						}
						if ($redirectUrl) {
							$result['redirect'] = $redirectUrl;
						}
					} catch (Mage_Payment_Exception $e) {
						if ($e->getFields()) {
							$result['fields'] = $e->getFields();
						}
						$result['error'] = $e->getMessage();
					} catch (Mage_Core_Exception $e) {
						$result['error'] = $e->getMessage();
					} catch (Exception $e) {
						Mage::logException($e);
						$result['error'] = $this->__('Unable to set Payment Method.');
					}
				}
			}
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
}