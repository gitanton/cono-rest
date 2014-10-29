<?php
class Project extends MY_Model
{

    protected static $fields = array(
        'name' => 'string',
        'user_id' => 'int',
        'archived' => 'int',
        'type_id' => 'int',
        'ordering' => 'int'
    );

    function get_scope()
    {
        return "project";
    }

    function column_map($col)
    {
        $column_map = array('id');
        return $column_map[intval($col)];
    }

    function get_for_user($user_id, $archived = 0) {
        $sql = "SELECT p.*, pu.ordering from ".$this->get_scope()." p, project_user pu where p.id = pu.project_id and "
            ." pu.user_id = ? and p.deleted = 0";
        $query_params = array(intval($user_id));

        if($archived>1) {
            $sql.=" AND p.archived = ?";
            $query_params[] = $archived;
        }
        $sql.=" ORDER by pu.ordering";

        $query = $this->db->query($sql, $query_params);
        return $query->result();
    }

    function get_max_ordering_for_user($user_id) {
        $this->db->select_max('ordering');
        $this->db->where(array('user_id' => $user_id));
        $query = $this->db->get('project_user');
        $row = $query->row();
        return $row->ordering;
    }

    /* Set the ordering on the project */
    function after_load($project) {
        if($project) {
            $this->db->where(array('user_id' => get_user_id(), 'project_id' => $project->id));
            $query = $this->db->get('project_user');
            $row = $query->row();
            $project->ordering = $row->ordering;
        }
        return $project;
    }

    /** Add the creator to the project_user table, also update the ordering to be above the maximum for this user */
    function after_add($id) {
        $user_id = get_user_id();
        $ordering = intval($this->get_max_ordering_for_user($user_id)) + 1;
        $data = array(
            'project_id' => $id,
            'user_id' => $user_id,
            'ordering' => $ordering
        );
        $query = $this->db->query($this->db->insert_string('project_user', $data));
    }

    function add_data()
    {
        $this->load->library('uuid');

        $data = array(
            'uuid' => $this->uuid->v4(),
            'creator_id' => intval(get_user_id()),
            'archived' => 0,
            'deleted' => 0,
            'created' => timestamp_to_mysqldatetime(now())
        );
        return $data;
    }
}

?>