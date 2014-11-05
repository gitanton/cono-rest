<?
use Swagger\Annotations as SWG;

/**
 *
 * @SWG\Model(id="Team",required="uuid,name,type_id")
 * @SWG\Property(name="id",type="integer",description="The unique ID of the Team (for private use in referencing other objects)")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Team (for public consumption)")
 * @SWG\Property(name="owner_id",type="integer",description="The id of the user who owns the team")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this team was created")
 * @SWG\Property(name="users",type="array",@SWG\Items("User"),description="The users attached to this team")
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
        $this->load->model('Team');
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
            $team = $this->Team->load_by_uuid($uuid);
        } else {
            $team = $this->Team->load($this->session->userdata(SESS_TEAM_ID));
        }
        if (!$team) {
            json_error('There is no active team for that user');
            exit;
        } else {
            /* Validate that the user is on the project */
            if (!$this->User->is_on_team($team->id, get_user_id())) {
                json_error('You are not authorized to view this team.');
                exit;
            }
            $this->response($this->decorate_object($team));
        }
    }

    protected function decorate_object($object) {
        unset($object->deleted);

        $users = $this->User->get_for_team($object->id);
        $object->users = $users;
        return $object;
    }
}
