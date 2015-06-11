<?php

use Phinx\Migration\AbstractMigration;

class CommentRemoveData extends AbstractMigration
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
        $table->removeColumn('data')
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

        $table = $this->table('comment');
        $table->addColumn('data', 'binary')
            ->update();
    }
}