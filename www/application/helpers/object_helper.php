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
    unset($user->notify_general);
    unset($user->notify_promotions);

    if(isset($user->avatar)) {
        $user->avatar = file_url($user->avatar);
    } else {
        $user->avatar = get_gravatar($user->email, IMG_SIZE_LG);
    }

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

function session_clear()
{

    $CI =& get_instance();
    $CI->session->unset_userdata(SESS_USER_ID);
    $CI->session->unset_userdata(SESS_TEAM_ID);
    $CI->session->unset_userdata(SESS_SUBSCRIPTION_ID);
}

?>