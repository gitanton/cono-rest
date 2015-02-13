<?
/**
 * Webhooks for stripe to notify us that a user's card is bad or expired or something failed.
 * Class Stripe
 */

class Stripe extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->validate_user();
        $this->load->helper('json');
        $this->load->model(array('Project', 'Video', 'Comment', 'Hotspot'));
    }
}

?>