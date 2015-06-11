<?php

use Phinx\Migration\AbstractMigration;

class UserLocation extends AbstractMigration
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
        $table = $this->table('user')
            ->addColumn('city', 'string', array('limit' => 100, 'after' => 'website', 'null' => true))
            ->addColumn('state', 'string', array('limit' => 100, 'after' => 'city', 'null' => true))
            ->addColumn('country', 'string', array('limit' => 100, 'after' => 'state', 'null' => true))
            ->save();

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('user');
        $table->removeColumn('city')
            ->removeColumn('state')
            ->removeColumn('country')
            ->save();

    }
}