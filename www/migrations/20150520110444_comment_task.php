<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CommentTask extends AbstractMigration
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
        $table = $this->table('comment');
        $table->addColumn('is_task', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'after' => 'time', 'default' => 0))
            ->addIndex(array('is_task'))
            ->addColumn('assignee_id', 'integer', array('limit' => MysqlAdapter::INT_REGULAR, 'after' => 'is_task', 'null' => true))
            ->addForeignKey('assignee_id', 'user', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('comment');
        $table->removeColumn('is_task')
            ->removeColumn('assignee_id')
            ->save();

    }
}