<?php
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


/**********************************************
 * Project
 **********************************************/
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

/**********************************************
 * Screen
 **********************************************/
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


/**********************************************
 * User
 **********************************************/
function activity_user_join_team($team_id, $user_id) {

    $CI =& get_instance();
    $CI->load->model('Team');

    $team = $CI->Team->load($team_id);
    $user = $CI->User->load($user_id);
    if($team->name) {
        $title = $user->fullname." has joined the '".$team->name."' team.";
    } else {
        $title = $user->fullname." has joined the team.";
    }
    activity_add($user->id, $team->id, null, $team->id, ACTIVITY_TYPE_USER_TEAM_JOIN, $title);
}

function activity_user_join_project($project_id, $user_id) {

    $CI =& get_instance();
    $CI->load->model(array('Team','Project'));

    $project = $CI->Project->load($project_id);
    $user = $CI->User->load($user_id);
    $title = $user->fullname." has been added to the '".$project->name."' Project.";
    activity_add($user->id, $project->team_id, $project->id, $project->id, ACTIVITY_TYPE_USER_PROJECT_JOIN, $title);
}


/**********************************************
 * Message
 **********************************************/

function activity_add_message($message_id) {
    $CI =& get_instance();
    $CI->load->model(array('Message','Project'));

    $message = $CI->Message->load($message_id);
    $user = $CI->User->load($message->sender_id);
    $project = $CI->Project->load($message->project_id);
    $title = $user->fullname." has sent a new message on the '".$project->name."' Project.";
    activity_add($user->id, $project->team_id, $project->id, $message->id, ACTIVITY_TYPE_MESSAGE_ADD, $title);
}

function activity_reply_message($message_id) {
    $CI =& get_instance();
    $CI->load->model(array('Message','Project'));

    $message = $CI->Message->load($message_id);
    $user = $CI->User->load($message->sender_id);
    $project = $CI->Project->load($message->project_id);
    $title = $user->fullname." has sent a new reply on the '".$project->name."' Project.";
    activity_add($user->id, $project->team_id, $project->id, $message->id, ACTIVITY_TYPE_MESSAGE_REPLY, $title);
}

function activity_delete_message($message_id) {
    $CI =& get_instance();
    $CI->load->model(array('Message','Project'));

    $message = $CI->Message->load($message_id);
    $user = $CI->User->load($message->sender_id);
    $project = $CI->Project->load($message->project_id);
    $title = $user->fullname." deleted a message on the '".$project->name."' Project.";
    activity_add($user->id, $project->team_id, $project->id, $message->id, ACTIVITY_TYPE_MESSAGE_DELETE, $title);
}

/**********************************************
 * Hotspot
 **********************************************/

function activity_add_hotspot_video($hotspot_id) {
    $CI =& get_instance();
    $CI->load->model(array('Hotspot', 'Video', 'Project'));

    $hotspot = $CI->Hotspot->load($hotspot_id);
    $user = $CI->User->load($hotspot->creator_id);
    $video = $CI->Video->load($hotspot->video_id);
    $project = $CI->Project->load($video->project_id);
    $title = $user->fullname." added a hotspot to a video on the '".$project->name."' Project.";
    activity_add($user->id, $project->team_id, $project->id, $hotspot->id, ACTIVITY_TYPE_HOTSPOT_VIDEO_ADD, $title);
}

function activity_add_hotspot_screen($hotspot_id) {
    $CI =& get_instance();
    $CI->load->model(array('Hotspot', 'Screen', 'Project'));

    $hotspot = $CI->Hotspot->load($hotspot_id);
    $user = $CI->User->load($hotspot->creator_id);
    $screen = $CI->Screen->load($hotspot->screen_id);
    $project = $CI->Project->load($screen->project_id);
    $title = $user->fullname." added a hotspot to a screen on the '".$project->name."' Project.";
    activity_add($user->id, $project->team_id, $project->id, $hotspot->id, ACTIVITY_TYPE_HOTSPOT_SCREEN_ADD, $title);
}

function activity_delete_hotspot($hotspot_id) {
    $CI =& get_instance();
    $CI->load->model(array('Hotspot', 'Screen', 'Project'));

    $hotspot = $CI->Hotspot->load($hotspot_id);
    $user = $CI->User->load($hotspot->creator_id);
    $screen = $CI->Screen->load($hotspot->screen_id);
    $project = $CI->Project->load($screen->project_id);
    $title = $user->fullname." deleted a hotspot on the '".$project->name."' Project.";
    activity_add($user->id, $project->team_id, $project->id, $hotspot->id, ACTIVITY_TYPE_HOTSPOT_SCREEN_ADD, $title);
}

/**********************************************
 * Comment
 **********************************************/

function activity_add_comment_video($comment_id) {
    $CI =& get_instance();
    $CI->load->model(array('Comment', 'Project'));

    $comment = $CI->Comment->load($comment_id);
    $user = $CI->User->load($comment->creator_id);
    $project = $CI->Project->load($comment->project_id);
    $title = $user->fullname." added a comment to a video on the '".$project->name."' Project.";
    activity_add($user->id, $project->team_id, $project->id, $comment->id, ACTIVITY_TYPE_COMMENT_ADD, $title);
}

function activity_add_comment_screen($comment_id) {
    $CI =& get_instance();
    $CI->load->model(array('Comment', 'Project'));

    $comment = $CI->Comment->load($comment_id);
    $user = $CI->User->load($comment->creator_id);
    $project = $CI->Project->load($comment->project_id);
    $title = $user->fullname." added a comment to a screen on the '".$project->name."' Project.";
    activity_add($user->id, $project->team_id, $project->id, $comment->id, ACTIVITY_TYPE_COMMENT_ADD, $title);
}

/**********************************************
 * Meeting
 **********************************************/

function activity_add_meeting($meeting_id) {
    $CI =& get_instance();
    $CI->load->model(array('Meeting', 'Project'));

    $meeting = $CI->Meeting->load($meeting_id);
    $user = $CI->User->load($meeting->creator_id);
    $project = $CI->Project->load($meeting->project_id);
    $title = $user->fullname." scheduled a meeting on the '".$project->name."' Project.";
    activity_add($user->id, $project->team_id, $project->id, $meeting->id, ACTIVITY_TYPE_MEETING_ADD, $title);
}

function activity_delete_meeting($meeting_id) {
    $CI =& get_instance();
    $CI->load->model(array('Hotspot', 'Screen', 'Project'));

    $CI->load->model(array('Meeting', 'Project'));

    $meeting = $CI->Meeting->load($meeting_id);
    $user = $CI->User->load($meeting->creator_id);
    $project = $CI->Project->load($meeting->project_id);
    $title = $user->fullname." deleted a hotspot on the '".$project->name."' Project.";
    activity_add($user->id, $project->team_id, $project->id, $meeting->id, ACTIVITY_TYPE_MEETING_DELETE, $title);
}
?>