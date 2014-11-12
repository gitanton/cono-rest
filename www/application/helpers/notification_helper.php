<?
/**
 * Send an email to a person who is being invited to a team
 * @param $user_id
 * @param $password
 */
function notify_team_invite($invite_id, $inviter_id)
{
    if ($invite_id > 0) {
        $CI = & get_instance();

        setup_notification_email();
        $user = $CI->User->load($inviter_id);
        $invite = $CI->Team_Invite->load($invite_id);

        $CI->email->clear();
        $subject = "You have been invited to join ".$CI->config->item('site_title');
        include(APPPATH . '/views/emails/team_invite.php');
        $CI->email->subject($subject);
        $CI->email->message($msg);

        $CI->email->from($CI->config->item('notifications_email'), $CI->config->item('site_title'));
        $CI->email->to($user->email);

        process_notification_send();
        $log_text = sprintf('[Notify Team Invite] Sending message: [%s] to %s', $subject, $invite->email);
        log_message('info', $log_text);
        loggly(array(
            'text' => $log_text,
            'method' => 'notification_helper.notify_team_invite',
            'team_invite' => $invite,
            'user_id' => $inviter_id
        ));
    }
}

/**
 * Send an email to a person who is being invited to a project
 * @param $user_id
 * @param $password
 */
function notify_project_invite_new_user($invite_id, $inviter_id)
{
    if ($invite_id > 0) {
        $CI = & get_instance();

        setup_notification_email();
        $user = $CI->User->load($inviter_id);
        $invite = $CI->Project_Invite->load($invite_id);

        $CI->email->clear();
        $subject = "You have been invited to join a project on ".$CI->config->item('site_title');
        include(APPPATH . '/views/emails/project_invite.php');
        $CI->email->subject($subject);
        $CI->email->message($msg);

        $CI->email->from($CI->config->item('notifications_email'), $CI->config->item('site_title'));
        $CI->email->to($user->email);

        process_notification_send();
        $log_text = sprintf('[Notify Project Invite New User] Sending message: [%s] to %s', $subject, $invite->email);
        log_message('info', $log_text);
        loggly(array(
            'text' => $log_text,
            'method' => 'notification_helper.notify_project_invite_new',
            'team_invite' => $invite,
            'user_id' => $inviter_id
        ));
    }
}

/**
 * Send an email to the new user who has just been created
 * @param $user_id
 * @param $password
 */
function notify_new_user($user_id, $password)
{
    if ($user_id > 0) {
        $CI = & get_instance();

        setup_notification_email();
        $user = $CI->User->load($user_id);

        $CI->email->clear();
        $subject = "Welcome to the ".$CI->config->item('site_title');
        include(APPPATH . '/views/emails/new_user.php');
        $CI->email->subject($subject);
        $CI->email->message($msg);

        $CI->email->from($CI->config->item('notifications_email'), $CI->config->item('site_title'));
        $CI->email->to($user->email);

        process_notification_send();
        $log_text = sprintf('[Notify New User] Sending message: [%s] to %s', $subject, $user->email);
        log_message('info', $log_text);
        loggly(array(
            'text' => $log_text,
            'method' => 'notification_helper.notify_new_user',
            'user_id' => $user_id
        ));
    }
}

function notify_reset_password($user, $password) {
    $CI = & get_instance();

    setup_notification_email();
    include(APPPATH . '/views/emails/reset_password.php');

    $subject = sprintf('[%s] Password Reset', $CI->config->item('site_title'));

    $CI->email->subject($subject);
    $CI->email->message($msg);

    $CI->email->from($CI->config->item('notifications_email'), $CI->config->item('site_title'));
    $CI->email->to($user->email);
    $CI->email->send();

    $log_text = sprintf('[Notify Password Reset] Sending new password: [%s] to %s', $subject, $user->email);
    log_message('info', $log_text);
    loggly(array(
        'text' => $log_text,
        'method' => 'notification_helper.notify_reset_password',
        'user' => $user
    ));
}

function process_notification_send() {
    $CI = & get_instance();
    if(!$CI->email->send()) {
        loggly(array(
            'text' => 'Error sending notification',
            'error' => $CI->email->print_debugger()
        ));
    }
}

function setup_notification_email()
{
    $CI = & get_instance();

    $config = array();
    //$config['protocol'] = 'smtp';
    $config['mailtype'] = 'html';
    //$config['smtp_host'] = $CI->config->item('smtp_host');
    //$config['smtp_port'] = $CI->config->item('smtp_port');
    //$config['smtp_user'] = $CI->config->item('notifications_user');
    //$config['smtp_pass'] = $CI->config->item('notifications_password');
    //$config['smtp_timeout'] = 5;
    $config['charset'] = 'iso-8859-1';
    $config['wordwrap'] = TRUE;

    $CI->load->library('email', $config);
    $CI->email->set_newline("\r\n");
}

?>