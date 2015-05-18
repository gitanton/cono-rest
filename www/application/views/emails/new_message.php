<?php
include (APPPATH . '/views/emails/header.php');
$msg .=
'<p style="font-size:12px; color: #1496cf; border-bottom: 1px #ccc solid">'
.$sender->fullname.' posted a new message to the \''.$project->name.'\' project on '.$CI->config->item('site_title').'.</p>'
.'<p>'.nl2br($message->content).'</p>'
.'<p style="font-size:11px; color:#bbbbbb">This message has been sent to: '.$recipient_names.'</p>';

$msg_text = $sender->fullname." posted a new message to the '".$project->name."' project on ".$CI->config->item('site_title').".\n\n"
.$message->content."\n\n"
."This message has been sent to: ".$recipient_names;

include (APPPATH . '/views/emails/invite-footer.php');
?>