<?
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
        $response = new stdClass;
        $response->timezones = get_timezones();
        $this->response($response);
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