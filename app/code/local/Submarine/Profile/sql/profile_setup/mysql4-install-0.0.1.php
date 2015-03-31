<?php
// company, cvr, address, zip, city, phone, mphone
$installer = $this;
$installer->startSetup();
$setup = Mage::getModel('customer/entity_setup', 'core_setup');
$setup->addAttribute('customer', 'company', array(
	'type' => 'varchar',
	'input' => 'text',
	'label' => 'Company name',
	'global' => 1,
	'visible' => 1,
	'required' => 0,
	'user_defined' => 1,
	'default' => '',
	'visible_on_front' => 1,
    'source' =>	 '',
));
$setup->addAttribute('customer', 'cvr', array(
	'type' => 'varchar',
	'input' => 'text',
	'label' => 'CVR',
	'global' => 1,
	'visible' => 1,
	'required' => 0,
	'user_defined' => 1,
	'default' => '',
	'visible_on_front' => 1,
    'source' =>	 '',
));
$setup->addAttribute('customer', 'address', array(
	'type' => 'varchar',
	'input' => 'text',
	'label' => 'Address',
	'global' => 1,
	'visible' => 1,
	'required' => 1,
	'user_defined' => 1,
	'default' => '',
	'visible_on_front' => 1,
    'source' =>	 '',
));
$setup->addAttribute('customer', 'zip', array(
	'type' => 'varchar',
	'input' => 'text',
	'label' => 'Zip.nr.',
	'global' => 1,
	'visible' => 1,
	'required' => 1,
	'user_defined' => 1,
	'default' => '',
	'visible_on_front' => 1,
    'source' =>	 '',
));
$setup->addAttribute('customer', 'city', array(
	'type' => 'varchar',
	'input' => 'text',
	'label' => 'City',
	'global' => 1,
	'visible' => 1,
	'required' => 1,
	'user_defined' => 1,
	'default' => '',
	'visible_on_front' => 1,
    'source' =>	 '',
));
$setup->addAttribute('customer', 'phone', array(
	'type' => 'varchar',
	'input' => 'text',
	'label' => 'Phone',
	'global' => 1,
	'visible' => 1,
	'required' => 0,
	'user_defined' => 1,
	'default' => '',
	'visible_on_front' => 1,
    'source' =>	 '',
));
$setup->addAttribute('customer', 'mphone', array(
	'type' => 'varchar',
	'input' => 'text',
	'label' => 'Mobile phone',
	'global' => 1,
	'visible' => 1,
	'required' => 1,
	'user_defined' => 1,
	'default' => '',
	'visible_on_front' => 1,
    'source' =>	 '',
));

if (version_compare(Mage::getVersion(), '1.4.2', '>='))
{
	Mage::getSingleton('eav/config')
	->getAttribute('customer', 'company')
	->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','checkout_register'))
	->save();
	Mage::getSingleton('eav/config')
	->getAttribute('customer', 'cvr')
	->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','checkout_register'))
	->save();
	Mage::getSingleton('eav/config')
	->getAttribute('customer', 'address')
	->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','checkout_register'))
	->save();
	Mage::getSingleton('eav/config')
	->getAttribute('customer', 'zip')
	->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','checkout_register'))
	->save();
	Mage::getSingleton('eav/config')
	->getAttribute('customer', 'city')
	->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','checkout_register'))
	->save();
	Mage::getSingleton('eav/config')
	->getAttribute('customer', 'phone')
	->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','checkout_register'))
	->save();
	Mage::getSingleton('eav/config')
	->getAttribute('customer', 'mphone')
	->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','checkout_register'))
	->save();
	
}

$tablequote = $this->getTable('sales/quote');
$installer->run("ALTER TABLE $tablequote ADD `customer_company` INT NOT NULL");
$installer->run("ALTER TABLE $tablequote ADD `customer_cvr` INT NOT NULL");
$installer->run("ALTER TABLE $tablequote ADD `customer_address` INT NOT NULL");
$installer->run("ALTER TABLE $tablequote ADD `customer_zip` INT NOT NULL");
$installer->run("ALTER TABLE $tablequote ADD `customer_city` INT NOT NULL");
$installer->run("ALTER TABLE $tablequote ADD `customer_phone` INT NOT NULL");
$installer->run("ALTER TABLE $tablequote ADD `customer_mphone` INT NOT NULL");

$installer->endSetup();