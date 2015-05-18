<?php

function loggly($data = '')
{

    if (is_array($data) || is_object($data)) {
        $data = json_encode($data);
    }

    $CI =& get_instance();
    $url = sprintf("http://logs-01.loggly.com/inputs/%s/tag/%s/", $CI->config->item('loggly_token'), $CI->config->item('loggly_tag'));
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($curl);
}

function dologin($username = '', $password = '')
{
    $CI =& get_instance();

    /** Do Login **/
    if (!$username) {
        $username = $CI->input->post('username', TRUE);
    }

    if (!$password) {
        $password = $CI->input->post('password', TRUE);
    }

    $user = $CI->User->login($username, $password);

    return $user;
}

function get_user_type_id()
{
    $user_type_id = 0;

    $CI =& get_instance();
    $CI->load->database();
    if ($CI->session->userdata('user_type_id')) {
        return $CI->session->userdata('user_type_id');
    }

    if ($CI->session->userdata(SESS_USER_ID)) {
        $CI->load->model('User');
        $user_id = $CI->session->userdata(SESS_USER_ID);
        $user = $CI->User->load($user_id);
        if ($user) {
            $user_type_id = $user->user_type_id;
            $CI->session->set_userdata('user_type_id', $user_type_id);
        }
    }

    return $user_type_id;
}

function get_user($user_id = 0)
{
    $CI =& get_instance();
    $CI->load->database();

    if ($user_id === 0) {
        $user_id = $CI->session->userdata(SESS_USER_ID);
        if (!$user_id) {
            $user_id = $CI->session->userdata(SESS_ADMIN_USER_ID);
        }
    }

    if ($user_id) {
        $CI->load->model('User');
        $user = $CI->User->load($user_id);
        return $user;
    }
}

function get_user_json($user_id = 0)
{
    $user = get_user($user_id);
    if (!$user) {
        return '{}';
    } else {
        $user = clean_user($user);
        return json_encode($user);
    }
}

function clean_user($user)
{
    unset($user->id);
    unset($user->password);
    unset($user->salt);
    unset($user->user_type_id);
    unset($user->created);
    unset($user->deleted);
    unset($user->inactive);

    return $user;
}

function get_user_name($user_id = 0)
{

    $user = get_user($user_id);
    if ($user) {
        return $user->firstname . " " . $user->lastname;
    }
}

function get_user_id()
{
    $CI =& get_instance();
    return $CI->session->userdata(SESS_USER_ID);
}

function get_team_id()
{
    $CI =& get_instance();
    return $CI->session->userdata(SESS_TEAM_ID);
}

function get_subscription_id()
{
    $CI =& get_instance();
    return $CI->session->userdata(SESS_SUBSCRIPTION_ID);
}

function get_user_email()
{
    $CI =& get_instance();
    $CI->load->database();
    if ($CI->session->userdata(SESS_USER_ID)) {
        $CI->load->model('User');
        $user_id = $CI->session->userdata(SESS_USER_ID);
        $user = $CI->User->load($user_id);
        return $user->email;
    }
}

function object_list($table_name, $sort_col = 'id', $sort_order = 'ASC')
{
    $CI =& get_instance();
    $CI->load->database();

    $options = array();
    $CI->db->order_by($sort_col, $sort_order);
    $query = $CI->db->get($table_name);

    $results = array();
    foreach ($query->result() as $result) {
        if (isset($result->enabled) && !$result->enabled)
            continue;

        $results[] = $result;
    }

    return $results;
}

function table_lookup($table_name, $id)
{
    if (intval($id)) {
        $CI =& get_instance();
        $CI->load->database();

        $CI->db->where('id', $id);
        $query = $CI->db->get($table_name);
        $result = $query->row();
        if ($result) {

            if (isset($result->enabled) && !$result->enabled)
                return NULL;

            return $result->name;
        }
    }
}

function table_lookup_reverse($table_name, $name)
{
    if ($name) {
        $CI =& get_instance();
        $CI->load->database();

        $CI->db->where('name', $name);
        $query = $CI->db->get($table_name);
        $result = $query->row();
        if ($result) {

            if (isset($result->enabled) && !$result->enabled)
                return NULL;

            return $result->id;
        }
    }
}

