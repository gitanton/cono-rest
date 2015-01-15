<?php
class User extends MY_Model
{

    protected static $fields = array(
        'fullname' => 'string',
        'username' => 'username',
        'email' => 'email',
        'timezone' => 'string'
    );

    function get_scope()
    {
        return "user";
    }

    function column_map($col)
    {
        $column_map = array('fullname', 'username', 'created', 'last_login', 'plan_id', 'trial_end');
        return $column_map[intval($col)];
    }

    function get_all()
    {
        $this->db->where('deleted', 0);
        $query = $this->db->get($this->get_scope());
        return $query->result();
    }

    function load_by_username($username = '', $include_deleted = FALSE)
    {
        $this->db->where("username", $username);
        if (!$include_deleted) {
            $this->db->where('deleted', 0);
        }
        $query = $this->db->get($this->get_scope());
        return $this->after_load($query->row());
    }

    function delete_permanently($user_id = 0)
    {
        $this->db->delete($this->get_scope(), array("id" => $user_id));
    }

    function delete($user_id = 0)
    {
        $this->mark_deleted('project', array("creator_id" => $user_id));
        $this->mark_deleted('team', array("owner_id" => $user_id));
        parent::delete($user_id);
    }

    function load_by_email($email = '', $include_deleted = FALSE)
    {
        $this->db->where("email", $email);
        if (!$include_deleted) {
            $this->db->where('deleted', 0);
        }
        $query = $this->db->get($this->get_scope());
        return $this->after_load($query->row());
    }

    function load($id = 0)
    {
        if (intval($id)) {
            $query = $this->db->get_where($this->get_scope(), array("id" => $id, 'deleted' => 0));
            return $this->after_load($query->row());
        }
    }

    function get_name($user_id)
    {
        $this->db->where(array("id" => $user_id));
        $this->db->select('fullname');
        $query = $this->db->get($this->get_scope());
        return $query->row();
    }

    /**
     * Returns all users attached to a specified project
     * @param int $project_id
     * @return mixed
     */
    function get_for_project($project_id = 0) {
        $this->db->select('user.id,user.uuid,user.fullname,user.email,user.username,user.last_login');
        $this->db->join('project_user', 'project_user.user_id = user.id');
        $this->db->where('project_user.project_id', $project_id);
        $query = $this->db->get($this->get_scope());
        return $query->result();
    }

    /**
     * Returns all users attached to a specified message
     * @param int $message_id
     * @return mixed
     */
    function get_for_message($message_id = 0) {
        $this->db->select('user.id,user.uuid,user.fullname,user.email,user.username,user.last_login');
        $this->db->join('message_user', 'message_user.user_id = user.id');
        $this->db->where('message_user.message_id', $message_id);
        $query = $this->db->get($this->get_scope());
        return $query->result();
    }

    /**
     * Returns all users attached to a specified meeting
     * @param int $meeting_id
     * @return mixed
     */
    function get_for_meeting($meeting_id = 0, $is_connected = false) {
        $this->db->select('user.id,user.uuid,user.fullname,user.email,user.username,user.last_login');
        $this->db->join('meeting_user', 'meeting_user.user_id = user.id');
        $this->db->where('meeting_user.meeting_id', $meeting_id);
        if($is_connected) {
            $this->db->where('meeting_user.connected', 1);
        }
        $query = $this->db->get($this->get_scope());
        return $query->result();
    }

    /**
     * Returns all users attached to a specified team
     * @param int $team_id
     * @return mixed
     */
    function get_for_team($team_id = 0) {
        $this->db->select('user.id,user.uuid,user.fullname,user.email,user.username,user.last_login');
        $this->db->join('team_user', 'team_user.user_id = user.id');
        $this->db->where('team_user.team_id', $team_id);
        $query = $this->db->get($this->get_scope());
        return $query->result();
    }

