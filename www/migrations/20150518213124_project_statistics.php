<?php

use Phinx\Migration\AbstractMigration;

class ProjectStatistics extends AbstractMigration
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
        $project_statistics->addColumn('project_id', 'integer')
            ->addColumn('views', 'integer')
            ->addForeignKey('project_id', 'project', 'id', array('delete' => 'CASCADE', 'update' => 'CASCADE'))
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('project_statistics');
    }
}