function convert_field($value, $datatype = '')
{
    //echo "BEFORE: ".$value."\n";
    $value = trim($value);

    if ($datatype == 'phone') {
        $value = phone_format($value);
    } else if ($datatype == 'date') {
        $value = mysql_date($value);
    } else if ($datatype == 'time') {
        $value = mysql_time($value);
    } else if ($datatype == 'int') {
        if ($value || $value === 0 || $value === "0") {
            $value = intval($value);
        } else {
            $value = NULL;
        }
    } else if ($datatype == 'float') {
        if ($value || $value === 0) {
            $value = floatval($value);
        } else {
            $value = NULL;
        }
    } else if ($datatype == 'double') {
        if ($value || $value === 0) {
            $value = doubleval($value);
        } else {
            $value = NULL;
        }
    } else if ($datatype == 'url') {
        $value = prep_url($value);
    }

    //echo "AFTER: ".$value." DATATYPE: ".$datatype."\n";
    return $value;
}


/* Object Validation */

function validate_admin() {
    if(!get_user_type_id()==USER_TYPE_ADMIN) {
        json_error('You are not authorized to perform this action.', null, 403);
    }
}

/**
 * Validates that:
 *   - a project exists with the specified uuid
 *   - the project isn't deleted
 *   - The currently logged in user is a member of that project
 * @param string $uuid
 * @return mixed
 */
function validate_project_uuid($uuid = '', $validate_own = false)
{
    $CI =& get_instance();
    $CI->load->model('Project');
    if (!$uuid) {
        json_error('uuid is required');
        exit;
    }
    $project = $CI->Project->load_by_uuid($uuid);
    if (!$project || $project->deleted) {
        json_error('There is no project with that id');
        exit;
    }
    /* Validate that the user is on the project */
    if (!$CI->User->is_on_project($project->id, get_user_id())) {
        json_error('You are not authorized to access this project.', null, 403);
        exit;
    }
    /* Validate that the user is the message sender */
    if ($validate_own && get_user_id() != $project->creator_id) {
        json_error('Only the project owner can perform that action.', null, 403);
        exit;
    }

    return $project;
}

/**
 * Validates that:
 *  - a screen exists with that specified uuid
 *  - the screen isn't deleted
 *  - the screen's project is one that the user belongs to
 * @param string $uuid
 * @return mixed
 */
function validate_screen_uuid($uuid = '')
{
    $CI =& get_instance();
    $CI->load->model('Screen');
    if (!$uuid) {
        json_error('uuid is required');
        exit;
    }
    $screen = $CI->Screen->load_by_uuid($uuid);
    if (!$screen || $screen->deleted) {
        json_error('There is no screen with that id');
        exit;
    }
    /* Validate that the user is on the project that the screen belongs to */
    if (!$CI->User->is_on_project($screen->project_id, get_user_id())) {
        json_error('You are not authorized to access this project.', null, 403);
        exit;
    }

    return $screen;
}

/**
 * Validates that:
 *  - a video exists with that specified uuid
 *  - the video isn't deleted
 *  - the video's project is one that the user belongs to
 * @param string $uuid
 * @return mixed
 */
function validate_video_uuid($uuid = '')
{
    $CI =& get_instance();
    $CI->load->model('Video');
    if (!$uuid) {
        json_error('uuid is required');
        exit;
    }
    $video = $CI->Video->load_by_uuid($uuid);
    if (!$video || $video->deleted) {
        json_error('There is no video with that id');
        exit;
    }
    /* Validate that the user is on the project that the video belongs to */
    if (!$CI->User->is_on_project($video->project_id, get_user_id())) {
        json_error('You are not authorized to access this project.', null, 403);
        exit;
    }

    return $video;
}

/**
 * Validates that:
 *  - a screen exists with that specified uuid
 *  - the screen isn't deleted
 *  - the screen's project is one that the user belongs to
 * @param string $uuid
 * @return mixed
 */
function validate_hotspot_uuid($uuid = '')
{
    $CI =& get_instance();
    $CI->load->model(array('Hotspot', 'Screen'));
    if (!$uuid) {
        json_error('uuid is required');
        exit;
    }
    $hotspot = $CI->Hotspot->load_by_uuid($uuid);
    if (!$hotspot || $hotspot->deleted) {
        json_error('There is no hotspot with that id');
        exit;
    }
    $screen = $CI->Screen->load($hotspot->screen_id);
    /* Validate that the user is on the project that the screen belongs to */
    if (!$CI->User->is_on_project($screen->project_id, get_user_id())) {
        json_error('You are not authorized to access this project.', null, 403);
        exit;
    }

    return $hotspot;
}


/**
 * Validates that:
 *   - a team exists with the specified uuid
 *   - the team isn't deleted
 *   - The currently logged in user is a member of that team
 * @param string $uuid
 * @return mixed
 */
