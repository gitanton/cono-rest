<?
include (APPPATH . '/views/emails/header.php');
$msg .=
  '<p>'.$user->fullname.' has invited you to join a '.$CI->config->item('site_title').' team. To accept the invitation, click the "Accept Invitation" link below.  If you do not have an account, you will be prompted to create one.</p>
  <p><a style="color:#0370a2" href="'.$CI->config->item('webapp_url').'#/invitation/?invite='.$invite->key.'&type=team">Accept Invitation</a></p>';
include (APPPATH . '/views/emails/invite-footer.php');
?>