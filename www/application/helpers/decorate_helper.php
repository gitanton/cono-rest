<?php
/**
 * These are a set of decorators that can be used by the REST endpoints to add and remove attributes from
 * objects after they are pulled out of the database and sent down.  Useful for doing things like removing the
 * id, password etc.. information from users and objects.
 *
 * Also useful for loading lists of related objects such as the users on a project or the replies to a message
 */
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
    $CI->load->model(array('Team'));

    $users = $CI->User->get_for_project($object->id);
    $object->users = decorate_users($users);

    if (isset($object->creator_id)) {
        $object->creator_uuid = $CI->User->get_uuid($object->creator_id);
    }
    if (isset($object->team_id)) {
        $object->team_uuid = $CI->Team->get_uuid($object->team_id);
    }

    $object->ordering = intval($object->ordering);
    $object->archived = ci_boolval($object->archived);
    $object->type_id = intval($object->type_id);

    unset($object->deleted, $object->team_id, $object->id, $object->creator_id, $object->notify);
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

function decorate_meeting($object)
{
    $CI =& get_instance();
    $CI->load->model(array('Meeting', 'Project'));

    if (isset($object->project_id)) {
        $object->project_uuid = $CI->Project->get_uuid($object->project_id);
    }

    if (isset($object->moderator_id)) {
        $object->moderator_uuid = $CI->User->get_uuid($object->moderator_id);
    }

    if (isset($object->creator_id)) {
        $object->creator_uuid = $CI->User->get_uuid($object->creator_id);
    }

    $users = $CI->User->get_for_meeting($object->id);
    $object->recipients = decorate_users($users);
    $datetime = localize_datetime($object->date, $object->time);
    $object->date = $datetime->format('Y-m-d');
    $object->time = $datetime->format('H:i');
    $object->phone = $CI->config->item('twilio_phone');

    unset($object->deleted, $object->moderator_id, $object->creator_id, $object->project_id, $object->id);
    return $object;
}

function decorate_meeting_comment($object) {
    $CI =& get_instance();
    $CI->load->model(array('Meeting_Comment'));

    if (isset($object->creator_id)) {
        $object->creator = decorate_user($CI->User->load($object->creator_id));
        $object->creator_uuid = $object->creator->uuid;
    }

    unset($object->deleted, $object->creator_id, $object->id, $object->meeting_id);
    return $object;
}

function decorate_meeting_comments($objects)
{
    $updated = array();
    foreach ($objects as $object) {
        $updated[] = decorate_meeting_comment($object);
    }
    return $updated;
}

function decorate_meeting_delta($object) {
    $CI =& get_instance();
    $CI->load->model(array('Meeting_Delta'));

    unset($object->id, $object->meeting_id);
    return $object;
}

function decorate_meeting_deltas($objects)
{
    $updated = array();
    foreach ($objects as $object) {
        $updated[] = decorate_meeting_delta($object);
    }
    return $updated;
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
    $CI->load->model(array('Screen', 'Video'));
    if (isset($object->screen_id) && $object->screen_id > 0) {
        $object->screen_uuid = $CI->Screen->get_uuid($object->screen_id);
    }
    if (isset($object->video_id) && $object->video_id > 0) {
        $object->video_uuid = $CI->Video->get_uuid($object->video_id);
    }

    if (isset($object->creator_id)) {
        $object->creator_uuid = $CI->User->get_uuid($object->creator_id);
    }
    $object->ordering = intval($object->ordering);
    $object->begin_x = isset($object->begin_x) ? intval($object->begin_x) : null;
    $object->begin_y = isset($object->begin_y) ? intval($object->begin_y) : null;
    $object->end_x = isset($object->end_x) ? intval($object->end_x) : null;
    $object->end_y = isset($object->end_y) ? intval($object->end_y) : null;
    unset($object->deleted, $object->screen_id, $object->video_id, $object->id, $object->creator_id, $object->video_i);
    return $object;
}

function decorate_activity($object)
{
    $CI =& get_instance();

    if (isset($object->creator_id)) {
        $object->creator_uuid = $CI->User->get_uuid($object->creator_id);
    }
    if (isset($object->team_id)) {
        $object->team_uuid = $CI->Team->get_uuid($object->team_id);
    }
    if (isset($object->project_id)) {
        $object->project_uuid = $CI->Project->get_uuid($object->project_id);
    }
    unset($object->deleted, $object->id, $object->creator_id, $object->team_id, $object->project_id);
    $object->activity_type_id = intval($object->activity_type_id);
    $object->object_id = intval($object->object_id);
    return $object;
}

function decorate_drawing($object)
{
    $CI =& get_instance();
    $CI->load->model(array('Video', 'Screen'));
    if (isset($object->video_id)) {
        $object->video_uuid = $CI->Video->get_uuid($object->video_id);
    }
    if (isset($object->screen_id)) {
        $object->screen_uuid = $CI->Screen->get_uuid($object->screen_id);
    }

    if (isset($object->creator_id)) {
        $object->creator_uuid = $CI->User->get_uuid($object->creator_id);
    }

    // cast the numbers to integers
    $object->ordering = intval($object->ordering);
    $object->data = (isset($object->data) && $object->data) ? $object->data : null;
    unset($object->deleted, $object->screen_id, $object->id, $object->creator_id, $object->video_id);
    return $object;
}

