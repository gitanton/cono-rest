<?php echo '<?xml version="1.0" encoding="UTF-8"?>';?>
<Response>
    <Dial>
        <Conference startConferenceOnEnter="false" waitUrl="http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical">
            <?=$meeting_id?>
        </Conference>
    </Dial>
</Response>