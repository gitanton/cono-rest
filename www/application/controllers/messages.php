<?
use Swagger\Annotations as SWG;

/** *
 * @SWG\Model(id="Message",required="uuid,content,sender_id")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Message (for public use)")
 * @SWG\Property(name="sender_uuid",type="string",description="The id of the sender of the message")
 * @SWG\Property(name="content",type="string",description="The content of the message")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time of that the message was sent")
 * @SWG\Property(name="updated",type="string",format="date",description="The date/time of that the message was last updated")
 * @SWG\Property(name="reply_count",type="integer",description="The number of replies to this message")
 * @SWG\Property(name="project_uuid",type="string",description="The uuid of the project for whom the screen is provided")
 * @SWG\Property(name="recipients",type="array",@SWG\Items("User"),description="The recipients attached to this message")
 * @SWG\Property(name="replies",type="array",@SWG\Items("Message"),description="The replies attached to this message")
 *
 * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="2.0",
 *     resourcePath="/messages",
 *     basePath="http://conojoapp.scmreview.com/rest/messages"
 * )
 */
class Messages extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('json');
        $this->load->model(array('Message'));
    }

    /**
     *
     * @SWG\Api(
     *   path="/",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[Message]",
     *    summary="Returns a list of the current messages that the user is a recipient of",
     *    @SWG\Parameter(
     *       name="page",
     *       description="The starting page # of the messages (defaults to 0)",
     *       paramType="query",
     *       required=false,
     *       type="integer"
     *     ),
     *    @SWG\Parameter(
     *       name="limit",
     *       description="The number of results to return per page (defaults to 20)",
     *       paramType="query",
     *       required=false,
     *       type="integer"
     *     ),
     *    @SWG\Parameter(
     *       name="project_uuid",
     *       description="The UUID of the project that this message is attached to",
     *       paramType="query",
     *       required=false,
     *       type="string"
     *     ),
     *   )
     * )
     */
    public function index_get()
    {
        validate_team_read(get_team_id());
        $project_uuid = $this->get('project_uuid');
        $project_id = 0;
        if($project_uuid) {
            $project = validate_project_uuid($project_uuid);
            if($project) {
                $project_id = $project->id;
            }
        }
        $messages = $this->Message->get_for_user(get_user_id(), $project_id, $this->get('page', TRUE), $this->get('limit', TRUE));
        $this->response($this->decorate_objects($messages));
    }

    /**
     *
     * @SWG\Api(
     *   path="/",
     *   description="API for message actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="Message",
     *    summary="Create a new message for the current user (user must be logged in)",
     * @SWG\Parameter(
     *     name="content",
     *     description="Content of the message",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="project_uuid",
     *     description="The UUID of the project that this message is attached to",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="parent_uuid",
     *     description="The UUID of the message that is the parent of this one (if it is a reply -- leave it null if it is a new message)",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="recipients",
     *     description="A Comma-Separated List of UUIDs of individuals who should be recipients of the message (Example: '123,232,443').  If this is null, the message will be sent to all users on the project.  This will also be ignored if there is a parent_uuid set since we will default to the parent email for recipients",
     *     paramType="form",
     *     required=false,
     *     type="array[string]"
     *     )
     *   )
     * )
     *
     * Creates a new message and attaches project members to it.
     */
    public function index_post()
    {
        /* Validate add */
        $this->load->library('form_validation');
        $this->load->helper('notification');
        $this->form_validation->set_rules('content', 'Content', 'trim|required|xss_clean');
        $this->form_validation->set_rules('project_uuid', 'Project UUID', 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $project = validate_project_uuid($this->post('project_uuid', TRUE));
            $parent_uuid = $this->post('parent_uuid', TRUE);
            $data = array(
                'content' => $this->post('content', TRUE),
                'project_id' => $project->id,
                'sender_id' => get_user_id()
            );

            if($parent_uuid) {
                $parent_message = $this->Message->load_by_uuid($parent_uuid);
                if($parent_message) {
                    /* Prevent messages that are replies to replies so we don't have to deal with a multi-level hierarchy */
                    if($parent_message->parent_id) {
                        json_error('The parent uuid provided belongs to a reply.  You cannot reply to a reply but only to the parent messgae.');
                    } else {
                        $data['parent_id'] = $parent_message->id;
                    }
                }
            }

            $message = $this->Message->load($this->Message->add($data));

            /* Set the recipients on the message if it doesn't have a parent */
            /* Allow the recipients to be optional, if it isn't specified, all people on the project are marked as recipients */
            if(!$message->parent_id) {
                $recipients = $this->post('recipients', TRUE);
                if(!$recipients) {
                    $users = $this->User->get_for_project($project->id);
                    foreach($users as $user) {
                        $existing = $this->Message->get_message_user($message->id, $user->id);
                        if(!$existing) {
                            $this->Message->add_message_user($message->id, $user->id);
                        }
                    }
                } else {
                    $recipients = explode(",", $recipients);
                    foreach($recipients as $recipient) {
                        $user = $this->User->load_by_uuid($recipient);
                        $existing = $this->Message->get_message_user($message->id, $user->id);
                        if(!$existing) {
                            $this->Message->add_message_user($message->id, $user->id);
                        }
                    }
                }
                /* This is a new message, so store the add activity */
                activity_add_message($message->id);
                notify_new_message($message->id, get_user_id());
            } else {
                /* Update the updated date on the parent message */
                $this->Message->update($message->parent_id, array(
                    'updated' => timestamp_to_mysqldatetime(now())
                ));
                /* This is a new message, so store the add activity */
                activity_reply_message($message->id);
                notify_new_message($message->id, get_user_id(), $message->parent_id);
            }

            $this->response($this->decorate_object($message));
        }
    }

    /**
     * @SWG\Api(
     *   path="/message/{uuid}",
     *   description="API for message actions",
     * @SWG\Operation(
     *    method="GET",
     *    nickname="getMessage",
     *    type="Message",
     *    summary="Returns a message that matches the given uuid",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the message",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   ),
     *
     * @SWG\Operation(
     *    method="DELETE",
     *    type="Response",
     *    nickname="deleteMessage",
     *    summary="Deletes a message with the specified UUID",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the message",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */

    /**
     * Returns a single message referenced by their uuid
     * @param string $uuid
     */
    public function message_get($uuid = '')
    {
        validate_team_read(get_team_id());
        $message = validate_message_uuid($uuid);

        $this->response($this->decorate_object($message));
    }

    /**
     * Deletes a project by its uuid
     * @param string $uuid
     */
    public function message_delete($uuid = '')
    {
        $message = validate_message_uuid($uuid, true);
        activity_delete_message($message->id);
        $this->Message->delete($message->id);
        json_success("Message deleted successfully.");
    }

    protected function decorate_object($object)
    {
        return decorate_message($object);
    }
}

?>