function validate_team_uuid($uuid = '', $validate_own = false)
{
    $CI =& get_instance();
    $CI->load->model('Team');
    if (!$uuid) {
        json_error('uuid is required');
        exit;
    }
    $team = $CI->Team->load_by_uuid($uuid);
    if (!$team || $team->deleted) {
        json_error('There is no active team with that id');
        exit;
    }
    /* Validate that the user is on the project */
    if (!$CI->User->is_on_team($team->id, get_user_id())) {
        json_error('You are not authorized to view this team.', null, 403);
        exit;
    }
    /* Validate that the user is the message sender */
    if ($validate_own && get_user_id() != $team->owner_id) {
        json_error('Only the team owner can perform that action.', null, 403);
        exit;
    }

    return $team;
}

function validate_team_owner($team_id = 0, $user_id)
{
    $CI =& get_instance();
    $CI->load->model('Team');
    $team = $CI->Team->load_fields($team_id, 'owner_id');
    if ($team->owner_id == $user_id) {
        return true;
    }

    json_error('Only the team owner can perform this action.', null, 403);
    exit;
}

/**
 * Validates that the user can read from the team based on either being part of a free trial
 * or being a member of a team that is owned by someone else.
 *
 * Looks at the creator of the team and sees if they are a free trial user or have a valid
 * subscription
 */
function validate_team_read($team_id = 0)
{
    $CI =& get_instance();
    $CI->load->model(array('Team', 'Subscription'));
    $team = $CI->Team->load_fields($team_id, 'owner_id');

    /* If the team owner has a valid subscription, return true */
    $subscription = $CI->Subscription->load_by_field('user_id', $team->owner_id);
    if ($subscription && !$subscription->failed) {
        return true;
    }

    $owner = $CI->User->load_fields($team->owner_id, 'created');

    /* See if their free trial has expired */
    $expiration = add_day(FREE_TRIAL_LENGTH, $owner->created);
    if ($expiration > now()) {
        return true;
    }

    if (get_user_id() == $team->owner_id) {
        json_error('Free trial has expired.', null, 403);
        exit;
    } else {
        json_error('The owner of this team does not have a valid subscription', null, 403);
        exit;
    }
}

/**
 * Validates that the person can add a project to their account
 * - A free trial user can only add one project
 * - A user with a subscription can only add up to the number of projects that are available in their plan
 * @param $user_id
 */
function validate_project_add($user_id)
{
    $CI =& get_instance();
    $CI->load->model(array('Team', 'Subscription', 'Project', 'Plan'));
    $subscription = $CI->Subscription->load_by_field('user_id', $user_id);
    $user = $CI->User->load_fields($user_id, 'created');
    if ($subscription) {
        $plan = $CI->Plan->load($subscription->plan_id);
        $projects = $CI->Project->get_owned_by_user($user_id);

        if(sizeof($projects)>=$plan->projects) {
            json_error(sprintf('You cannot create anymore projects.  Your plan allows you to create up to %d projects.',
                $plan->projects), null, 403);
            exit;
        }
    } /* Otherwise, they are still in a free trial */
    else {
        $expiration = add_day(FREE_TRIAL_LENGTH, $user->created);
        if ($expiration < now()) {
            json_error('Free Trial Expired', null, 403);
            exit;
        } else {
            $projects = $CI->Project->get_owned_by_user($user_id);
            if(sizeof($projects)>=FREE_TRIAL_PROJECTS) {
                json_error('You cannot create anymore projects during your free trial.', null, 403);
                exit;
            }
        }
    }
}

/**
 * Validates that a user can invite/add users to their projects
 * @param $user_id
 */
function validate_user_add($user_id) {
    $CI =& get_instance();
    $CI->load->model(array('Team', 'Subscription', 'Project', 'Plan'));
    $subscription = $CI->Subscription->load_by_field('user_id', $user_id);
    $user = $CI->User->load_fields($user_id, 'created');
    if ($subscription) {
        $plan = $CI->Plan->load($subscription->plan_id);
        $users = $CI->User->get_for_teams_owner($user_id);
        $max_users = $plan->team_members + $subscription->additional_users;

        if(sizeof($users)>=$max_users) {
            if($user_id == get_user_id()) {
                json_error(sprintf('You cannot invite any more users to your team.  Your plan allows you to invite up to %d users.',
                    $max_users), null, 403);
            } else {
                json_error('You cannot accept this invite since the team owner does not have room for any more users on their plan.',
                    null, 403);
            }
            exit;
        }

    } /* Otherwise, they are still in a free trial */
    else {
        $expiration = add_day(FREE_TRIAL_LENGTH, $user->created);
        if ($expiration < now()) {
            json_error('Free Trial Expired', null, 403);
            exit;
        } else {
            $users = $CI->User->get_for_teams_owner($user_id);
            //array_print($users);
            if(sizeof($users)>=FREE_TRIAL_USERS) {

                if($user_id == get_user_id()) {
                    json_error('You cannot invite any more users during your free trial.', null, 403);
                } else {
                    json_error('You cannot accept this invite since the team owner does not have room for any more users on their plan.',
                        null, 403);
                }
                exit;
            }
        }
    }
}


