<?php
include (APPPATH . '/views/emails/header.php');
$msg .=
  '<p>'.$user->fullname.' has invited you to join a '.$CI->config->item('site_title').' team. To accept the invitation, click the "Accept Invitation" link below.  If you do not have an account, you will be prompted to create one.</p>
  <p><a style="color:#1496cf" href="'.$CI->config->item('webapp_url').'#/invitation/'.$invite->key.'/'.INVITE_TYPE_TEAM.'">Accept Invitation</a></p>';

$msg_text = $user->fullname." has invited you to join a ".$CI->config->item('site_title')
    ." team. To accept the invitation, click the link below.  If you do not have an account, you will be prompted to create one.\n"
    .$CI->config->item('webapp_url').'#/invitation/'.$invite->key.'/'.INVITE_TYPE_TEAM;

include (APPPATH . '/views/emails/invite-footer.php');
?>