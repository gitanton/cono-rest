<?
use Swagger\Annotations as SWG;

/**
 *
 * @SWG\Model(id="Project",required="uuid,name,type_id")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Project (for public consumption)")
 * @SWG\Property(name="name",type="string",description="The name of the Project")
 * @SWG\Property(name="creator_uuid",type="string",description="The id of the user who created the project")
 * @SWG\Property(name="type_id",type="integer",description="The project type id")
 * @SWG\Property(name="archived",type="integer",description="Whether this project is archived or not")
 * @SWG\Property(name="ordering",type="integer",description="The ordering of how the project should be displayed in the list of projects")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this project was created")
 * @SWG\Property(name="users",type="array",@SWG\Items("User"),description="The users attached to this project")
 *
 * @SWG\Model(id="ProjectInvite",required="uuid,project_id,email")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the ProjectInvite (for public consumption)")
 * @SWG\Property(name="email",type="string",description="The email that the invite is sent to")
 * @SWG\Property(name="key",type="string",description="The unique 32 character key assigned to this invite that allows the user to accept the invite")
 * @SWG\Property(name="project_id",type="integer",description="The id of the project for whom the invite is provided")
 * @SWG\Property(name="user_id",type="integer",description="The id of the user who used the invite")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this invite was created")
 * @SWG\Property(name="used",type="string",format="date",description="The date/time that this invite was used")
 *
 * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="2.0",
 *     resourcePath="/projects",
 *     basePath="http://conojoapp.scmreview.com/rest/projects"
 * )
 */
