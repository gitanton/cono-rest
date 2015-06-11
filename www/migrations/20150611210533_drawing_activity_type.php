<?php

use Phinx\Migration\AbstractMigration;

class DrawingActivityType extends AbstractMigration
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
        $this->execute("INSERT INTO `activity_type` (`name`) VALUES ('Drawing Video Add')");
        $this->execute("INSERT INTO `activity_type` (`name`) VALUES ('Drawing Screen Add')");
        $this->execute("INSERT INTO `activity_type` (`name`) VALUES ('Hotspot Screen Delete')");
        $this->execute("INSERT INTO `activity_type` (`name`) VALUES ('Drawing Screen Delete')");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

        $this->execute("DELETE FROM `activity_type` WHERE name = 'Drawing Video Add'");
        $this->execute("DELETE FROM `activity_type` WHERE name = 'Drawing Screen Add'");
        $this->execute("DELETE FROM `activity_type` WHERE name = 'Hotspot Screen Delete'");
        $this->execute("DELETE FROM `activity_type` WHERE name = 'Drawing Screen Delete'");
    }
}