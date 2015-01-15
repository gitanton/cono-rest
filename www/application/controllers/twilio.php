<?
class Twilio extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('json');
        $this->load->model(array('Meeting'));
    }

    function voice_post($action = '', $meeting_uuid='')
    {
        $this->data = array();
        header('Content-Type: application/xml; charset=utf-8');

        if(!$action) {
            $this->load->view('twilio/enter-pin', $this->data);
        }
        /* They have entered a pin */
        else if($action==='pin') {
            $pin =  $this->post('Digits');
            $this->data['pin'] = $pin;

            /* Lookup the conference by pin */
            $today = date("Y-m-d");
            $participant_meeting = $this->Meeting->load_by_pin($pin, $today);

            /* If this is a meeting participant */
            if($participant_meeting) {
                if($participant_meeting->ended) {
                    $this->load->view('twilio/conference-ended.php', $this->data);
                    return;
                }

                $this->data['meeting_id'] = $participant_meeting->uuid;
                $this->load->view('twilio/conference-participant.php', $this->data);
                return;
            }

            /* If this is a meeting moderator */
            $moderator_meeting = $this->Meeting->load_by_moderator_pin($pin, $today);
            if($moderator_meeting) {
                if($moderator_meeting->ended) {
                    $this->load->view('twilio/conference-ended.php', $this->data);
                    return;
                }

                $this->Meeting->update_by_uuid($moderator_meeting->uuid, array('started' => timestamp_to_mysqldatetime(now())));
                $this->data['meeting_id'] = $moderator_meeting->uuid;
                $this->load->view('twilio/conference-moderator.php', $this->data);
                return;
            }

            $this->load->view('twilio/invalid-pin.php', $this->data);
        } else if($action==='end') {
            $this->Meeting->update_by_uuid($meeting_uuid, array('ended' => timestamp_to_mysqldatetime(now())));
            return;
        }
    }
}
?>