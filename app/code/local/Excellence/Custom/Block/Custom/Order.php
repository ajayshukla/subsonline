<?php
class Excellence_Custom_Block_Custom_Order extends Mage_Core_Block_Template{
    public function getCustomVars(){
        $model = Mage::getModel('custom/custom_order');
        $vars = $model->getByOrder($this->getOrder()->getId());
        $dateFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        foreach ($vars as $key => &$val) {
            if ($key == 'deliverydate') {
                //$val = Mage::app()->getLocale()->date($val)->toString($dateFormat);
				setlocale(LC_ALL, 'dk_DK');
				$val = date("j.M Y H:i", $val);
            }
        }
        return $vars;
    }
    public function getOrder(){
        return Mage::registry('current_order');
    }
}