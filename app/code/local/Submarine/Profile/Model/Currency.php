<?php
//require_once 'app/code/core/Mage/Directory/Model/Currency.php';
class Submarine_Profile_Model_Currency extends Mage_Directory_Model_Currency
{
    public function format($price, $options=array(), $includeContainer = true, $addBrackets = false)
    {
		$options['symbol'] = ",-";
		$options['format'] = "#,##0.00¤";
		$options['precision'] = 0;
		//$price = "12.34";
		if ($price - intval($price)) {
			$options['symbol'] = "";
			$options['precision'] = 2;
		}
        return $this->formatPrecision($price, 2, $options, $includeContainer, $addBrackets);
    }
}