<?php
class Meeting extends MY_Model
{

    protected static $fields = array(
        '' => 'string'
    );

    function get_scope()
    {
        return "meeting";
    }

    function column_map($col)
    {
        $column_map = array('id');
        return $column_map[intval($col)];
    }

    function get_for_user($user_id = 0, $project_id = 0, $page = 0, $limit = 20)
    {
        $sql = "SELECT m.* from ".$this->get_scope()." m, meeting_user mu where m.id = mu.meeting_id and "
            ." mu.user_id = ? and m.deleted = 0 and m.ended = 0";
        $query_params = array(intval($user_id));

        $sql .= " ORDER by m.date ASC, m.time ASC";

        $query = $this->db->query($sql, $query_params);
        return $query->result();
    }

    /**
     * Add a recipient to a meeting
     * @param $meeting_id
     * @param $user_id
     */
    function add_meeting_user($meeting_id, $user_id)
    {
        $data = array(
            'meeting_id' => $meeting_id,
            'user_id' => $user_id
        );
        $this->db->query($this->db->insert_string('meeting_user', $data));
    }

    function get_meeting_user($meeting_id, $user_id)
    {
        $query = $this->db->get_where('meeting_user', array(
            'meeting_id' => $meeting_id,
            'user_id' => $user_id
        ));
        return $query->row();
    }

    /**
     * Add the sender to the meeting_user table
     * @param int $id
     */
    function after_add($id = 0)
    {
        $meeting = $this->load($id);

        /* Add the creator as a meeting participant */
        $this->add_meeting_user($id, get_user_id());

        /* Create the shortened url */
        $short_url = google_short_url($this->config->item('webapp_url').'#/meeting/'.$meeting->uuid);
        $this->update($id, array('share_link'=>$short_url));
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