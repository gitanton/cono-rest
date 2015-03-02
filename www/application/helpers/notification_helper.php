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
 * Sent when a user accepts a team invite
 * @param $invite_id
 */
function notify_team_invite_accepted($invite_id) {

    if ($invite_id > 0) {
        $CI = & get_instance();
        $CI->load->model('Team_Invite');

        $invite = $CI->Team_Invite->load($invite_id);
        $user = $CI->User->load($invite->creator_id);
        $invitee = $CI->User->load($invite->user_id);

        $subject = $invitee->fullname." has accepted your invitation on ".$CI->config->item('site_title');
        include(APPPATH . '/views/emails/team_invite_accepted.php');

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

        $log_text = sprintf('[Notify Team Invite Accepted] Sending message: [%s] to %s', $subject, $invite->email);
        log_message('info', $log_text);
        loggly(array(
            'text' => $log_text,
            'method' => 'notification_helper.notify_team_invite_accepted',
            'team_invite' => $invite,
            'user_id' => $user->id,
            'invitee_id' => $invitee->id,
            'response' => $response
        ));
    }
}

/**
 * Sent when a user accepts a project invite
 * @param $invite_id
 */
function notify_project_invite_accepted($invite_id) {

    if ($invite_id > 0) {
        $CI = & get_instance();
        $CI->load->model(array('Project','Project_Invite'));

        $invite = $CI->Project_Invite->load($invite_id);
        $user = $CI->User->load($invite->creator_id);
        $invitee = $CI->User->load($invite->user_id);
        $project = $CI->Project->load($invite->project_id);
        $project_name = $project->name;

        $subject = $invitee->fullname." has accepted your invitation on ".$CI->config->item('site_title');
        include(APPPATH . '/views/emails/project_invite_accepted.php');

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

        $log_text = sprintf('[Notify Project Invite Accepted] Sending message: [%s] to %s', $subject, $invite->email);
        log_message('info', $log_text);
        loggly(array(
            'text' => $log_text,
            'method' => 'notification_helper.notify_project_invite_accepted',
            'team_invite' => $invite,
            'user_id' => $user->id,
            'invitee_id' => $invitee->id,
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

        $subject = "Welcome to ".$CI->config->item('site_title');
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

/** Send the contents of a new message to users on the project */
function notify_new_message($message_id, $sender_id, $parent_id=0) {
    $CI = & get_instance();
    $CI->load->model(array('Project', 'Message'));

    $message = $CI->Message->load($message_id);
    $project = $CI->Project->load($message->project_id);
    $subject = sprintf('(%s) New Message', $project->name);

    if($parent_id) {
        $recipients = $CI->User->get_for_message($parent_id);
    } else {
        $recipients = $CI->User->get_for_message($message->id);
    }
    $recipient_names = '';

    $i=0;
    foreach($recipients as $recipient) {
        if($i>0) {
            $recipient_names.=",";
        }
        $recipient_names.=" ".$recipient->fullname;
        $i++;
    }
    $sender = $CI->User->load($sender_id);

    include(APPPATH . '/views/emails/new_message.php');

    $mg = new Mailgun($CI->config->item('mailgun_key'));
    $batchMsg = $mg->BatchMessage($CI->config->item('mailgun_domain'));
    $batchMsg->setFromAddress($CI->config->item('notifications_email_from') . ' <' . $CI->config->item('notifications_email') . '>');
    $batchMsg->setSubject($subject);
    $batchMsg->setTextBody($msg_text);
    $batchMsg->setHtmlBody($msg);
    $batchMsg->setClickTracking(true);
    $batchMsg->setOpenTracking(true);

    foreach($recipients as $recipient) {
        if($recipient->id!=$sender_id) {
            $batchMsg->addToRecipient($recipient->email, array("fullname" => $recipient->fullname));
        }
    }

    $batchMsg->finalize();

    loggly(array(
        'text' => 'Sending notification of new message',
        'method' => 'notification_helper.notify_new_message',
        'sender_id' => $sender_id,
        'messgae_id' => $message_id
    ));
}

/** Send the contents of a new message to users on the project */
function notify_new_meeting($meeting_id, $sender_id) {
    $CI = & get_instance();
    $CI->load->model(array('Project', 'Meeting'));

    $meeting = $CI->Meeting->load($meeting_id);
    $project = $CI->Project->load($meeting->project_id);
    $subject = sprintf('(%s) New Meeting Scheduled', $project->name);

    $attendees = $CI->User->get_for_meeting($meeting_id);
    $recipient_names = '';

    $i=0;
    foreach($attendees as $recipient) {
        if($i>0) {
            $recipient_names.=",";
        }
        $recipient_names.=" ".$recipient->fullname;
        $i++;
    }
    $sender = $CI->User->load($sender_id);

    include(APPPATH . '/views/emails/new_meeting.php');

    $mg = new Mailgun($CI->config->item('mailgun_key'));
    $batchMsg = $mg->BatchMessage($CI->config->item('mailgun_domain'));
    $batchMsg->setFromAddress($CI->config->item('notifications_email_from') . ' <' . $CI->config->item('notifications_email') . '>');
    $batchMsg->setSubject($subject);
    $batchMsg->setTextBody($msg_text);
    $batchMsg->setHtmlBody($msg);
    $batchMsg->setClickTracking(true);
    $batchMsg->setOpenTracking(true);

    foreach($attendees as $recipient) {
        $datetime = localize_datetime($meeting->date, $meeting->time, $recipient);
        $batchMsg->addToRecipient($recipient->email, array(
            "time" => $datetime->format('h:i A'),
            "date" => $datetime->format('F j, Y')
        ));
    }

    $batchMsg->finalize();

    loggly(array(
        'text' => 'Sending notification of new meeting',
        'method' => 'notification_helper.notify_new_meeting',
        'sender_id' => $sender_id,
        'meeting_id' => $meeting_id
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

function notify_subscription_cancelled($user) {
    $CI = & get_instance();

    include(APPPATH . '/views/emails/subscription_cancelled.php');

    $subject = sprintf('[%s] Subscription Cancelled', $CI->config->item('site_title'));

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

    $log_text = sprintf('[Subscription Cancelled] Sending notice: [%s] to %s', $subject, $user->email);
    log_message('info', $log_text);
    loggly(array(
        'text' => $log_text,
        'method' => 'notification_helper.notify_subscription_cancelled',
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