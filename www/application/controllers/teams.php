<?
use Swagger\Annotations as SWG;

/**
 *
 * @SWG\Model(id="Team",required="uuid,name,type_id")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Team (for public consumption)")
 * @SWG\Property(name="owner_uuid",type="string",description="The id of the user who owns the team")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this team was created")
 * @SWG\Property(name="users",type="array",@SWG\Items("User"),description="The users attached to this team")
 *
 * @SWG\Model(id="TeamInvite",required="uuid,team_id,email")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the TeamInvite (for public consumption)")
 * @SWG\Property(name="email",type="string",description="The email that the invite is sent to")
 * @SWG\Property(name="key",type="string",description="The unique 32 character key assigned to this invite that allows the user to accept the invite")
 * @SWG\Property(name="team_id",type="integer",description="The id of the team for whom the invite is provided")
 * @SWG\Property(name="user_id",type="integer",description="The id of the user who used the invite")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this invite was created")
 * @SWG\Property(name="used",type="string",format="date",description="The date/time that this invite was used")
 *
 * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="2.0",
 *     resourcePath="/teams",
 *     basePath="http://conojoapp.scmreview.com/rest/teams"
 * )
 */
class Teams extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->validate_user();
        $this->load->helper('json');
        $this->load->model(array('Team', 'Team_Invite'));
    }

    /**
     *
     * @SWG\Api(
     *   path="/",
     *   description="API for team actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[Team]",
     *    summary="Returns a list of teams for the currently logged in user (must be logged in to view)"
     *   )
     * )
     */
    public function index_get()
    {
        $teams = $this->Team->get_for_user(get_user_id());
        $this->response($this->decorate_objects($teams));
    }

    /**
     *
     * @SWG\Api(
     *   path="/team",
     *   description="API for team actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="Team",
     *    summary="Returns the active team for the currently logged in user (or the team specified by the uuid)",
     * @SWG\Parameter(
     *       name="uuid",
     *       description="The unique ID of the project",
     *       paramType="path",
     *       required=false,
     *       type="string"
     *       )
     *     )
     *   )
     * )
     */
    public function team_get($uuid = '')
    {
        if ($uuid) {
            $team = validate_team_uuid($uuid);
        } else {
            $team = $this->Team->load(get_team_id());
        }
        if (!$team) {
            json_error('There is no active team for that user');
            exit;
        }

        $this->response($this->decorate_object($team));
    }


    /**
     * Rest endpoint for team related actions with a post
     * @param string $uuid
     * @param $action the action being performed
     */
    public function team_post($uuid = '', $action = '')
    {
        if ($action) {
            if ($action == 'invite') {
                return $this->team_invite($uuid);
            }
        }
    }

    /**
     *
     * @SWG\Api(
     *   path="/team/{uuid}/invite",
     *   description="API for team actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="Response",
     *    summary="Invite a user to a team.  You can only invite people who are not already on your team.",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="UUID of the team",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="email",
     *     description="The email address of the user you would like to invite",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     *
     * Invites a user to a team
     * @param string $uuid
     */
    private function team_invite($uuid = '')
    {
        $this->load->library('form_validation');
        $this->load->helper('notification');

        $team = validate_team_uuid($uuid);

        $this->form_validation->set_rules('email', 'Email', 'required|trim|xss_clean|valid_email');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: '.validation_errors(' ', ' '));
        } else {
            $email = $this->post('email', TRUE);

            /** Look to see if there is an existing invite and resend it */
            $invite = $this->Team_Invite->get_for_email_team($email, $team->id);
            $invite_id = 0;
            if($invite && !$invite->user_id) {
                $invite_id = $invite->id;
                $key = $invite->key;
            } else {
                $key = random_string('unique');
                $invite_id = $this->Team_Invite->add(array(
                    'email' => $email,
                    'team_id' => $team->id,
                    'key' => $key
                ));
            }

            notify_team_invite($invite_id, get_user_id());
            json_success("User invited successfully", array('invite_id' => $invite_id, 'email' => $email, 'key' => $key));
        }
    }

    protected function decorate_object($object) {
        return decorate_team($object);
    }
}
