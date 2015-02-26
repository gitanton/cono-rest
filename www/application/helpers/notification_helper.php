<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/rest/vendor/autoload.php');
use Mailgun\Mailgun;
/**
 * Send an email to a person who is being invited to a team
 * @param $user_id
 * @param $password
 */
function notify_team_invite($invite_id, $inviter_id)
{
    if ($invite_id > 0) {
        $CI = & get_instance();

        $user = $CI->User->load($inviter_id);
        $invite = $CI->Team_Invite->load($invite_id);

        $subject = "You have been invited to join ".$CI->config->item('site_title');
        include(APPPATH . '/views/emails/team_invite.php');

        $mg = new Mailgun($CI->config->item('mailgun_key'));
        $response = $mg->sendMessage($CI->config->item('mailgun_domain'),
            array('from' => $CI->config->item('notifications_email_from') . ' <' . $CI->config->item('notifications_email') . '>',
                'to' => $invite->email,
                'subject' => $subject,
                'html' => $msg,
                'text' => $msg_text,
                'o:tracking' => 'yes',
                'o:tracking-clicks' => 'yes',
                'o:tracking-opens' => 'yes'));

        $log_text = sprintf('[Notify Team Invite] Sending message: [%s] to %s', $subject, $invite->email);
        log_message('info', $log_text);
        loggly(array(
            'text' => $log_text,
            'method' => 'notification_helper.notify_team_invite',
            'team_invite' => $invite,
            'user_id' => $inviter_id,
            'response' => $response
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

        $user = $CI->User->load($inviter_id);
        $invite = $CI->Project_Invite->load($invite_id);

        $email = $invite->email;
        if(!$email) {
            $invitee = $CI->User->load($invite->user_id);
            $email = $invitee->email;
        }

        $subject = "You have been invited to join a project on ".$CI->config->item('site_title');
        include(APPPATH . '/views/emails/project_invite.php');

        $mg = new Mailgun($CI->config->item('mailgun_key'));
        $response = $mg->sendMessage($CI->config->item('mailgun_domain'),
            array('from' => $CI->config->item('notifications_email_from') . ' <' . $CI->config->item('notifications_email') . '>',
                'to' => $email,
                'subject' => $subject,
                'html' => $msg,
                'text' => $msg_text,
                'o:tracking' => 'yes',
                'o:tracking-clicks' => 'yes',
                'o:tracking-opens' => 'yes'));

        $log_text = sprintf('[Notify Project Invite New User] Sending message: [%s] to %s', $subject, $email);
        log_message('info', $log_text);
        loggly(array(
            'text' => $log_text,
            'method' => 'notification_helper.notify_project_invite_new',
            'project_invite' => $invite,
            'email' => $email,
            'user_id' => $inviter_id,
            'response' => $response
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

        $user = $CI->User->load($user_id);

        $subject = "Welcome to the ".$CI->config->item('site_title');
        include(APPPATH . '/views/emails/new_user.php');

        $mg = new Mailgun($CI->config->item('mailgun_key'));
        $response = $mg->sendMessage($CI->config->item('mailgun_domain'),
            array('from' => $CI->config->item('notifications_email_from') . ' <' . $CI->config->item('notifications_email') . '>',
                'to' => $user->email,
                'subject' => $subject,
                'html' => $msg,
                'text' => $msg_text,
                'o:tracking' => 'yes',
                'o:tracking-clicks' => 'yes',
                'o:tracking-opens' => 'yes'));

        $log_text = sprintf('[Notify New User] Sending message: [%s] to %s', $subject, $user->email);
        log_message('info', $log_text);
        loggly(array(
            'text' => $log_text,
            'method' => 'notification_helper.notify_new_user',
            'user_id' => $user_id,
            'response' => $response
        ));
    }
}

function notify_reset_password($user, $password) {
    $CI = & get_instance();

    include(APPPATH . '/views/emails/reset_password.php');

    $subject = sprintf('[%s] Password Reset', $CI->config->item('site_title'));

    $mg = new Mailgun($CI->config->item('mailgun_key'));
    $response = $mg->sendMessage($CI->config->item('mailgun_domain'),
        array('from' => $CI->config->item('notifications_email_from') . ' <' . $CI->config->item('notifications_email') . '>',
            'to' => $user->email,
            'subject' => $subject,
            'html' => $msg,
            'text' => $msg_text,
            'o:tracking' => 'yes',
            'o:tracking-clicks' => 'yes',
            'o:tracking-opens' => 'yes'));

    $log_text = sprintf('[Notify Password Reset] Sending new password: [%s] to %s', $subject, $user->email);
    log_message('info', $log_text);
    loggly(array(
        'text' => $log_text,
        'method' => 'notification_helper.notify_reset_password',
        'user' => $user,
        'response' => $response
    ));
}

function notify_failed_charge($user, $card_last_four) {
    $CI = & get_instance();

    include(APPPATH . '/views/emails/failed_charge.php');

    $subject = sprintf('[%s] Subscription Payment Failure', $CI->config->item('site_title'));

    $mg = new Mailgun($CI->config->item('mailgun_key'));
    $response = $mg->sendMessage($CI->config->item('mailgun_domain'),
        array('from' => $CI->config->item('notifications_email_from') . ' <' . $CI->config->item('notifications_email') . '>',
            'to' => $user->email,
            'subject' => $subject,
            'html' => $msg,
            'text' => $msg_text,
            'o:tracking' => 'yes',
            'o:tracking-clicks' => 'yes',
            'o:tracking-opens' => 'yes'));

    $log_text = sprintf('[Subscription Payment Failure] Sending notice: [%s] to %s', $subject, $user->email);
    log_message('info', $log_text);
    loggly(array(
        'text' => $log_text,
        'method' => 'notification_helper.notify_failed_charge',
        'user' => $user,
        'response' => $response
    ));
}

function notify_successful_charge($user, $card_last_four='', $amount='') {
    $CI = & get_instance();

    include(APPPATH . '/views/emails/successful_charge.php');

    $subject = sprintf('[%s] Subscription Receipt', $CI->config->item('site_title'));

    $mg = new Mailgun($CI->config->item('mailgun_key'));
    $response = $mg->sendMessage($CI->config->item('mailgun_domain'),
        array('from' => $CI->config->item('notifications_email_from') . ' <' . $CI->config->item('notifications_email') . '>',
            'to' => $user->email,
            'subject' => $subject,
            'html' => $msg,
            'text' => $msg_text,
            'o:tracking' => 'yes',
            'o:tracking-clicks' => 'yes',
            'o:tracking-opens' => 'yes'));

    $log_text = sprintf('[Subscription Payment Successful] Sending notice: [%s] to %s', $subject, $user->email);
    log_message('info', $log_text);
    loggly(array(
        'text' => $log_text,
        'method' => 'notification_helper.notify_failed_charge',
        'user' => $user,
        'response' => $response
    ));
}

?>