function decorate_comment($object)
{
    $CI =& get_instance();
    $CI->load->model(array('Video', 'Screen'));
    if (isset($object->video_id)) {
        $object->video_uuid = $CI->Video->get_uuid($object->video_id);
    }
    if (isset($object->screen_id)) {
        $object->screen_uuid = $CI->Screen->get_uuid($object->screen_id);
    }

    if (isset($object->creator_id)) {
        $object->creator_uuid = $CI->User->get_uuid($object->creator_id);
    }

    if (isset($object->assignee_id)) {
        $object->assignee_uuid = $CI->User->get_uuid($object->assignee_id);
    }

    // cast the numbers to integers
    $object->ordering = intval($object->ordering);
    $object->project_id = intval($object->project_id);
    $object->begin_x = isset($object->begin_x) ? intval($object->begin_x) : null;
    $object->begin_y = isset($object->begin_y) ? intval($object->begin_y) : null;
    $object->end_x = isset($object->end_x) ? intval($object->end_x) : null;
    $object->end_y = isset($object->end_y) ? intval($object->end_y) : null;
    $object->left_x = isset($object->left_x) ? intval($object->left_x) : null;
    $object->is_task = isset($object->is_task) ? intval($object->is_task) : null;
    $object->marker = isset($object->marker) ? intval($object->marker) : null;
    unset($object->deleted, $object->screen_id, $object->id, $object->creator_id, $object->video_id, $object->assignee_id);
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

function decorate_comments($objects)
{
    $updated = array();
    foreach ($objects as $object) {
        $updated[] = decorate_comment($object);
    }
    return $updated;
}

function decorate_drawings($objects)
{
    $updated = array();
    foreach ($objects as $object) {
        $updated[] = decorate_drawing($object);
    }
    return $updated;
}

function decorate_screen($object)
{
    $CI =& get_instance();
    $CI->load->model(array('Project', 'Hotspot', 'Comment', 'Drawing'));

    if (isset($object->project_id)) {
        $object->project_uuid = $CI->Project->get_uuid($object->project_id);
    }

    if (isset($object->creator_id)) {
        $object->creator_uuid = $CI->User->get_uuid($object->creator_id);
    }

    $object->url = file_url($object->url);

    $hospots = $CI->Hotspot->get_for_screen($object->id);
    $object->hotspots = decorate_hotspots($hospots);

    $drawings = $CI->Drawing->get_for_screen($object->id);
    $object->drawings = decorate_drawings($drawings);

    $comments = $CI->Comment->get_for_screen($object->id);
    $object->comments = decorate_comments($comments);

    $object->ordering = intval($object->ordering);
    $object->image_width = floatval($object->image_width);
    $object->image_height = floatval($object->image_height);
    $object->file_size = floatval($object->file_size);
    unset($object->deleted, $object->project_id, $object->id, $object->creator_id);
    return $object;
}

function decorate_video($object)
{
    $CI =& get_instance();
    $CI->load->model(array('Project', 'Hotspot', 'Comment', 'Drawing'));

    if (isset($object->project_id)) {
        $object->project_uuid = $CI->Project->get_uuid($object->project_id);
    }

    if (isset($object->creator_id)) {
        $object->creator_uuid = $CI->User->get_uuid($object->creator_id);
    }

    $object->url = file_url($object->url, FILE_TYPE_VIDEO);

    $hotspots = $CI->Hotspot->get_for_video($object->id);
    $object->hotspots = decorate_hotspots($hotspots);

    $drawings = $CI->Drawing->get_for_video($object->id);
    $object->drawings = decorate_drawings($drawings);

    $comments = $CI->Comment->get_for_video($object->id);
    $object->comments = decorate_comments($comments);

    $object->ordering = intval($object->ordering);
    $object->file_size = floatval($object->file_size);
    unset($object->deleted, $object->project_id, $object->id, $object->creator_id);
    return $object;
}

function decorate_template($object)
{
    $CI =& get_instance();
    $CI->load->model(array('Template'));

    if (isset($object->creator_id)) {
        $object->creator_uuid = $CI->User->get_uuid($object->creator_id);
    }

    $object->url = file_url($object->url, FILE_TYPE_TEMPLATE);

    $object->ordering = intval($object->ordering);
    $object->image_width = floatval($object->image_width);
    $object->image_height = floatval($object->image_height);
    $object->file_size = floatval($object->file_size);
    unset($object->deleted, $object->id, $object->creator_id);
    return $object;
}

/**
 * Converts an array of stripe invoice objects into usable invoice objects for the billing history
 * @param $objects
 * @return array
 */
function decorate_invoices($objects) {
    $updated = array();
    foreach ($objects as $object) {
        $updated[] = decorate_invoice($object);
    }
    return $updated;
}

/**
 * Converts a stripe invoice into a friendlier json object
 * @param $object
 */
function decorate_invoice($object) {
    unset($object->id, $object->customer, $object->livemode, $object->webhooks_delivered_at, $object->charge,
        $object->application_fee, $object->subscription, $object->attempted, $object->closed, $object->forgiven,
        $object->lines, $object->object, $object->starting_balance, $object->ending_balance, $object->next_payment_attempt,
        $object->metadata, $object->statement_descriptor, $object->description, $object->receipt_number);

    $object->subtotal = $object->subtotal / 100;
    $object->total = $object->total / 100;
    $object->amount_due = $object->amount_due / 100;
    $object->tax = $object->tax / 100;
    $object->date = timestamp_to_mysqldatetime($object->date);
    $object->period_start = timestamp_to_mysqldatetime($object->period_start);
    $object->period_end = timestamp_to_mysqldatetime($object->period_end);

    return $object->__toArray();
}

?>