<?php
class Screen extends MY_Model
{

    protected static $fields = array(
        '' => 'string'
    );

    function get_scope()
    {
        return "screen";
    }

    function column_map($col)
    {
        $column_map = array('id');
        return $column_map[intval($col)];
    }

    function get_for_project($project_id = 0) {
        $sql = "SELECT s.* from ".$this->get_scope()." s where s.project_id = ? and "
            ." s.deleted = 0";
        $query_params = array(intval($project_id));
        $sql.=" ORDER by s.ordering";

        $query = $this->db->query($sql, $query_params);
        return $query->result();
    }

    /**
     * Find the max ordering for screens for the current project (used when creating new screens)
     * @param $user_id
     * @return mixed
     */
    function get_max_ordering_for_project($project_id = 0) {
        $this->db->select_max('ordering');
        $this->db->where(array('project_id' => $project_id));
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