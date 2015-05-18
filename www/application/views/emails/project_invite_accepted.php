<?php
include (APPPATH . '/views/emails/header.php');
$msg .=
  '<p>'.$invitee->fullname.' has successfully accepted your invite to join your '.$CI->config->item('site_title')
  .' project, \''.$project_name.'\'. They can now participate in all activity on this project.</p>';

$msg_text = $invitee->fullname.' has successfully accepted your invite to join your '.$CI->config->item('site_title')
    .' project, \''.$project_name.'\'. They can now participate in all activity on the projects assigned to this team.';

include (APPPATH . '/views/emails/invite-footer.php');
?>