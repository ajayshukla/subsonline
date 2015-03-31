<?php
/**
 * Copyright ePay | Dit Online Betalingssystem, (c) 2010.
 * 
 * This program is free software. You are allowed to use the software but NOT allowed to modify the software. 
 * It is also not legal to do any changes to the software and distribute it in your own name / brand. 
 */
class Mage_Epay_Model_System_Config_Source_Epaylogos
{

    public function toOptionArray()
    {
        return array(
        		array('value'=>1000, 'label'=>Mage::helper('adminhtml')->__('ePay trusted')),
        		array('value'=>1001, 'label'=>Mage::helper('adminhtml')->__('Verified by VISA')),
        		array('value'=>1002, 'label'=>Mage::helper('adminhtml')->__('MasterCard Secure Code')),
        		array('value'=>1003, 'label'=>Mage::helper('adminhtml')->__('PCI')),
        		array('value'=>1004, 'label'=>Mage::helper('adminhtml')->__('PBS')),
        		array('value'=>1005, 'label'=>Mage::helper('adminhtml')->__('SEB')),
        		array('value'=>1006, 'label'=>Mage::helper('adminhtml')->__('EUROLINE')),
            array('value'=>1, 'label'=>Mage::helper('adminhtml')->__('Dankort')),
            array('value'=>2, 'label'=>Mage::helper('adminhtml')->__('eDankort')),
            array('value'=>3, 'label'=>Mage::helper('adminhtml')->__('Danske Netbetaling')),
            array('value'=>4, 'label'=>Mage::helper('adminhtml')->__('Nordea e-betaling')),
            array('value'=>5, 'label'=>Mage::helper('adminhtml')->__('EWIRE')),
            array('value'=>6, 'label'=>Mage::helper('adminhtml')->__('Forbrugsforeningen')),
            array('value'=>7, 'label'=>Mage::helper('adminhtml')->__('VISA')),
            array('value'=>8, 'label'=>Mage::helper('adminhtml')->__('VISA Electron')),
            array('value'=>9, 'label'=>Mage::helper('adminhtml')->__('MasterCard')),
            array('value'=>10, 'label'=>Mage::helper('adminhtml')->__('Maestro')),
            array('value'=>11, 'label'=>Mage::helper('adminhtml')->__('JCB')),
            array('value'=>12, 'label'=>Mage::helper('adminhtml')->__('Diners Club')),
            array('value'=>13, 'label'=>Mage::helper('adminhtml')->__('AMEX')),
        );
    }

}
