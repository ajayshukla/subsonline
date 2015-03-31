<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper for fetching properties by product configurational item
 *
 * @category   Mage
 * @package    Mage_Bundle
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Submarine_Favproducts_Helper_Configuration extends Mage_Bundle_Helper_Catalog_Product_Configuration
{
   
    /**
     * Get bundled selections (slections-products collection)
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @return array
     */
    public function getBundleOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        $options = array();
        $product = $item->getProduct();

        /**
         * @var Mage_Bundle_Model_Product_Type
         */
        $typeInstance = $product->getTypeInstance(true);

        // get bundle options
        $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = unserialize($optionsQuoteItemOption->getValue());
        if ($bundleOptionsIds) {
            /**
            * @var Mage_Bundle_Model_Mysql4_Option_Collection
            */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');

            $selectionsCollection = $typeInstance->getSelectionsByIds(
                unserialize($selectionsQuoteItemOption->getValue()),
                $product
            );
			
			$showSubSize = false;
            $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            foreach ($bundleOptions as $bundleOption) {
                if ($bundleOption->getSelections()) {
                    $option = array(
                        'label' => $bundleOption->getTitle(),
                        'value' => array()
					);
					$product_sku = explode("-", $product->getSku());
                    $bundleSelections = $bundleOption->getSelections();
                    foreach ($bundleSelections as $bundleSelection) {
						if ($product_sku[0] == 'subs') {
							$bundleSelectionSku = explode("-", $bundleSelection->getSku());
							if ($bundleSelectionSku[0] == 'subs') {
								$categoryIds = $bundleSelection->getCategoryIds();
								if (!is_array($categoryIds)) $categoryIds = array();
								$showSubSize = true;
								$subOption['label'] = $this->__("Size");
								$subOption['value'] = Mage::getModel("catalog/category")->load(end($categoryIds))->getName();
							}
						} elseif (in_array($product_sku[0], array('menu', 'supermenu', 'saladmenu', 'valgfrisupermenu'))) {
							$menuOption = array();
							$bundleSelectionSku = explode("-", $bundleSelection->getSku());
							if (in_array($bundleSelectionSku[0], array('subs', 'salad'))) {
								$menuOption['label'] = $this->__("Type of Menus");
								$menuOption['value'] = $product->getName();
								$options[] = $menuOption;
							}
						}
						$option['value'][] = $this->escapeHtml($bundleSelection->getName());
                    }

                    if ($option['value']) {
                        $options[] = $option;
                    }
					if ($showSubSize) {
						$options[] = $subOption;
						$showSubSize = false;
					}
                }
            }
        }

        return $options;
    }

}
