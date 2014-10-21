<?php
class User extends MY_Model
{

    protected static $fields = array(
        'firstname' => 'string',
        'lastname' => 'string',
        'username' => 'username',
        'password' => 'password',
        'user_type_id' => 'int',
        'email' => 'email',
        'phone' => 'phone',
        'timezone' => 'string'
    );

    function get_scope()
    {
        return "user";
    }

    function column_map($col)
    {
        $column_map = array('lastname', 'firstname', 'username', 'created', 'last_login', 'plan_id', 'trial_end');
        return $column_map[intval($col)];
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
        parent::delete($user_id);
        //$this->mark_deleted('request', array("user_id" => $user_id));
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
        $this->db->select('firstname, lastname');
        $query = $this->db->get($this->get_scope());
        return $query->row();
    }

    function login($username, $password)
    {
        $query = $this->db->get_where($this->get_scope(), array("username" => $username, 'deleted' => 0));
        $user = $query->row();

        if($user) {
            $password = sha1($password.$user->salt);

            if($user->password!=$password) {
                unset($user);
            }

            if(isset($user) && $user) {
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

        $data = array('firstname' => trim($this->input->post('firstname', TRUE)),
            'lastname'  => trim($this->input->post('lastname', TRUE)),
            'email'     => trim($this->input->post('email', TRUE)),
            'phone'     => phone_format(trim($this->input->post('phone', TRUE)))
        );
        return $data;
    }

    function register() {

        $salt = $this->create_salt();
        $password = $this->input->post('password');
        $password = sha1($password.$salt);

        $data = array(
            'username' => $this->input->post('username'),
            'email' => $this->input->post('email'),
            'password' => $password,
            'salt' => $salt
        );

        return $this->add($data);
    }

    function change_password($id = 0, $password)
    {
        if (intval($id)) {

            $salt = $this->create_salt();
            $password = sha1($password.$salt);

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
        $password = sha1($clear_password.$salt);

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
    public function update_add_data($data) {

        if(!isset($data['salt']) || !$data['salt']) {
            $salt = $this->create_salt();
            $password = sha1($data['password'].$salt);
            $data['password'] = $password;
            $data['salt'] = $salt;
        }
        return $data;
    }

    /**
     * Update the password and created the salt
     * @param $data - data going into the add()
     */
    public function update_update_data($data) {
        if(isset($data['password'])) {
            $salt = $this->create_salt();
            $password = sha1($data['password'].$salt);
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
            $parts = explode(' ', $filter);
            if (sizeof($parts) > 1) {
                $where .= ' AND (u.firstname like ? AND u.lastname like ?)';
                array_unshift($query_params, $parts[1] . '%');
                array_unshift($query_params, $parts[0] . '%');
            } else {
                $where .= ' AND (u.lastname like ? OR u.firstname like ?)';
                array_unshift($query_params, $filter . '%');
                array_unshift($query_params, $filter . '%');
            }
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
            $ordering = array('sort' => 'lastname', 'dir' => 'ASC');
        } else {
            $ordering['sort'] = $this->column_map($ordering['sort']);
        }

        $query_params = array();

        $sql = "SELECT u.* ";

        $where = ' WHERE u.deleted = 0';
        $from = ' from ' . $this->get_scope() . ' u';
        if ($filter) {
            $parts = explode(' ', $filter);
            if (sizeof($parts) > 1) {
                $where .= ' AND (u.firstname like ? AND u.lastname like ?)';
                array_unshift($query_params, $parts[1] . '%');
                array_unshift($query_params, $parts[0] . '%');
            } else {
                $where .= ' AND (u.lastname like ? OR u.firstname like ?)';
                array_unshift($query_params, $filter . '%');
                array_unshift($query_params, $filter . '%');
            }
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

    function edit_from_get($id)
    {
        $data = array('firstname' => trim($this->input->get('firstname', TRUE)), 'lastname' => trim($this->input->get('lastname', TRUE)), 'email' => trim($this->input->get('email', TRUE)), 'phone' => trim($this->input->get('phone', TRUE)), 'notify_deal' => intval($this->input->get('notify_deal')), 'notify_task' => intval($this->input->get('notify_task')), 'notify_comment' => intval($this->input->get('notify_comment')), 'notify_file' => intval($this->input->get('notify_file')), 'notify_join' => intval($this->input->get('notify_new_user')),);
        $this->db->where(array('id' => $id));
        $this->db->update($this->get_scope(), $data);
    }

    function blank()
    {
        $user = new stdClass;
        $user->id = 0;
        $user->user_type_id = 1;
        $user->deleted = 0;
        $user->inactive = 0;
        $user->email = '';
        $user->username = '';
        $user->firstname = '';
        $user->lastname = '';
        $user->password = random_string('alnum', 8);
        $user->phone = '';
        return $user;
    }

}

?>