<?php
// change field "Last name" in customer in not require in register form

$installer = $this;
$installer->startSetup();
$tableEavAttribute = $this->getTable('eav/attribute');
$tableEavEntityType = $this->getTable('eav/entity_type');
$conn = $installer->getConnection();
$select = $conn->select()
    ->from(array('ea'=>$tableEavAttribute), 'attribute_id')
	->joinLeft(array('eet'=>$tableEavEntityType), new Zend_Db_Expr("ea.entity_type_id = eet.entity_type_id"), '')
    ->where(new Zend_Db_Expr(" eet.entity_type_code = 'customer' "))
    ->where(new Zend_Db_Expr(" ea.attribute_code = 'lastname' "))
	->limit(1);
$fieldId_lastname = $conn->fetchOne($select);
$installer->run("UPDATE `{$tableEavAttribute}` SET `is_required` = '0' WHERE `{$tableEavAttribute}`.`attribute_id` = '{$fieldId_lastname}' ");
$installer->endSetup();