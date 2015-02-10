<?
/**
 * Helper for creating activities for various object actions
 */
function activity_add($user_id, $team_id, $project_id, $object_id, $activity_type_id, $title, $content='') {

    $CI =& get_instance();
    $CI->load->model('Activity');

    $data = array(
        'creator_id' => $user_id,
        'team_id' => $team_id,
        'project_id' => $project_id,
        'activity_type_id' => $activity_type_id,
        'object_id' => $object_id,
        'title' => $title
    );
    if($content) {
        $data['content'] = $content;
    }
    $CI->Activity->add($data);

}

function activity_add_project($project_id, $user_id) {

    $CI =& get_instance();
    $CI->load->model('Project');

    $project = $CI->Project->load($project_id);
    $user = $CI->User->load($user_id);
    $title = $user->fullname." created a the '".$project->name."' project.";
    activity_add($user->id, $project->team_id, $project->id, $project->id, ACTIVITY_TYPE_PROJECT_ADD, $title);
}

function activity_update_project($project_id, $user_id) {

    $CI =& get_instance();
    $CI->load->model('Project');

    $project = $CI->Project->load($project_id);
    $user = $CI->User->load($user_id);
    $title = $user->fullname." updated the '".$project->name."' project.";
    activity_add($user->id, $project->team_id, $project->id, $project->id, ACTIVITY_TYPE_PROJECT_UPDATE, $title);
}

function activity_delete_project($project_id, $user_id) {

    $CI =& get_instance();
    $CI->load->model('Project');

    $project = $CI->Project->load($project_id);
    $user = $CI->User->load($user_id);
    $title = $user->fullname." deleted the '".$project->name."' project.";
    activity_add($user->id, $project->team_id, $project->id, $project->id, ACTIVITY_TYPE_PROJECT_DELETE, $title);
}

function activity_add_screen($screen_id, $user_id) {

    $CI =& get_instance();
    $CI->load->model('Screen');

    $screen = $CI->Screen->load($screen_id);
    $project = $CI->Project->load($screen->project_id);
    $user = $CI->User->load($user_id);
    $title = $user->fullname." uploaded a new screen to the '".$project->name."' project.";
    activity_add($user->id, $project->team_id, $project->id, $screen_id, ACTIVITY_TYPE_SCREEN_ADD, $title);
}

function activity_update_screen($screen_id, $user_id) {

    $CI =& get_instance();
    $CI->load->model('Screen');

    $screen = $CI->Screen->load($screen_id);
    $project = $CI->Project->load($screen->project_id);
    $user = $CI->User->load($user_id);
    $title = $user->fullname." updated a screen on the '".$project->name."' project.";
    activity_add($user->id, $project->team_id, $project->id, $screen_id, ACTIVITY_TYPE_SCREEN_UPDATE, $title);
}

function activity_delete_screen($screen_id, $user_id) {

    $CI =& get_instance();
    $CI->load->model('Screen');

    $screen = $CI->Screen->load($screen_id);
    $project = $CI->Project->load($screen->project_id);
    $user = $CI->User->load($user_id);
    $title = $user->fullname." deleted a screen from the '".$project->name."' project.";
    activity_add($user->id, $project->team_id, $project->id, $screen_id, ACTIVITY_TYPE_SCREEN_DELETE, $title);
}
?>