<?php
class Meeting_Comment extends MY_Model
{

    protected static $fields = array(
        '' => 'string'
    );

    function get_scope()
    {
        return "meeting_comment";
    }

    function column_map($col)
    {
        $column_map = array('id');
        return $column_map[intval($col)];
    }

    /**
     * Returns the matching meeting comments for the given meeting id
     * @param int $user_id
     * @return mixed
     */
    function get_for_meeting($meeting_id = 0, $last_id = 0)
    {
        $this->db->where(array('meeting_id' => $meeting_id));
        if($last_id) {
            $this->db->where('id > ', $last_id);
        }
        $this->db->order_by('id', 'asc');

        $query = $this->db->get($this->get_scope());
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