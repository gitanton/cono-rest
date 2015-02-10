<?php
class Activity extends MY_Model
{

    protected static $fields = array(
        'project_id' => 'int'
    );

    function get_scope()
    {
        return "activity";
    }

    function column_map($col)
    {
        $column_map = array('id');
        return $column_map[intval($col)];
    }

    function get_for_project($project_id = 0, $page = 0, $limit = 20)
    {
        $sql = "SELECT a.* from ".$this->get_scope()." a where a.project_id = ? and a.deleted = 0";
        $query_params = array(intval($project_id));

        if (!$limit) {
            $limit = DEFAULT_LIMIT;
        }

        $offset = intval($page) * $limit;
        $sql .= " ORDER by a.created DESC";
        $sql .= " LIMIT ".$offset.", ".$limit;

        $query = $this->db->query($sql, $query_params);
        return $query->result();
    }

    function get_for_team($team_id = 0, $page = 0, $limit = 20)
    {
        $sql = "SELECT a.* from ".$this->get_scope()." a where a.team_id = ? and a.deleted = 0";
        $query_params = array(intval($team_id));

        if (!$limit) {
            $limit = DEFAULT_LIMIT;
        }

        $offset = intval($page) * $limit;
        $sql .= " ORDER by a.created DESC";
        $sql .= " LIMIT ".$offset.", ".$limit;

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