/**
 * Validates that:
 *   - a user exists with the specified uuid
 *   - the user isn't deleted
 * @param string $uuid
 * @return mixed
 */
function validate_user_uuid($uuid = '')
{
    $CI =& get_instance();
    if (!$uuid) {
        json_error('uuid is required');
        exit;
    }
    $user = $CI->User->load_by_uuid($uuid);
    if (!$user || $user->deleted) {
        json_error('There is no active user with that id');
        exit;
    }

    return $user;
}


/**
 * Validates that:
 *   - a message exists with the specified uuid
 *   - the message isn't deleted
 *   - The currently logged in user is a member of the recipients of that message
 * @param string $uuid
 * @param boolean $validate_own whether to validate that the user is the sender of the message
 * @return mixed
 */
function validate_message_uuid($uuid = '', $validate_own = false)
{
    $CI =& get_instance();
    $CI->load->model('Message');
    if (!$uuid) {
        json_error('uuid is required');
        exit;
    }
    $message = $CI->Message->load_by_uuid($uuid);
    if (!$message || $message->deleted) {
        json_error('There is no active message with that id');
        exit;
    }

    $parent_id = $message->id;
    if ($message->parent_id) {
        $parent_id = $message->parent_id;
    }
    /* Validate that the user is on the message */
    if (!$CI->User->is_on_message($parent_id, get_user_id())) {
        json_error('You are not authorized to view this message.', null, 403);
        exit;
    }
    /* Validate that the user is the message sender */
    if ($validate_own && get_user_id() != $message->sender_id) {
        json_error('Only the message sender can perform this action.', null, 403);
        exit;
    }

    return $message;
}


/**
 * Validates that:
 *   - a meeting exists with the specified uuid
 *   - the meeting isn't deleted
 *   - The currently logged in user is a member of the attendees of that meeting
 * @param string $uuid
 * @param boolean $validate_own whether to validate that the user is the creator of the meeting
 * @return mixed
 */
function validate_meeting_uuid($uuid = '', $validate_started = false, $validate_moderator = false)
{
    $CI =& get_instance();
    $CI->load->model('Meeting');
    if (!$uuid) {
        json_error('uuid is required');
        exit;
    }
    $meeting = $CI->Meeting->load_by_uuid($uuid);
    if (!$meeting || $meeting->deleted) {
        json_error('There is no active meeting with that id');
        exit;
    }

    /* Validate that the user is on the meeting */
    if (!$CI->User->is_on_meeting($meeting->id, get_user_id())) {
        json_error('You are not authorized to view this meeting.', null, 403);
        exit;
    }
    /* Validate that the meeting has started */
    if ($validate_started && !$meeting->started) {
        json_error('This meeting has not started yet.');
        exit;
    }
    /* Validate that the user is the moderator */
    if ($validate_moderator && get_user_id() != $meeting->moderator_id) {
        json_error('Only the moderator can perform this action.', null, 403);
        exit;
    }

    return $meeting;
}


/**
 * Validates that:
 *   - a invite exists
 *   - the invite isn't used
 *   - The currently logged in user is a member of that project
 * @param Invite $invite
 * @return mixed
 */
function validate_invite($invite, $user_id = 0)
{
    if (!$invite) {
        json_error('There is no active invite with that key');
        exit;
    }

    if ($invite->used && $invite->used != '0000-00-00 00:00:00') {
        json_error('The invite you are attempting to use has already been used.');
        exit;
    }

    if ($invite->user_id && $user_id) {
        if ($invite->user_id != $user_id) {
            json_error('You are trying to accept an invite that is assigned to a different user.');
            exit;
        }
    }
}

function session_clear()
{

    $CI =& get_instance();
    $CI->session->unset_userdata(SESS_USER_ID);
    $CI->session->unset_userdata(SESS_TEAM_ID);
    $CI->session->unset_userdata(SESS_SUBSCRIPTION_ID);
}

?>