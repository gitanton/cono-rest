<?php
class Hotspot extends MY_Model
{

    protected static $fields = array(
        '' => 'string'
    );

    function get_scope()
    {
        return "hotspot";
    }

    function column_map($col)
    {
        $column_map = array('id');
        return $column_map[intval($col)];
    }

    /**
     * Returns the list of hotspots for the current screen
     * @param int $screen_id
     * @return mixed
     */
    function get_for_screen($screen_id = 0)
    {
        $sql = "SELECT h.* from " . $this->get_scope() . " h where h.screen_id = ? and h.deleted = 0";
        $query_params = array(intval($screen_id));
        $sql .= " ORDER by h.ordering ASC";

        $query = $this->db->query($sql, $query_params);
        return $query->result();
    }

    function add_data()
    {
        $this->load->library('uuid');

        $data = array(
            'uuid' => $this->uuid->v4(),
            'created' => timestamp_to_mysqldatetime(now())
        );
        return $data;
    }
}

?>