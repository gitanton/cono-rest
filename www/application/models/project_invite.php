<?php
class Project_Invite extends MY_Model
{

    protected static $fields = array(
        '' => 'string'
    );

    function get_scope()
    {
        return "project_invite";
    }

    function load_by_key($key = '')
    {
        if ($key) {
            $query = $this->db->get_where($this->get_scope(), array("key" => $key));
            return $this->after_load($query->row());
        }
    }

    /**
     * Returns the matching project invite for the email/project
     * @param int $user_id
     * @return mixed
     */
    function get_for_email_project($email = '', $project_id = 0)
    {
        $this->db->where(array('email' => $email, 'project_id' => $project_id));
        $query = $this->db->get($this->get_scope());
        return $query->row();
    }

    function column_map($col)
    {
        $column_map = array('id');
        return $column_map[intval($col)];
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