<?php
class Excellence_Custom_Model_Observer{
    public function saveQuoteBefore($evt){
        $quote = $evt->getQuote();
        $post = Mage::app()->getFrontController()->getRequest()->getPost();

        if(isset($post['billing']['caldate'])){
            $quote->setDeliverydate(strtotime($post['billing']['caldate']));
        }
    }
    public function saveQuoteAfter($evt){
        $quote = $evt->getQuote();

        if($quote->getDeliverydate()){
            $model = Mage::getModel('custom/custom_quote');
            $model->deteleByQuote($quote->getId(),'deliverydate');
            $model->setQuoteId($quote->getId());
            $model->setKey('deliverydate');
            $model->setValue($quote->getDeliverydate());
            $model->save();
        }
    }
    public function loadQuoteAfter($evt){
        $quote = $evt->getQuote();
        $model = Mage::getModel('custom/custom_quote');
        $data = $model->getByQuote($quote->getId());
        foreach($data as $key => $value){
            $quote->setData($key,$value);
        }
    }
    public function saveOrderAfter($evt){
        $order = $evt->getOrder();
        $quote = $evt->getQuote();

        if($quote->getDeliverydate()){
            $model = Mage::getModel('custom/custom_order');
            $model->deleteByOrder($order->getId(),'deliverydate');
            $model->setOrderId($order->getId());
            $model->setKey('deliverydate');
            $model->setValue($quote->getDeliverydate());
            $order->setDeliverydate($quote->getDeliverydate());
            $model->save();
        }
    }
    public function loadOrderAfter($evt){
        $order = $evt->getOrder();
        $model = Mage::getModel('custom/custom_order');
        $data = $model->getByOrder($order->getId());
        foreach($data as $key => $value){
            $order->setData($key,$value);
        }
    }

}