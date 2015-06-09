<?php
class Project_Statistic extends MY_Model
{

    protected static $fields = array(
        '' => 'string'
    );

    function get_scope()
    {
        return "project_statistics";
    }

    function get_project_history($project_id = 0) {
        $project_history = new stdClass;
        $project_history->total_views = $this->get_project_total($project_id, PROJECT_STATISTICS_TYPE_VIEW);
        $project_history->total_comments = $this->get_project_total($project_id, PROJECT_STATISTICS_TYPE_COMMENT);
        $project_history->total_viewers = $this->get_project_total_viewers($project_id);
        $project_history->view_days = $this->get_project_view_days($project_id);
        $project_history->viewers = $this->get_project_viewers($project_id);
        return $project_history;
    }

    function get_project_viewers($project_id=0) {
        $sql = "SELECT u.uuid, u.avatar, u.fullname, u.email, u.username, ps.created from user u, ".$this->get_scope()
            ." ps where ps.project_id = ? and ps.user_id = u.id "
            ." order by ps.created desc limit 0, 100 ";
        $query = $this->db->query($sql, array($project_id));
        return $query->result();

    }

    /**
     * Get a breakdown of the count of views by date of a project
     * @param int $project_id
     * @return mixed
     */
    function get_project_view_days($project_id = 0) {
        $sql = "SELECT created, count(id) as count from ".$this->get_scope()
            ." where project_id = ? GROUP BY DATE_FORMAT(created, '%Y%m%d') order by created asc";
        $query = $this->db->query($sql, array($project_id));
        return $query->result();
    }

    /**
     * Get the total views or comments for a project
     * @param int $project_id
     * @param int $project_statistics_type_id
     * @return mixed
     */
    function get_project_total($project_id = 0, $project_statistics_type_id = 0) {
        $sql = "SELECT count(id) as cnt from ".$this->get_scope()." where project_id = ? and project_statistics_type_id = ?";
        $query = $this->db->query($sql, array($project_id, $project_statistics_type_id));
        $row = $query->row();
        return $row->cnt;
    }

    function get_project_total_viewers($project_id = 0) {
        $sql = "SELECT count( distinct(user_id)) as cnt from ".$this->get_scope()." where project_id = ?";
        $query = $this->db->query($sql, array($project_id));
        $row = $query->row();
        return $row->cnt;
    }

    /**
     * Logs a comment on a project
     */
    function comment_project($project_id) {
        $this->add(array(
            'project_id' => $project_id,
            'project_statistics_type_id' => PROJECT_STATISTICS_TYPE_COMMENT,
            'user_id' => get_user_id()
        ));
    }

    /**
     * Logs a view on a project
     */
    function view_project($project_id) {
        $this->add(array(
            'project_id' => $project_id,
            'project_statistics_type_id' => PROJECT_STATISTICS_TYPE_VIEW,
            'user_id' => get_user_id()
        ));
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
            'created' => timestamp_to_mysqldatetime(now())
        );
        return $data;
    }
}

?>