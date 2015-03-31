<?php
class Excellence_Custom_Model_Sales_Order extends Mage_Sales_Model_Order{
    public function hasCustomFields(){
        $var = $this->getDeliverydate();
        if($var && !empty($var)){
            return true;
        }else{
            return false;
        }
    }

    public function getFieldHtml(){
        //$dateFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        //return Mage::app()->getLocale()->date($this->getDeliverydate())->toString($dateFormat);
		setlocale(LC_ALL, 'dk_DK');
        $newdate = date("j.M Y H:i", $this->getDeliverydate());
        return $newdate;
    }

    public function getAddressHtml(){

        $address = parent::getBillingAddress();
        $addressArray = array(
            'name'          => $address->getName(),
            'street'        => $address->getStreetFull(),
//            'city'          => $address->getCity(),
//            'postcode'      => $address->getPostcode(),
            'postcode+city' => $address->getPostcode() . ' ' . $address->getCity(),
            'telephone'     => $address->getTelephone(),
        );

        return implode('<br />', $addressArray);
    }
}
