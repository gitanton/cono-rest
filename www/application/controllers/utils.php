<?php
use Swagger\Annotations as SWG;

/**
 *
 * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="2.0",
 *     resourcePath="/utils",
 *     basePath="http://conojoapp.scmreview.com/rest/utils"
 * )
 */
class Utils extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('json');
    }


    /**
     *
     * @SWG\Api(
     *   path="/bootstrap",
     *   description="API for generic utility actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[Object]",
     *    summary="Returns an object with lookup data for the front-end"
     *   )
     * )
     */
    public function bootstrap_get() {
        $this->load->model('Plan');
        $response = new stdClass;
        $response->token = $this->twilio_token();
        $response->stripe_key = $this->config->item('stripe_public_key');
        $response->plans = $this->Plan->get_all();
        $response->timezones = get_timezones();
        $response->countries = get_countries();
        $this->response($response);
    }

    private function twilio_token() {

        include APPPATH.'../vendor/twilio/sdk/Services/Twilio/Capability.php';
        $capability = new Services_Twilio_Capability($this->config->item('twilio_account_sid'), $this->config->item('twilio_auth_token'));
        $capability->allowClientOutgoing($this->config->item('twilio_app_sid'));
        $token = $capability->generateToken();
        return $token;
    }


    /**
     *
     * @SWG\Api(
     *   path="/timezones",
     *   description="API for generic utility actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[Object]",
     *    summary="Returns a list of maps of timezones"
     *   )
     * )
     */
    public function timezones_get()
    {
        $this->response(get_timezones());
    }
}
?>