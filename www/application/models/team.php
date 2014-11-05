<?php
class Team extends MY_Model
{

    protected static $fields = array(
        'name' => 'string',
        'creator_id' => 'int',
        'archived' => 'int',
        'type_id' => 'int'
    );

    function get_scope()
    {
        return "team";
    }

    function column_map($col)
    {
        $column_map = array('id');
        return $column_map[intval($col)];
    }

    /**
     * Returns the list of teams for the current user
     * @param int $user_id
     * @return mixed
     */
    function get_for_user($user_id = 0)
    {
        $sql = "SELECT t.* from " . $this->get_scope() . " t, team_user tu where t.id = tu.team_id and "
            . " tu.user_id = ? and t.deleted = 0";
        $query_params = array(intval($user_id));
        $sql .= " ORDER by t.id ASC";

        $query = $this->db->query($sql, $query_params);
        return $query->result();
    }

    /** Add the creator to the team_user table */
    function after_add($id)
    {
        $user_id = get_user_id();
        $data = array(
            'team_id' => $id,
            'user_id' => $user_id
        );
        $query = $this->db->query($this->db->insert_string('team_user', $data));
    }

    function add_data()
    {
        $this->load->library('uuid');

        $data = array(
            'uuid' => $this->uuid->v4(),
            'owner_id' => intval(get_user_id()),
            'deleted' => 0,
            'created' => timestamp_to_mysqldatetime(now())
        );
        return $data;
    }
}

?>