    /**
     * Validates that the specified user is a member of the specified project
     * @param int $project_id
     * @param int $user_id
     * @return bool
     */
    function is_on_project($project_id = 0, $user_id = 0) {
        if($project_id > 0 && $user_id > 0) {
            $this->db->join('project_user', 'project_user.user_id = user.id');
            $this->db->where('project_user.project_id', $project_id);
            $this->db->where('project_user.user_id', $user_id);
            $query = $this->db->get($this->get_scope());
            $row = $query->row();
            if($row) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validates that a user is a member of the specified team
     * @param int $team_id
     * @param int $user_id
     * @return bool
     */
    function is_on_team($team_id = 0, $user_id = 0) {
        if($team_id > 0 && $user_id > 0) {
            $this->db->join('team_user', 'team_user.user_id = user.id');
            $this->db->where('team_user.team_id', $team_id);
            $this->db->where('team_user.user_id', $user_id);
            $query = $this->db->get($this->get_scope());
            $row = $query->row();
            if($row) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validates that a user is a recipient of the specified message
     * @param int $message_id
     * @param int $user_id
     * @return bool
     */
    function is_on_message($message_id = 0, $user_id = 0) {
        if($message_id > 0 && $user_id > 0) {
            $this->db->join('message_user', 'message_user.user_id = user.id');
            $this->db->where('message_user.message_id', $message_id);
            $this->db->where('message_user.user_id', $user_id);
            $query = $this->db->get($this->get_scope());
            $row = $query->row();
            if($row) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validates that a user is an attendee of the specified meeting
     * @param int $meeting_id
     * @param int $user_id
     * @return bool
     */
    function is_on_meeting($meeting_id = 0, $user_id = 0) {
        if($meeting_id > 0 && $user_id > 0) {
            $this->db->join('meeting_user', 'meeting_user.user_id = user.id');
            $this->db->where('meeting_user.meeting_id', $meeting_id);
            $this->db->where('meeting_user.user_id', $user_id);
            $query = $this->db->get($this->get_scope());
            $row = $query->row();
            if($row) {
                return true;
            }
        }
        return false;
    }

    function login($username, $password)
    {
        $query = $this->db->get_where($this->get_scope(), array("username" => $username, 'deleted' => 0));
        $user = $query->row();

        if ($user) {
            $password = sha1($password . $user->salt);

            if ($user->password != $password) {
                unset($user);
            }

            if (isset($user) && $user) {
                return $this->after_load($user);
            }
        }
    }

    function login_admin($username, $password)
    {
        $query = $this->db->get_where($this->get_scope(), array("username" => $username, "password" => $password, 'user_type_id' => 99, 'deleted' => 0));
        return $query->row();
    }

    function from_post()
    {

        $data = array(
            'fullname' => trim($this->post('fullname', TRUE)),
            'email' => trim($this->post('email', TRUE)),
        );
        return $data;
    }

    function change_password($id = 0, $password)
    {
        if (intval($id)) {

            $salt = $this->create_salt();
            $password = sha1($password . $salt);

            /** Update Community **/
            $data = array('password' => $password, 'salt' => $salt);
            $this->db->where('id', $id);
            $this->db->update($this->get_scope(), $data);
        }
    }

    function reset_password($id = 0)
    {
        $clear_password = random_string('alnum', 8);
        $salt = $this->create_salt();
        $password = sha1($clear_password . $salt);

        /** Update Community **/
        $data = array('password' => $password, 'salt' => $salt);

        $this->db->where('id', $id);
        $this->db->update($this->get_scope(), $data);
        return $clear_password;
    }

    /**
     * Update the password and created the salt
     * @param $data - data going into the add()
     */
    public function update_add_data($data)
    {

        if (!isset($data['salt']) || !$data['salt']) {
            $salt = $this->create_salt();
            $password = sha1($data['password'] . $salt);
            $data['password'] = $password;
            $data['salt'] = $salt;
        }
        return $data;
    }

    /**
     * Update the password and created the salt
     * @param $data - data going into the add()
     */
    public function update_update_data($data)
    {
        if (isset($data['password'])) {
            $salt = $this->create_salt();
            $password = sha1($data['password'] . $salt);
            $data['password'] = $password;
            $data['salt'] = $salt;
        }
        return $data;
    }

    function add_data()
    {
        $this->load->library('uuid');

        $data = array(
            'uuid' => $this->uuid->v4(),
            'user_type_id' => USER_TYPE_USER,
            'created' => timestamp_to_mysqldatetime(now())
        );
        return $data;
    }

    function record_login($user_id = 0)
    {
        $user = $this->User->load($user_id);
        $data = array('last_login' => timestamp_to_mysqldatetime(now()));
        $this->db->where('id', $user_id);
        $this->db->update($this->get_scope(), $data);
    }

    function get_count($filter = '', $user_type_id = 0)
    {
        $sql = 'select count(u.id) as cnt ';

        $where = 'WHERE u.deleted = 0';
        $from = ' from ' . $this->get_scope() . ' u';
        $query_params = array();

        if ($filter) {
            $where .= ' AND (u.fullname like ?)';
            array_unshift($query_params, $filter . '%');
        }

        if ($user_type_id > 0) {
            $where .= " AND u.user_type_id = ? ";
            $query_params[] = $user_type_id;
        }

        $sql .= ' ' . $from . ' ' . $where;

        $query = $this->db->query($sql, $query_params);
        $row = $query->row();
        return $row->cnt;
    }

    function get_list($limit = 999, $offset = 0, $ordering = '', $filter = '', $user_type_id = 0)
    {
        if (!$ordering) {
            $ordering = array('sort' => 'fullname', 'dir' => 'ASC');
        } else {
            $ordering['sort'] = $this->column_map($ordering['sort']);
        }

        $query_params = array();

        $sql = "SELECT u.* ";

        $where = ' WHERE u.deleted = 0';
        $from = ' from ' . $this->get_scope() . ' u';

        if ($filter) {
            $where .= ' AND (u.fullname like ?)';
            array_unshift($query_params, $filter . '%');
        }

        if ($user_type_id > 0) {
            $where .= " and u.user_type_id = ? ";
            $query_params[] = $user_type_id;
        }

        $query_params[] = $offset;
        $query_params[] = $limit;

        $sql .= ' ' . $from . ' ' . $where . " ORDER BY " . $this->get_ordering($ordering) . " LIMIT ?, ? ";

        $query = $this->db->query($sql, $query_params);
        //echo $this->db->last_query();
        return $query->result();
    }

}

?>