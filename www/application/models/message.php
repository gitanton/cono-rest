<?php
class Message extends MY_Model
{

    protected static $fields = array(
        '' => 'string'
    );

    function get_scope()
    {
        return "message";
    }

    function column_map($col)
    {
        $column_map = array('id');
        return $column_map[intval($col)];
    }

    function get_for_user($user_id = 0, $project_id = 0, $page = 0, $limit = 20)
    {
        $sql = "SELECT m.* from " . $this->get_scope() . " m, message_user mu where m.id = mu.message_id and "
            . " mu.user_id = ? and m.deleted = 0";
        $query_params = array(intval($user_id));
        if ($project_id > 0) {
            $sql .= " and m.project_id = ? ";
            $query_params[] = $project_id;
        }

        if (!$limit) {
            $limit = DEFAULT_LIMIT;
        }

        $offset = intval($page) * $limit;
        $sql .= " ORDER by m.updated DESC";
        $sql .= " LIMIT " . $offset . ", " . $limit;

        $query = $this->db->query($sql, $query_params);
        return $query->result();
    }

    function get_replies($message_id)
    {
        $query = $this->db->get_where($this->get_scope(), array(
            'parent_id' => $message_id,
            'deleted' => 0
        ));
        return $query->result();
    }

    /**
     * Add a recipient to a message
     * @param $message_id
     * @param $user_id
     */
    function add_message_user($message_id, $user_id)
    {
        $data = array(
            'message_id' => $message_id,
            'user_id' => $user_id
        );
        $this->db->query($this->db->insert_string('message_user', $data));
    }

    function get_message_user($message_id, $user_id)
    {
        $query = $this->db->get_where('message_user', array(
            'message_id' => $message_id,
            'user_id' => $user_id
        ));
        return $query->row();
    }

    /**
     * Add the sender to the message_user table
     * @param int $id
     */
    function after_add($id = 0)
    {
        $message = $this->load($id);

        /* Don't deal with message recipients on replies */
        if (!$message->parent_id) {
            $this->add_message_user($id, get_user_id());
        }
    }

    function add_data()
    {
        $this->load->library('uuid');

        $data = array(
            'uuid' => $this->uuid->v4(),
            'created' => timestamp_to_mysqldatetime(now()),
            'updated' => timestamp_to_mysqldatetime(now())
        );
        return $data;
    }
}

?>