<?
include (APPPATH . '/views/emails/header.php');
$msg .=
  '<p>You are receiving this email because we were unable to charge your card with the last four digits of '.$card_last_four
   . ' to renew your subscription.  Please login to your account at your earliest convenience and update your account.</p>
   <p>Thank you.</p>';

$msg_text = 'You are receiving this email because we were unable to charge your card with the last four digits of: '.$card_last_four
    . ' to renew your subscription.  Please login to your account at your earliest convenience and update your account.
   Thank you.';

include (APPPATH . '/views/emails/footer.php');
?>