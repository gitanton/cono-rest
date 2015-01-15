<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<Response>
    <Gather action="<?=site_url('twilio/voice/pin')?>" method="POST" numDigits="7" timeout="5" finishOnKey="#">
        <Say>Please enter the seven digit PIN for this meeting.  Press the pound key when finished.</Say>
    </Gather>
    <!-- If caller doesn't input anything, prompt and try again. -->
    <Say>We didn't receive any input. Goodbye!</Say>
</Response>