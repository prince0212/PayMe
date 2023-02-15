<?php

namespace Deloitte\PayMe\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if(version_compare($context->getVersion(), '1.2.0', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable( 'deloitte_payme_history' ),
                'status_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'comment' => 'Status Code',
                    'after' => 'status'
                ]
            );
        }
        $installer->endSetup();
    }
}