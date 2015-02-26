<?
include(APPPATH.'/views/emails/header.php');
$msg .=
    '<p>You have successfully created a new account with '.$CI->config->item('site_title').'. Go ahead and login when you get some time.</p>
  <p><strong>Login URL</strong>: <a style="color: #1496cf" href="'.$CI->config->item('signin_url').'">'.$CI->config->item('signin_url').'</a><br/>
  <strong>Email Address</strong>: <a style="color: #1496cf" href="mailto:'.$user->email.'">'.$user->email.'</a><br/>
  <strong>Username</strong>: '.$user->username.'<br/>
  <strong>Password</strong>: '.$password.'</p>';

$msg_text =
    "You have been assigned a new account with ".$CI->config->item('site_title').". Go ahead and login when you get some time.\n
Login URL: ".$CI->config->item('signin_url')."
Email Address: ".$user->email."
Username: ".$user->username."
Password: ".$password;

include(APPPATH.'/views/emails/footer.php');
?>