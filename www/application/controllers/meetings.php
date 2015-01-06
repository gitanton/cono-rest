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
     *     name="project_uuid",
     *     description="The UUID of the project that this meeting is attached to",
     *     paramType="form",
     *     required=true,
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
     *     description="A Comma-Separated List of UUIDs of individuals who should be invited to the meeting (Example: '123,232,443'). If this is null, all members of the project will be invited",
     *     paramType="form",
     *     required=false,
     *     type="array[string]"
     *     )
     *   )
     * )
     *
     * Creates a new meeting and attaches project members to it.
     */
    public function index_post()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('notes', 'Notes', 'trim|xss_clean');
        $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', 'Date', 'trim|required|xss_clean');
        $this->form_validation->set_rules('time', 'Time', 'trim|required|xss_clean');
        $this->form_validation->set_rules('attendees', 'Attendees', 'trim|required|xss_clean');
        $this->form_validation->set_rules('project_uuid', 'Project UUID', 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $project = validate_project_uuid($this->post('project_uuid', TRUE));

            $datetime = server_datetime($this->post('date', TRUE), $this->post('time', TRUE));

            $data = array(
                'project_id' => $project->id,
                'date' => $datetime->format('Y-m-d'),
                'time' => $datetime->format('H:i'),
                'notes' => $this->post('notes', TRUE),
                'name' => $this->post('name', TRUE),
                'pin' => random_string('numeric', 6),
                'creator_id' => get_user_id(),
                'moderator_id' => get_user_id()
            );

            $meeting_id = $this->Meeting->add($data);

            /* Allow the attendees to be optional, if it isn't specified, all people on the project are invited */
            $attendees = $this->post('attendees', TRUE);
            if(!$attendees) {
                $users = $this->User->get_for_project($project->id);
                foreach($users as $user) {
                    $existing = $this->Meeting->get_meeting_user($meeting_id, $user->id);
                    if(!$existing) {
                        $this->Meeting->add_meeting_user($meeting_id, $user->id);
                    }
                }
            } else {
                $attendees = explode(",", $attendees);
                foreach($attendees as $attendee) {
                    $user = $this->User->load_by_uuid($attendee);
                    $existing = $this->Meeting->get_meeting_user($meeting_id, $user->id);
                    if(!$existing) {
                        $this->Meeting->add_meeting_user($meeting_id, $user->id);
                    }
                }
            }
            $meeting = $this->Meeting->load($meeting_id);
            $this->response($this->decorate_object($meeting));
        }
    }

    /**
     * @SWG\Api(
     *   path="/meeting/{uuid}",
     *   description="API for meeting actions",
     * @SWG\Operation(
     *    method="GET",
     *    nickname="getMeeting",
     *    type="Meeting",
     *    summary="Returns a meeting that matches the given uuid",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the meeting",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   ),
     *
     * @SWG\Operation(
     *    method="DELETE",
     *    type="Response",
     *    nickname="deleteMeeting",
     *    summary="Deletes a meeting with the specified UUID",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the meeting",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */

    /**
     * Returns a single meeting referenced by their uuid
     * @param string $uuid
     */
    public function meeting_get($uuid = '')
    {
        $meeting = validate_meeting_uuid($uuid);

        $this->response($this->decorate_object($meeting));
    }

    /**
     * Deletes a project by its uuid
     * @param string $uuid
     */
    public function meeting_delete($uuid = '')
    {
        $meeting = validate_meeting_uuid($uuid, true);

        $this->Meeting->delete($meeting->id);
        json_success("Message deleted successfully.");
    }

    protected function decorate_object($object)
    {
        return decorate_meeting($object);
    }
}
?>