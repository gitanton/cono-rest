<?php
include (APPPATH . '/views/emails/header.php');
$msg .=
  '<p>You are receiving this email because we have successfully cancelled your subscription.  Your card will no longer be charged.</p>
   <p>Thank you for your business.</p>';

$msg_text = 'You are receiving this email because we have successfully cancelled your subscription.  Your card will no longer be charged.
   Thank you for your business.';

include (APPPATH . '/views/emails/footer.php');
?>