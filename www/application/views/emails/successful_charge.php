<?php
include (APPPATH . '/views/emails/header.php');
$msg .=
  '<p>You are receiving this email because we successfully charged your card with the last four digits of '.$card_last_four
   . ' to renew your subscription.</p><p>Your card was charged for the amount of '.dollarfy($amount).'.
   <p>Thank you for your payment.</p>';

$msg_text = 'You are receiving this email because we successfully charged your card with the last four digits of '.$card_last_four
    . ' to renew your subscription.
Your card was charged for the amount of '.dollarfy($amount).'.
Thank you for your payment.';

include (APPPATH . '/views/emails/footer.php');
?>