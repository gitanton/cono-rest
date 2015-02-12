<?php
class Plan extends MY_Model
{

    protected static $fields = array(
        '' => 'string'
    );

    function get_scope()
    {
        return "plan";
    }

    function column_map($col)
    {
        $column_map = array('id');
        return $column_map[intval($col)];
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