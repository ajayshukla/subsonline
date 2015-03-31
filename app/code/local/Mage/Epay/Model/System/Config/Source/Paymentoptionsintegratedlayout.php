<?php
/**
 * Copyright ePay | Dit Online Betalingssystem, (c) 2010.
 * 
 * This program is free software. You are allowed to use the software but NOT allowed to modify the software. 
 * It is also not legal to do any changes to the software and distribute it in your own name / brand. 
 */
class Mage_Epay_Model_System_Config_Source_Paymentoptionsintegratedlayout
{

    public function toOptionArray()
    {
        return array(
        		array('value'=>0, 'label'=>Mage::helper('adminhtml')->__('Credit card')),
        		array('value'=>17, 'label'=>Mage::helper('adminhtml')->__('EWIRE')),
        		array('value'=>20, 'label'=>Mage::helper('adminhtml')->__('eDankort')),
        		array('value'=>21, 'label'=>Mage::helper('adminhtml')->__('Nordea')),
        		array('value'=>22, 'label'=>Mage::helper('adminhtml')->__('Danske Bank'))
        );
    }

}
