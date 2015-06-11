<?php
class Drawing extends MY_Model
{

    protected static $fields = array(
        'data' => 'string'
    );

    function get_scope()
    {
        return "drawing";
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
        $sql = "SELECT d.* from " . $this->get_scope() . " d where d.screen_id = ? and d.deleted = 0";
        $query_params = array(intval($screen_id));
        $sql .= " ORDER by d.ordering ASC";

        $query = $this->db->query($sql, $query_params);
        return $query->result();
    }

    /**
     * Returns the list of hotspots for the current video
     * @param int $screen_id
     * @return mixed
     */
    function get_for_video($video_id = 0)
    {
        $sql = "SELECT d.* from " . $this->get_scope() . " d where d.video_id = ? and d.deleted = 0";
        $query_params = array(intval($video_id));
        $sql .= " ORDER by d.ordering ASC";

        $query = $this->db->query($sql, $query_params);
        return $query->result();
    }

    /**
     * Find the max ordering for screens for the current project (used when creating new screens)
     * @param $screen_id
     * @return mixed
     */
    function get_max_ordering_for_screen($screen_id = 0) {
        $this->db->select_max('ordering');
        $this->db->where(array('screen_id' => $screen_id));
        $query = $this->db->get($this->get_scope());
        $row = $query->row();
        return $row->ordering;
    }

    /**
     * Find the max ordering for videos for the current project (used when creating new videos)
     * @param $screen_id
     * @return mixed
     */
    function get_max_ordering_for_video($video_id = 0) {
        $this->db->select_max('ordering');
        $this->db->where(array('video_id' => $video_id));
        $query = $this->db->get($this->get_scope());
        $row = $query->row();
        return $row->ordering;
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