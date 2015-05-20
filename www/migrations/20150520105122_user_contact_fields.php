<?php

use Phinx\Migration\AbstractMigration;

class UserContactFields extends AbstractMigration
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
        $table->addColumn('phone', 'string', array('limit' => 15, 'after' => 'timezone', 'null' => true))
            ->addColumn('skype', 'string', array('limit' => 50, 'after' => 'phone', 'null' => true))
            ->addColumn('website', 'string', array('limit' => 100, 'after' => 'skype', 'null' => true))
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('user');
        $table->removeColumn('phone')
            ->removeColumn('skype')
            ->removeColumn('website')
            ->save();

    }
}