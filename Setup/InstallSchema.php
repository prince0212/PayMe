<?php

namespace Deloitte\PayMe\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('deloitte_payme_history');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            // Create tutorial_simplenews table
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity id'
                )
                ->addColumn(
                    'quote_id',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Quote Id'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Order Id'
                )
                ->addColumn(
                    'transaction_id',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Transaction Id'
                )
                ->addColumn(
                    'transactions',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Transactions Value'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Status'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Updated At'
                );
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}