<?php
$installer = $this;
$installer->startSetup();


// change field "Last name" and "country_id" in customer_address in not require in register form
$tableCustomerFormAttribute = $this->getTable('customer/form_attribute');

$tableEavAttribute = $this->getTable('eav/attribute');
$tableEavEntityType = $this->getTable('eav/entity_type');

$conn = $installer->getConnection();
$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer_address' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'lastname' "))
	->limit(1);
$fieldId_lastname = $conn->fetchOne($select);
if ($fieldId_lastname) {
	$sql = "UPDATE `{$tableEavAttribute}` SET `is_required` = '0' WHERE `{$tableEavAttribute}`.`attribute_id` = '{$fieldId_lastname}' ";
	$installer->run($sql);
}

$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer_address' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'country_id' "))
	->limit(1);
$fieldId_country_id = $conn->fetchOne($select);
if ($fieldId_country_id) {
	$sql = "UPDATE `{$tableEavAttribute}` SET `is_required` = '0' WHERE `{$tableEavAttribute}`.`attribute_id` = '{$fieldId_country_id}' ";
	$installer->run($sql);
}


// change cvr in eav_attribute (maybe id 130) from "1" (customer) to "2" (customer_address)
$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'cvr' "))
	->limit(1);
$fieldId_cvr = $conn->fetchOne($select);
$select = $conn->select()
    ->from(array('eet'=>$tableEavEntityType), 'entity_type_id')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer_address' "))
	->limit(1);
$cvr_entity_type_id = $conn->fetchOne($select);
if ($fieldId_cvr && $cvr_entity_type_id) {
	$sql = "UPDATE `{$tableEavAttribute}` SET `entity_type_id` = '{$cvr_entity_type_id}' WHERE `{$tableEavAttribute}`.`attribute_id` = '{$fieldId_cvr}' ";
	$installer->run($sql);
}
// customer_form_attribute cvr - поменять adminhtml_customer на adminhtml_customer_address и customer_register_address, customer_address_edit
if ($fieldId_cvr) {
	$sql = "UPDATE `{$tableCustomerFormAttribute}` SET `form_code` = 'adminhtml_customer_address' WHERE `form_code` = 'adminhtml_customer' AND `{$tableCustomerFormAttribute}`.`attribute_id` = '{$fieldId_cvr}' ";
	$installer->run($sql);
	$sql = "UPDATE `{$tableCustomerFormAttribute}` SET `form_code` = 'customer_register_address' WHERE `form_code` = 'customer_account_create' AND `{$tableCustomerFormAttribute}`.`attribute_id` = '{$fieldId_cvr}' ";
	$installer->run($sql);
	$sql = "UPDATE `{$tableCustomerFormAttribute}` SET `form_code` = 'customer_address_edit' WHERE `form_code` = 'customer_account_edit' AND `{$tableCustomerFormAttribute}`.`attribute_id` = '{$fieldId_cvr}' ";
	$installer->run($sql);
}


// delete fields "company", "address", "zip", "city", "phone", "mphone"
$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'company' "))
	->limit(1);
$fieldId_company = $conn->fetchOne($select);

$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'address' "))
	->limit(1);
$fieldId_address = $conn->fetchOne($select);

$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'zip' "))
	->limit(1);
$fieldId_zip = $conn->fetchOne($select);

$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'city' "))
	->limit(1);
$fieldId_city = $conn->fetchOne($select);

$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'phone' "))
	->limit(1);
$fieldId_phone = $conn->fetchOne($select);

$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'mphone' "))
	->limit(1);
$fieldId_mphone = $conn->fetchOne($select);
if ($fieldId_company && $fieldId_address 
		&& $fieldId_zip 
		&& $fieldId_city 
		&& $fieldId_phone 
		&& $fieldId_mphone) {
	$sql = "DELETE FROM `{$tableEavAttribute}` WHERE `attribute_id` IN ({$fieldId_company}, {$fieldId_address}, {$fieldId_zip}, {$fieldId_city}, {$fieldId_phone}, {$fieldId_mphone}) ";
	$installer->run($sql);
	$sql = "DELETE FROM `{$tableCustomerFormAttribute}` WHERE `attribute_id` IN ({$fieldId_company}, {$fieldId_address}, {$fieldId_zip}, {$fieldId_city}, {$fieldId_phone}, {$fieldId_mphone}) ";
	$installer->run($sql);
}


// change front_label
$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer_address' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'street' "))
	->limit(1);
$fieldId_street = $conn->fetchOne($select);
if ($fieldId_street) {
	$sql = "UPDATE `{$tableEavAttribute}` SET `frontend_label` = 'Address' WHERE `{$tableEavAttribute}`.`attribute_id` = '{$fieldId_street}' ";
	$installer->run($sql);
}

$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer_address' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'postcode' "))
	->limit(1);
$fieldId_postcode = $conn->fetchOne($select);
if ($fieldId_postcode) {
	$sql = "UPDATE `{$tableEavAttribute}` SET `frontend_label` = 'Zip.nr.' WHERE `{$tableEavAttribute}`.`attribute_id` = '{$fieldId_postcode}' ";
	$installer->run($sql);
}

$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer_address' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'fax' "))
	->limit(1);
$fieldId_fax = $conn->fetchOne($select);
if ($fieldId_fax) {
	$sql = "UPDATE `{$tableEavAttribute}` SET `frontend_label` = 'Phone' WHERE `{$tableEavAttribute}`.`attribute_id` = '{$fieldId_fax}' ";
	$installer->run($sql);
}

$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer_address' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'telephone' "))
	->limit(1);
$fieldId_telephone = $conn->fetchOne($select);
if ($fieldId_telephone) {
	$sql = "UPDATE `{$tableEavAttribute}` SET `frontend_label` = 'Mobile phone' WHERE `{$tableEavAttribute}`.`attribute_id` = '{$fieldId_telephone}' ";
	$installer->run($sql);
}



$installer->endSetup();