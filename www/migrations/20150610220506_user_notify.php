<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UserNotify extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('user');
        $table->addColumn('notify_general', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'after' => 'inactive', 'default' => 1))
            ->addIndex(array('notify_general'))
            ->addColumn('notify_promotions', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'after' => 'notify_general', 'default' => 1))
            ->addIndex(array('notify_promotions'))
            ->save();
    
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

        $table = $this->table('user');
        $table->removeIndex('notify_general')
            ->removeColumn('notify_general')
            ->removeIndex('notify_promotions')
            ->removeColumn('notify_promotions')
            ->save();

    }
}