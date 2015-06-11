<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class Drawing extends AbstractMigration
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
        $drawing = $this->table('drawing');
        $drawing->addColumn('uuid', 'string', array('limit' => 36))
            ->addColumn('screen_id', 'integer', array('null' => true))
            ->addColumn('video_id', 'integer', array('null' => true))
            ->addColumn('data', 'binary', array('null' => true))
            ->addColumn('ordering', 'integer')
            ->addColumn('creator_id', 'integer')
            ->addColumn('created', 'datetime')
            ->addColumn('deleted', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'default' => 0))
            ->addIndex(array('uuid'), array('unique' => true, 'name' => 'idx_drawing_uuid'))
            ->addIndex(array('deleted'), array('name' => 'idx_drawing_deleted'))
            ->addIndex(array('screen_id'), array('name' => 'idx_drawing_screen_id'))
            ->addIndex(array('video_id'), array('name' => 'idx_drawing_video_id'))
            ->addForeignKey('creator_id', 'user', 'id', array('delete' => 'CASCADE', 'update' => 'CASCADE'))
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('drawing');
    }
}