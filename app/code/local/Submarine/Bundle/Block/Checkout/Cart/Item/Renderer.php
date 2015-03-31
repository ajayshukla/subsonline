<?php
/**
 * Shopping cart item render block
 */
class Submarine_Bundle_Block_Checkout_Cart_Item_Renderer extends Mage_Bundle_Block_Checkout_Cart_Item_Renderer
{
    public function getOptionList()
    {
        return Mage::helper('favourites/configuration')->getOptions($this->getItem());
    }
}
