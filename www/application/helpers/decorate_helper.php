<?

function decorate_hotspot($object) {
    $CI =& get_instance();
    $CI->load->model(array('Screen'));
    if(isset($object->screen_id)) {
        $object->screen_uuid = $CI->Screen->get_uuid($object->screen_id);
    }
    unset($object->deleted, $object->screen_id);
    return $object;
}

function decorate_hotspots($objects)
{
    $updated = array();
    foreach ($objects as $object) {
        $updated[] =  decorate_hotspot($object);
    }
    return $updated;
}

function decorate_screen($object) {
    $CI =& get_instance();
    $CI->load->model(array('Project', 'Hotspot'));

    if(isset($object->project_id)) {
        $object->project_uuid = $CI->Project->get_uuid($object->project_id);
    }
    unset($object->deleted, $object->project_id);

    $object->url = file_url($object->url);

    $hospots = $CI->Hotspot->get_for_screen($object->id);
    $object->hotspots = decorate_hotspots($hospots);
    return $object;
}
?>