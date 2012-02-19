<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Add yubikey field to table 'admin/user'
 */
$installer->getConnection()->addColumn(
    $installer->getTable('admin/user'),
    'yubikey',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 12,
        'default'   => null,
        'nullable'  => true,
        'comment'   => 'Yubikey (16 chars)'
    )
);

$installer->endSetup();
