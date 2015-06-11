<?php

use Phinx\Migration\AbstractMigration;

class AddDeletedIndex extends AbstractMigration
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
        $this->table('activity')->addIndex('deleted', array('name' => 'idx_activity_deleted'))->save();
        $this->table('comment')->addIndex('deleted', array('name' => 'idx_comment_deleted'))->save();
        $this->table('message')->addIndex('deleted', array('name' => 'idx_message_deleted'))->save();
        $this->table('screen')->addIndex('deleted', array('name' => 'idx_screen_deleted'))->save();
        $this->table('template')->addIndex('deleted', array('name' => 'idx_template_deleted'))->save();
        $this->table('video')->addIndex('deleted', array('name' => 'idx_video_deleted'))->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('activity')->removeIndex(array('deleted'));
        $this->table('comment')->removeIndex(array('deleted'));
        $this->table('message')->removeIndex(array('deleted'));
        $this->table('screen')->removeIndex(array('deleted'));
        $this->table('template')->removeIndex(array('deleted'));
        $this->table('video')->removeIndex(array('deleted'));
    }
}