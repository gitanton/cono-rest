<?php
class Comment extends MY_Model
{

    protected static $fields = array(
        'comment' => 'string',
        'data' => 'string',
        'time' => 'string'
    );

    function get_scope()
    {
        return "comment";
    }

    function column_map($col)
    {
        $column_map = array('id');
        return $column_map[intval($col)];
    }

    /**
     * Returns the list of comments for the current project
     * @param int $screen_id
     * @return mixed
     */
    function get_for_project($project_id = 0)
    {
        $sql = "SELECT c.* from " . $this->get_scope() . " c where c.project_id = ? and c.deleted = 0";
        $query_params = array(intval($project_id));
        $sql .= " ORDER by c.created ASC";

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
        $sql = "SELECT c.* from " . $this->get_scope() . " c where c.video_id = ? and c.deleted = 0";
        $query_params = array(intval($video_id));
        $sql .= " ORDER by c.ordering ASC";

        $query = $this->db->query($sql, $query_params);
        return $query->result();
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