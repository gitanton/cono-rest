<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ProjectStatisticsUserType extends AbstractMigration
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

        $project_statistics = $this->table('project_statistics');
        $project_statistics->removeColumn('views')
            ->addColumn('user_id', 'integer')
            ->addColumn('project_statistics_type_id', 'integer', array('limit' => MysqlAdapter::INT_SMALL))
            ->addIndex(array('project_statistics_type_id'))
            ->addForeignKey('user_id', 'user', 'id', array('delete' => 'CASCADE', 'update' => 'CASCADE'))
            ->addColumn('created', 'datetime')
            ->addIndex(array('created'))
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('project_statistics');
        $table->removeIndex(array('created'))
            ->removeColumn('created')
            ->dropForeignKey('user_id')
            ->removeIndex(array('project_statistics_type_id'))
            ->removeColumn('project_statistics_type_id')
            ->removeColumn('user_id')
            ->addColumn('views', 'integer')
            ->save();

    }
}