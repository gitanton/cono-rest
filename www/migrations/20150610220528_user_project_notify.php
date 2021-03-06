<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UserProjectNotify extends AbstractMigration
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
        $table = $this->table('project_user');
        $table->addColumn('notify', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'default' => 1))
            ->save();
    
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('project_user');
        $table->removeColumn('notify')
            ->save();
    }
}