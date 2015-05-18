<?php
include (APPPATH . '/views/emails/header.php');
$msg .=
'<p style="font-size:12px; color: #1496cf; border-bottom: 1px #ccc solid">'
.$sender->fullname.' has invited you to a new meeting for the \''.$project->name.'\' project on '.$CI->config->item('site_title').'.</p>'
.'<p><strong>Meeting Name:</strong> '.$meeting->name.'<br/>'
.'<strong>Description:</strong> '.$meeting->notes.'<br/>'
.'<strong>Date:</strong> %recipient.date%<br/>'
.'<strong>Time:</strong> %recipient.time%<br/>'
.'<strong>URL:</strong> '.auto_link($meeting->share_link).'<br/>'
.'</p>
<p style="font-size:11px; color:#bbbbbb">This message has been sent to: '.$recipient_names.'</p>';

$msg_text = $sender->fullname." has invited you to a new meeting for the '".$project->name."' project on ".$CI->config->item('site_title').".\n\n"
."<p><strong>Meeting Name:</strong> ".$meeting->name."\n"
."<strong>Description:</strong> ".$meeting->notes."\n"
."<strong>Date:</strong> %recipient.date%\n"
."<strong>Time:</strong> %recipient.time%\n"
."<strong>URL:</strong> ".$meeting->share_link."\n"
."This message has been sent to: ".$recipient_names;

include (APPPATH . '/views/emails/invite-footer.php');
?>