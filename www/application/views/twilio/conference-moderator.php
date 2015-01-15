<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<Response>
    <Dial>
        <Conference startConferenceOnEnter="true" endConferenceOnExit="true" eventCallbackUrl="<?=site_url('twilio/voice/end/' . $meeting_id)?>">
            <?=$meeting_id?>
        </Conference>
    </Dial>
</Response>