class Projects extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->validate_user();
        $this->load->helper('json');
        $this->load->model(array('Project', 'Project_Invite'));
    }

    /**
     *
     * @SWG\Api(
     *   path="/",
     *   description="API for project actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[Project]",
     *    summary="Returns a list of projects for the currently logged in user (must be logged in to view) for the current team"
     *   )
     * )
     */
    public function index_get()
    {
        validate_team_read(get_team_id());
        $projects = $this->Project->get_for_user_team(get_user_id(), get_team_id());
        $this->response($this->decorate_objects($projects));
    }

    /**
     *
     * @SWG\Api(
     *   path="/",
     *   description="API for project actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="Project",
     *    summary="Create a new project for the current user (user must be logged in)",
     * @SWG\Parameter(
     *     name="name",
     *     description="Name of the project",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="type_id",
     *     description="The Type of the project (1:UI/UX Project, 2:Video Project, 3:Business Template",
     *     paramType="form",
     *     required=true,
     *     type="integer"
     *     )
     *   )
     * )
     */
    public function index_post()
    {
        /* Validate that they are the team owner */
        validate_team_owner(get_team_id(), get_user_id());
        /* Validate that they have a valid subscription and can add a project */
        validate_project_add(get_user_id());

        /* Validate add */
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Project Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('type_id', 'Type ID', 'trim|required|integer|xss_clean|callback_validate_project_type');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $data = array(
                'name' => $this->post('name', TRUE),
                'type_id' => intval($this->post('type_id', TRUE)),
                'team_id' => get_team_id()
            );
            $project_id = $this->Project->add($data);
            /* Add the activity item to indicate that a project was added */
            activity_add_project($project_id, get_user_id());
            $project = $this->decorate_object($this->Project->load($project_id));
            $this->response($project);
        }
    }

    /**
     *
     * @SWG\Api(
     *   path="/project/{uuid}",
     *   description="API for project actions",
     * @SWG\Operation(
     *    method="PUT",
     *    type="Project",
     *    summary="Updates an existing project",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="Unique ID of the project",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="body",
     *     description="Project object that needs to be updated",
     *     paramType="body",
     *     required=true,
     *     type="User"
     *     )
     *   ),
     *
     *
     * @SWG\Operation(
     *    method="GET",
     *    type="Project",
     *    summary="Returns a project that matches the given uuid",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the project",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   ),
     *
     * @SWG\Operation(
     *    method="DELETE",
     *    type="Response",
     *    summary="Deletes a project with the specified UUID",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the project",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */
    public function project_put($uuid = '')
    {
        $this->validate_user();
        /* Validate update - have to copy the fields from put to $_POST for validation */
        $_POST['name'] = $this->put('name');
        $_POST['type_id'] = $this->put('type_id');

        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Project Name', 'trim|xss_clean');
        $this->form_validation->set_rules('type_id', 'Type ID', 'trim|integer|xss_clean|callback_validate_project_type');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $project = validate_project_uuid($uuid);
            /* Validate that they are the team owner */
            validate_team_owner($project->team_id, get_user_id());

            $data = $this->get_put_fields($this->Project->get_fields());
            $this->Project->update_by_uuid($uuid, $data);
            /* Add the activity item to indicate that a project was updated */
            activity_update_project($project->id, get_user_id());
            $this->project_get($uuid);
        }
    }

    /**
     * Returns a single user referenced by their uuid
     * @param string $uuid
     */
    public function project_get($uuid = '')
    {
        $this->validate_user();
        validate_team_read(get_team_id());
        $project = validate_project_uuid($uuid);
        $this->response($this->decorate_object($project));
    }

    /**
     * Deletes a project by its uuid
     * @param string $uuid
     */
    public function project_delete($uuid = '')
    {
        $this->validate_user();
        $project = validate_project_uuid($uuid);

        validate_team_read($project->team_id);
        /* Validate that they are the team owner */
        validate_team_owner($project->team_id, get_user_id());

        /* Add the activity item to indicate that a project was updated */
        activity_delete_project($project->id, get_user_id());
        $this->Project->delete($project->id);
        json_success("Project deleted successfully.");
    }


    /**
     * Rest endpoint for project related actions with a post
     * @param string $uuid
     * @param $action the action being performed
     */
    public function project_post($uuid = '', $action = '')
    {
        $this->validate_user();
        if ($action) {
            if ($action == 'duplicate') {
                return $this->project_duplicate($uuid);
            } else if ($action == 'invite') {
                return $this->project_invite($uuid);
            }
        }
    }

    /**
     *
     * @SWG\Api(
     *   path="/project/{uuid}/duplicate",
     *   description="API for project actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="Project",
     *    summary="Duplicate a project as specified by its uuid (user must be logged in)",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="UUID of the project",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="name",
     *     description="Name of the new, duplicated project",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     ),
     *   )
     * )
     *
     * Duplicates an existing project along with the list of users that is assigned to that project
     * @param string $uuid
     */
    private function project_duplicate($uuid = '')
    {
        $project = validate_project_uuid($uuid);
        validate_team_read($project->team_id);
        /* Validate that they are the team owner */
        validate_team_owner($project->team_id, get_user_id());
        /* Validate that they have a valid subscription and can add a project */
        validate_project_add(get_user_id());

        $duplicate_id = $this->Project->duplicate($project, get_user_id(), trim($this->post('name', TRUE)));
        $duplicate = $this->Project->load($duplicate_id);
        $this->response($this->decorate_object($duplicate));
    }

    /**
     *
     * @SWG\Api(
     *   path="/ordering",
     *   description="API for project actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="Response",
     *    summary="Order a list of projects",
     * @SWG\Parameter(
     *     name="uuids",
     *     description="Ordered uuids of the projects as they have been ordered",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     *
     * Reorders the list of projects for a user
     */
    public function ordering_post() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('uuids', 'Project Ids', 'trim|required|xss_clean');

        $uuids = json_decode($this->post('uuid', TRUE));
        if($uuids) {
            $i = 0;
            foreach($uuids as $uuid) {
                $project_id = $this->Project->get_id($uuid);
                if($project_id) {
                    $this->Project->update_ordering(get_user_id(), $project_id, $i++);
                }
            }
        }
        json_success('Projects ordered successfully');
    }


    /**
     *
     * @SWG\Api(
     *   path="/project/{uuid}/invite",
     *   description="API for project actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="ProjectInvite",
     *    summary="Invite a user to a project.  You can either invite a member of your team by passing their uuid or by sending them an external email",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="UUID of the project",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="user_uuid",
     *     description="The uuid of the user you would like to invite (optional)",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="email",
     *     description="The email address of the external user you would like to invite (optional)",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     ),
     *   )
     * )
     *
     * Invites a user to a project
     * @param string $uuid
     */
    private function project_invite($uuid = '')
    {
        $this->load->helper('notification');
        $project = validate_project_uuid($uuid);

        /* Validate that the team owner has a valid subscription or free trial */
        validate_team_read($project->team_id);

        /* Validate that they are the team owner */
        validate_team_owner($project->team_id, get_user_id());

        /* Validate that they have a valid subscription and can add a project */
        validate_user_add(get_user_id());

        $user_uuid = $this->post('user_uuid', TRUE);
        $email = $this->post('email', TRUE);

        if ($email) {
            /** Look to see if there is an existing invite and resend it */
            $invite = $this->Project_Invite->get_for_email_project($email, $project->id);

            $invite_id = 0;
            if ($invite && !$invite->user_id) {
                $invite_id = $invite->id;
                $key = $invite->key;
            } else {
                $key = random_string('unique');
                $invite_id = $this->Project_Invite->add(array(
                    'email' => $email,
                    'project_id' => $project->id,
                    'key' => $key
                ));
            }

            notify_project_invite_new_user($invite_id, get_user_id());
            json_success("User invited successfully", array('invite_id' => $invite_id, 'email' => $email, 'key' => $key));
            exit;
        } else if ($user_uuid) {
            $user = validate_user_uuid($user_uuid);

            /* Validate that the user is on the project */
            if (!$this->User->is_on_team(get_team_id(), $user->id)) {
                json_error('The user you are inviting is not on your team.  Please invite them to your team first.');
                exit;
            }

            $invite = $this->Project_Invite->get_for_user_id_project($user->id, $project->id);

            if ($invite) {
                $invite_id = $invite->id;
                $key = $invite->key;
            } else {
                $key = random_string('unique');
                $invite_id = $this->Project_Invite->add(array(
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'key' => $key
                ));
            }

            notify_project_invite_new_user($invite_id, get_user_id());
            json_success("User invited successfully", array('invite_id' => $invite_id, 'email' => $email, 'key' => $key));
            exit;
        }

        json_error("You must provide either a user id or an email address to invite to this project.");
    }

    public function validate_project_type($type_id = 0)
    {
        if (intval($type_id)) {
            $type = table_lookup('project_type', $type_id);
            if ($type) {
                return TRUE;
            }
        }
        $this->form_validation->set_message('validate_project_type', 'The %s is an invalid type.');
        return FALSE;
    }

    protected function decorate_object($object)
    {
        return decorate_project($object);
    }
}

?>