<?

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

/**
 * Validates that:
 *   - a project exists with the specified uuid
 *   - the project isn't deleted
 *   - The currently logged in user is a member of that project
 * @param string $uuid
 * @return mixed
 */
function validate_project_uuid($uuid = '')
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
        json_error('You are not authorized to access this project.');
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
        json_error('You are not authorized to access this project.');
        exit;
    }

    return $screen;
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
    $CI->load->model(array('Hotspot','Screen'));
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
        json_error('You are not authorized to access this project.');
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
function validate_team_uuid($uuid = '')
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
        json_error('You are not authorized to view this team.');
        exit;
    }

    return $team;
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
        json_error('You are not authorized to view this message.');
        exit;
    }
    /* Validate that the user is on the message */
    if ($validate_own && get_user_id() != $message->sender_id) {
        json_error('You are not authorized to view this message.');
        exit;
    }

    return $message;
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

?>