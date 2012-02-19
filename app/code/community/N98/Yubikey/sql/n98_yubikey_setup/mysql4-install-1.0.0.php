<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Add yubikey field to table 'admin/user'
 */
$installer->getConnection()->addColumn($this->getTable('admin/user'), 'yubikey', 'varchar(12) null');

$installer->endSetup();