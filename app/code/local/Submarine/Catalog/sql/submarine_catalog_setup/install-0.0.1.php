<?php
/**
 * Install script for Submarine_Catalog module
 *
 * @category    Submarine
 * @package     Submarine_Catalog
 */
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer = $this;
$installer->startSetup();

/**
 * Update attributes frontend class
 */
$attributes = array(
    'nutritionitem_carbohydrates',
    'nutritionrda_calories',
    'nutritionrda_carbohydrates',
    'nutritionrda_dietary_fibre',
    'nutritionrda_fat',
    'nutritionrda_protein',
    'nutritionrda_weight'
);

foreach ($attributes as $attribute) {
    $installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute, 'frontend_class', new Zend_Db_Expr("NULL"));
}


$installer->endSetup();