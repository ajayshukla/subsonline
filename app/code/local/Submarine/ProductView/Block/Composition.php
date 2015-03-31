<?php


class Submarine_ProductView_Block_Composition extends Mage_Core_Block_Template {

    protected function _prepareLayout()
    {
        // Set custom submit url route for form - to submit updated options to cart
        $block = $this->getLayout()->getBlock('product.info');
        if ($block) {
             $block->setSubmitRouteData(array(
                'route' => 'ajax/checkout_cart/add'
             ));
        }
        // Set custom template with 'Update Cart' button
        $block = $this->getLayout()->getBlock('product.info.addtocart');
        if ($block) {
            $block->setTemplate('productview/addtocart.phtml');
        }
        return parent::_prepareLayout();
    }
}

?>
