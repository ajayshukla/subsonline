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
class Submarine_Favourites_Helper_Configuration extends Mage_Bundle_Helper_Catalog_Product_Configuration
{
    /**
     * Get bundled selections (slections-products collection)
     *
     * @todo Select only filling options
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

            $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            foreach ($bundleOptions as $bundleOption) {
                if ($bundleOption->getSelections()) {
                    $option = array(
                        'label' => $bundleOption->getTitle(),
                        'value' => array()
                    );

                    $bundleSelections = $bundleOption->getSelections();

                    foreach ($bundleSelections as $bundleSelection) {
                        $subOption = array();
                        $qty = $this->getSelectionQty($product, $bundleSelection->getSelectionId()) * 1;
                        if ($qty) {
                            $subOption['qt'] = $qty;
                            $subOption['name'] = $this->escapeHtml($bundleSelection->getName());
                            $subOption['fpr'] = Mage::helper('core')->currency($this->getSelectionFinalPrice($item, $bundleSelection));
                            $subOption['sku'] = $bundleSelection->getSKU();
                            $subOption['category_ids'] = $bundleSelection->getCategoryIds();
                        }
                        $option['value'][] = $subOption;
                    }

                    if ($option['value']) {
                        $options[] = $option;
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Get bundled selections ids
     *
     * @return array
     */
    public function getBundleOptionsIds(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        $product = $item->getProduct();

        /**
         * @var Mage_Bundle_Model_Product_Type
         */
        $typeInstance = $product->getTypeInstance(true);

        // get bundle options
        $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = unserialize($optionsQuoteItemOption->getValue());
        $fillings = array();
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

            $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            
            foreach ($bundleOptions as $bundleOption) {
                if ($bundleOption->getSelections() && $bundleOption->getType() == 'filling') {
                    
                    $bundleSelections = $bundleOption->getSelections();
                    foreach ($bundleSelections as $bundleSelection) {
                        $option = array();
                        $option['id'] =  $bundleSelection->getSelectionId();
                        $option['productid'] =  $bundleSelection->getProductId();
                        $option['name'] =  $bundleSelection->getName();
						
						$attributeSetModel = Mage::getModel("eav/entity_attribute_set");
						$attributeSetModel->load($bundleSelection->getAttributeSetId());
						$option['attribute_set'] = $attributeSetModel->getAttributeSetName();
                        $fillings[] = $option;
                    }
                    return $fillings;
                }
            }
        }
    }
    
    
    public function getBundleOptionsForWishList(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
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

            $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            foreach ($bundleOptions as $bundleOption) {
                if ($bundleOption->getSelections()) {
                    $option = array(
                        'bundle_id' => $bundleOption->getId(),
                        'label' => $bundleOption->getTitle(),
						'type' => $bundleOption->getType(),
                        'value' => array()
                    );

                    $bundleSelections = $bundleOption->getSelections();

                    foreach ($bundleSelections as $bundleSelection) {
                        if ($bundleOption->getSelections() && ($bundleOption->getType() == 'radio' || $bundleOption->getType() == 'checkbox' || $bundleOption->getType() == 'filling')) {
                            $subOption = array();
                            $subOption['id'] =  $bundleSelection->getSelectionId();
                            $subOption['productid'] =  $bundleSelection->getProductId();
                            $subOption['name'] =  $bundleSelection->getName();
                            $subOption['sku'] = $bundleSelection->getSKU();
                            $subOption['category_ids'] = $bundleSelection->getCategoryIds();
                            $option['value'][] = $subOption;
                        }
                    }
                    if ($option['value']) {
                        $options[] = $option;
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Retrieves product options list
     *
     * @param Mage_Catalog_Model_Product_Configuration_Item_Interface $item
     * @return array
     */
    public function getOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        return $this->getBundleOptions($item);
    }
}
