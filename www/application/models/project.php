<?php
class Project extends MY_Model
{

    protected static $fields = array(
        'name' => 'string',
        'user_id' => 'int',
        'archived' => 'int',
        'type_id' => 'int'
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

    /**
     * Adds a user to the project_user table for the specified project
     * @param $project_id
     * @param $user_id
     */
    function add_user($project_id = 0, $user_id = 0) {
        $this->db->query($this->db->insert_string('project_user', array(
            'project_id' => $project_id,
            'user_id' => $user_id,
            'notify' => 1
        )));
        $id = $this->db->insert_id();
        return $id;
    }

    function get_for_user($user_id = 0, $archived = 0) {
        $sql = "SELECT p.*, pu.ordering, pu.notify from ".$this->get_scope()." p, project_user pu where p.id = pu.project_id and "
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

    function update_project_user($user_id, $project_id, $data) {
        $this->db->where(array('user_id' => $user_id, 'project_id' => $project_id));
        $this->db->update('project_user', $data);
    }

    /**
     * Returns the list of projects owned by a specific user (that aren't archived)
     * @param $user_id
     */
    function get_owned_by_user($user_id) {
        $this->db->where(array('creator_id' => $user_id, 'archived' => 0, 'deleted' => 0));
        $query = $this->db->get($this->get_scope());
        return $query->result();
    }

    function get_for_user_team($user_id = 0, $team_id = 0, $archived = false) {
        $sql = "SELECT p.*, pu.ordering from ".$this->get_scope()." p, project_user pu where p.id = pu.project_id and "
            ." pu.user_id = ? and p.deleted = 0 and p.team_id = ?";
        $query_params = array(intval($user_id), intval($team_id));

        if($archived>1) {
            $sql.=" AND p.archived = 1";
        } else if($archived===0) {
            $sql.=" AND p.archived = 0";
        }
        $sql.=" ORDER by pu.ordering";

        $query = $this->db->query($sql, $query_params);
        return $query->result();
    }

    function remove_for_user_team($user_id, $team_id) {
        $sql = "delete from project_user where user_id = ? and project_id in (select id from project where team_id = ?)";
        $query_params = array(intval($user_id), intval($team_id));

        $query = $this->db->query($sql, $query_params);
    }

    /**
     * Find the max ordering for projects for the current user (used when creating new projects
     * @param $user_id
     * @return mixed
     */
    function get_max_ordering_for_user($user_id = 0) {
        $this->db->select_max('ordering');
        $this->db->where(array('user_id' => $user_id));
        $query = $this->db->get('project_user');
        $row = $query->row();
        return $row->ordering;
    }

    function update_ordering($user_id, $project_id, $ordering) {
        $this->db->where(array('user_id' => $user_id, 'project_id' => $project_id));
        $this->db->update('project_user', array('ordering' => $ordering));
    }

    /**
     * Set the ordering on the project based on what projects that this user already has on their other projects
     * @param $project
     * @return mixed
     */
    function after_load($project) {
        if($project) {
            $this->db->where(array('user_id' => get_user_id(), 'project_id' => $project->id));
            $query = $this->db->get('project_user');
            $row = $query->row();
            if($row) {
                $project->ordering = $row->ordering;
            }
        }
        return $project;
    }

    /**
     * Duplicates an existing project and copies all users from the old project onto the new one
     * @param $project
     * @param int $creator_id
     * @return mixed
     */
    function duplicate($project, $creator_id = 0, $project_name = '') {
        $this->load->model(array('Video', 'Screen'));
        if(!$creator_id) {
            $creator_id = get_user_id();
        }
        if(!$project_name) {
            $project_name = $project->name;
        }
        $data = array(
            'name' => $project_name,
            'type_id' => $project->type_id,
            'team_id' => get_team_id(),
            'creator_id' => $creator_id
        );
        $duplicate_id = $this->add($data);

        /* Copy the users on the project */
        $this->copy_users($project->id, $duplicate_id);
        /* Copy the videos on the project */
        $this->Video->copy_to_project($project->id, $duplicate_id);
        /* Copy the screens on the project */
        $this->Screen->copy_to_project($project->id, $duplicate_id);
        return $duplicate_id;
    }

    /**
     * Copy users from one project to the other
     * @param $project_id
     * @param $duplicate_id
     */
    function copy_users($source_id = 0, $destination_id = 0) {
        $source_users = $this->User->get_for_project($source_id);
        foreach($source_users as $user) {
            /* Make sure the user isn't already on the project */
            if(!$this->User->is_on_project($destination_id, $user->id)) {
                $ordering = intval($this->get_max_ordering_for_user($user->id)) + 1;
                $data = array(
                    'project_id' => $destination_id,
                    'user_id' => $user->id,
                    'ordering' => $ordering
                );
                $this->db->query($this->db->insert_string('project_user', $data));
            }
        }
    }

    /**
     * Add the creator to the project_user table, also update the ordering to be above the maximum for this user
     * @param int $id
     */
    function after_add($id = 0) {
        $user_id = get_user_id();
        $ordering = intval($this->get_max_ordering_for_user($user_id)) + 1;
        $data = array(
            'project_id' => $id,
            'user_id' => $user_id,
            'ordering' => $ordering
        );
        $this->db->query($this->db->insert_string('project_user', $data));
    }

    function add_data()
    {
        $this->load->library('uuid');

        $data = array(
            'uuid' => $this->uuid->v4(),
            'creator_id' => intval(get_user_id()),
            'team_id' => intval(get_team_id()),
            'archived' => 0,
            'deleted' => 0,
            'created' => timestamp_to_mysqldatetime(now())
        );
        return $data;
    }
}

?>