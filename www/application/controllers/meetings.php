<?
use Swagger\Annotations as SWG;

/** *
 * @SWG\Model(id="Meeting",required="uuid,name,date,time")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Message (for public use)")
 * @SWG\Property(name="moderator_uuid",type="string",description="The id of the moderator of the meeting")
 * @SWG\Property(name="notes",type="string",description="The notes of the meeting")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time of that the meeting was created")
 * @SWG\Property(name="date",type="string",description="The date that the meeting will occur")
 * @SWG\Property(name="time",type="string",description="The uuid of the project for whom the screen is provided")
 * @SWG\Property(name="started",type="string",description="The date/time that the meeting was started")
 * @SWG\Property(name="ended",type="string",description="The date/time that the meeting was ended")
 * @SWG\Property(name="attendees",type="array",@SWG\Items("User"),description="The attendees invited to this message")
 *
 * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="2.0",
 *     resourcePath="/meetings",
 *     basePath="http://conojoapp.scmreview.com/rest/meetings"
 * )
 */
class Meetings extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('json');
        $this->load->model(array('Meeting'));
    }

    /**
     *
     * @SWG\Api(
     *   path="/",
     *   description="API for meeting actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[Meeting]",
     *    summary="Returns a list of the meetings that the user is invited to (that are taking place in the future)",
     *   )
     * )
     */
    public function index_get()
    {
        $meetings = $this->Meeting->get_for_user(get_user_id());
        $this->response($this->decorate_objects($meetings));
    }

    /**
     *
     * @SWG\Api(
     *   path="/",
     *   description="API for meeting actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="Meeting",
     *    summary="Schedule a new meeting",
     * @SWG\Parameter(
     *     name="notes",
     *     description="Notes/Description of the meeting",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="name",
     *     description="Name of the meeting",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="date",
     *     description="Date of the meeting (in the user's timezone) YYYY-MM-DD",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="time",
     *     description="Time of the meeting (in the user's timezone) 24-hour format (HH:MM)",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="attendees",
     *     description="An array of UUIDs of individuals who should be invited to the meeting",
     *     paramType="form",
     *     required=true,
     *     type="array[string]"
     *     )
     *   )
     * )
     *
     * Creates a new message and attaches project members to it.
     */
    public function index_post()
    {

    }
}
?>