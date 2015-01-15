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
 * @SWG\Model(id="MeetingComment",required="uuid,created,comment")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Message (for public use)")
 * @SWG\Property(name="creator_uuid",type="string",description="The id of the creator of the comment")
 * @SWG\Property(name="comment",type="string",description="The text of the comment")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time of that the meeting was created")
 * @SWG\Property(name="creator",type="User",description="The user who created the message")
 *
 * @SWG\Model(id="MeetingDelta",required="uuid,created,data")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Message (for public use)")
 * @SWG\Property(name="creator_uuid",type="string",description="The id of the creator of the comment")
 * @SWG\Property(name="data",type="string",description="The data of the change")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time of that the meeting was created")
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
        $this->load->model(array('Meeting', 'Meeting_Comment', 'Meeting_Delta'));
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
    public function meeting_get($uuid = '', $action = '')
    {
        if($action) {
            if ($action == 'chat') {
                return $this->meeting_chat_get($uuid);
            } else if ($action === 'delta') {
                return $this->meeting_delta_get($uuid);
            } else if ($action === 'participants') {
                return $this->meeting_participants($uuid);
            }
        } else {
            $meeting = validate_meeting_uuid($uuid);
            $this->response($this->decorate_object($meeting));
        }
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

    /**
     * Rest endpoint for meeting related actions with a post
     * @param string $uuid
     * @param $action the action being performed
     */
    public function meeting_post($uuid = '', $action = '')
    {
        if ($action) {
            if ($action == 'chat') {
                return $this->meeting_chat_add($uuid);
            } else if ($action === 'delta') {
                return $this->meeting_delta_add($uuid);
            } else if ($action === 'start') {
                return $this->meeting_start($uuid);
            } else if ($action === 'end') {
                return $this->meeting_end($uuid);
            }
        }
    }

    /**
     *
     * @SWG\Api(
     *   path="/meeting/{uuid}/chat",
     *   description="API for meeting actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="MeetingComment",
     *    summary="Adds a comment to the live chat for the current meeting",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="UUID of the meeting",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="comment",
     *     description="The comment to be added to the meeting",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     *   )
     * )
     *
     * Adds a comment to an existing meeting
     * @param string $uuid
     */
    private function meeting_chat_add($uuid = '')
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('comment', 'Comment', 'trim|required|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {

            $meeting = validate_meeting_uuid($uuid, true);

            $comment_id = $this->Meeting_Comment->add(array(
                'comment' => $this->post('comment'),
                'meeting_id' => $meeting->id,
                'creator_id' => get_user_id()
            ));
            $comment = $this->Meeting_Comment->load($comment_id);

            $this->response(decorate_meeting_comment($comment));

        }
    }

    /**
     *
     * @SWG\Api(
     *   path="/meeting/{uuid}/chat",
     *   description="API for meeting actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[MeetingComments]",
     *    summary="Get the last comments of a meeting",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="UUID of the meeting",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="last_uuid",
     *     description="The last meeting comment id that the client has received",
     *     paramType="query",
     *     required=false,
     *     type="string"
     *     ),
     *   )
     * )
     *
     * Adds a delta (change) to an ongoing meeting
     * @param string $uuid
     */
    private function meeting_chat_get($uuid = '')
    {
        $meeting = validate_meeting_uuid($uuid, true);
        $last_id = 0;
        if($this->get('last_uuid')) {
            $last_id = $this->Meeting_Comment->get_id($this->get('last_uuid'));
        }
        $comments = $this->Meeting_Comment->get_for_meeting($meeting->id, $last_id);

        $this->response(decorate_meeting_comments($comments));
    }

    /**
     *
     * @SWG\Api(
     *   path="/meeting/{uuid}/participants",
     *   description="API for meeting actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[User]",
     *    summary="Get the users who have connected to a meeting",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="UUID of the meeting",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     *   )
     * )
     *
     * Adds a delta (change) to an ongoing meeting
     * @param string $uuid
     */
    private function meeting_participants($uuid = '')
    {
        $meeting = validate_meeting_uuid($uuid, true);
        $users = $this->User->get_for_meeting($meeting->id, true);

        $this->response($this->decorate_object($users));
    }

    /**
     *
     * @SWG\Api(
     *   path="/meeting/{uuid}/delta",
     *   description="API for meeting actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="MeetingDelta",
     *    summary="Post a screen update to a meeting",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="UUID of the meeting",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="data",
     *     description="The json data representation of how the meeting has changed",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     *   )
     * )
     *
     * Adds a delta (change) to an ongoing meeting
     * @param string $uuid
     */
    private function meeting_delta_add($uuid = '')
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('data', 'Data', 'trim|required|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {

            $meeting = validate_meeting_uuid($uuid, true);

            $delta_id = $this->Meeting_Delta->add(array(
                'data' => $this->post('data'),
                'meeting_id' => $meeting->id
            ));
            $delta = $this->Meeting_Delta->load($delta_id);

            $this->response(decorate_meeting_delta($delta));

        }
    }

    /**
     *
     * @SWG\Api(
     *   path="/meeting/{uuid}/delta",
     *   description="API for meeting actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[MeetingDelta]",
     *    summary="Get the last screen updates to a meeting",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="UUID of the meeting",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="last_uuid",
     *     description="The last meeting delta id that the client has received",
     *     paramType="query",
     *     required=false,
     *     type="string"
     *     ),
     *   )
     * )
     *
     * Adds a delta (change) to an ongoing meeting
     * @param string $uuid
     */
    private function meeting_delta_get($uuid = '')
    {
        $meeting = validate_meeting_uuid($uuid, true);
        $last_id = 0;
        if($this->get('last_uuid')) {
            $last_id = $this->Meeting_Delta->get_id($this->get('last_uuid'));
        }
        $comments = $this->Meeting_Delta->get_for_meeting($meeting->id, $last_id);

        $this->response(decorate_meeting_deltas($comments));
    }

    /**
     *
     * @SWG\Api(
     *   path="/meeting/{uuid}/start",
     *   description="API for meeting actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="Meeting",
     *    summary="Starts a new meeting from a moderator",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="UUID of the meeting",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     *   )
     * )
     *
     * Adds a delta (change) to an ongoing meeting
     * @param string $uuid
     */
    private function meeting_start($uuid = '')
    {
        $meeting = validate_meeting_uuid($uuid, false, true);
        if($meeting->ended) {
            json_error('This meeting has already ended and cannot be restarted.');
        }
        if(!$meeting->started) {
            $this->Meeting->update($meeting->id, array('started' => timestamp_to_mysqldatetime(now())));
            $meeting = $this->Meeting->load($meeting->id);
        }
        $this->response($this->decorate_object($meeting));
    }

    /**
     *
     * @SWG\Api(
     *   path="/meeting/{uuid}/end",
     *   description="API for meeting actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="Meeting",
     *    summary="Ends an ongoing meeting from a moderator",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="UUID of the meeting",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     *   )
     * )
     *
     * Adds a delta (change) to an ongoing meeting
     * @param string $uuid
     */
    private function meeting_end($uuid = '')
    {
        $meeting = validate_meeting_uuid($uuid, true, true);
        if($meeting->ended) {
            $this->Meeting->update($meeting->id, array('ended' => timestamp_to_mysqldatetime(now())));
            $meeting = $this->Meeting->load($meeting->id);
        }

        $this->response($this->decorate_object($meeting));
    }

    protected function decorate_object($object)
    {
        return decorate_meeting($object);
    }
}
?>