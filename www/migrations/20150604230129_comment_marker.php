<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CommentMarker extends AbstractMigration
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
        $table->addColumn('marker', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'after' => 'is_task', 'default' => 0))
            ->addIndex(array('marker'))
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

        $table = $this->table('comment');
        $table->removeColumn('marker')
            ->save();
    }
}