<?
function decorate_user($object)
{
    return clean_user($object);
}

function decorate_users($objects)
{
    $updated = array();
    foreach ($objects as $object) {
        $updated[] = decorate_user($object);
    }
    return $updated;
}

function decorate_team($object)
{
    $CI =& get_instance();

    if (isset($object->owner_id)) {
        $object->owner_uuid = $CI->User->get_uuid($object->owner_id);
    }
    $users = $CI->User->get_for_team($object->id);
    $object->users = decorate_users($users);
    unset($object->deleted, $object->id, $object->owner_id);
    return $object;
}

function decorate_project($object)
{
    $CI =& get_instance();

    $users = $CI->User->get_for_project($object->id);
    $object->users = decorate_users($users);

    if (isset($object->creator_id)) {
        $object->creator_uuid = $CI->User->get_uuid($object->creator_id);
    }

    unset($object->deleted, $object->team_id, $object->id, $object->creator_id);
    return $object;
}

function decorate_message($object, $ignore_replies = false)
{
    $CI =& get_instance();
    $CI->load->model(array('Message', 'Project'));

    if (isset($object->project_id)) {
        $object->project_uuid = $CI->Project->get_uuid($object->project_id);
    }
    if (isset($object->parent_id) && $object->parent_id) {
        $object->parent_uuid = $CI->Message->get_uuid($object->parent_id);
    }

    if (isset($object->sender_id)) {
        $object->sender_uuid = $CI->User->get_uuid($object->sender_id);
    }
    if (!$ignore_replies) {
        $users = $CI->User->get_for_message($object->id);
        $object->recipients = decorate_users($users);
        $replies = $CI->Message->get_replies($object->id);
        $object->replies = decorate_messages($replies, true);
    }
    unset($object->deleted, $object->project_id, $object->parent_id, $object->id, $object->sender_id);
    return $object;
}

function decorate_messages($objects, $ignore_replies = false)
{
    $updated = array();
    foreach ($objects as $object) {
        $updated[] = decorate_message($object, $ignore_replies);
    }
    return $updated;
}

function decorate_hotspot($object)
{
    $CI =& get_instance();
    $CI->load->model(array('Screen'));
    if (isset($object->screen_id)) {
        $object->screen_uuid = $CI->Screen->get_uuid($object->screen_id);
    }

    if (isset($object->creator_id)) {
        $object->creator_uuid = $CI->User->get_uuid($object->creator_id);
    }
    unset($object->deleted, $object->screen_id, $object->id, $object->creator_id);
    return $object;
}

function decorate_hotspots($objects)
{
    $updated = array();
    foreach ($objects as $object) {
        $updated[] = decorate_hotspot($object);
    }
    return $updated;
}

function decorate_screen($object)
{
    $CI =& get_instance();
    $CI->load->model(array('Project', 'Hotspot'));

    if (isset($object->project_id)) {
        $object->project_uuid = $CI->Project->get_uuid($object->project_id);
    }

    if (isset($object->creator_id)) {
        $object->creator_uuid = $CI->User->get_uuid($object->creator_id);
    }

    $object->url = file_url($object->url);

    $hospots = $CI->Hotspot->get_for_screen($object->id);
    $object->hotspots = decorate_hotspots($hospots);

    unset($object->deleted, $object->project_id, $object->id, $object->creator_id);
    return $object;
}

?>