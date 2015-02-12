<?php
class Subscription extends MY_Model
{

    protected static $fields = array(
        '' => 'string'
    );

    function get_scope()
    {
        return "subscription";
    }

    function column_map($col)
    {
        $column_map = array('id');
        return $column_map[intval($col)];
    }

    function load_by_user_id($user_id)
    {
        $this->db->where("user_id", $user_id);
        $query = $this->db->get($this->get_scope());
        return $this->after_load($query->row());
    }

    function add_data()
    {
        $data = array(
            'created' => timestamp_to_mysqldatetime(now())
        );
        return $data;
    }
}

?>