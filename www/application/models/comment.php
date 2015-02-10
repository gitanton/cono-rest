<?php
class Comment extends MY_Model
{

    protected static $fields = array(
        'data' => 'string'
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