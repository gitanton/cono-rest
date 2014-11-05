<?
use Swagger\Annotations as SWG;

/**
 *
 * @SWG\Model(id="Project",required="uuid,name,type_id")
 * @SWG\Property(name="id",type="integer",description="The unique ID of the Project (for private use in referencing other objects)")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Project (for public consumption)")
 * @SWG\Property(name="name",type="string",description="The name of the Project")
 * @SWG\Property(name="creator_id",type="integer",description="The id of the user who created the project")
 * @SWG\Property(name="type_id",type="integer",description="The project type id")
 * @SWG\Property(name="archived",type="integer",description="Whether this project is archived or not")
 * @SWG\Property(name="ordering",type="integer",description="The ordering of how the project should be displayed in the list of projects")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this project was created")
 * @SWG\Property(name="users",type="array",@SWG\Items("User"),description="The users attached to this project")
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
        $this->load->model('Project');
    }

    /**
     *
     * @SWG\Api(
     *   path="/",
     *   description="API for project actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[Project]",
     *    summary="Returns a list of projects for the currently logged in user (must be logged in to view)"
     *   )
     * )
     */
    public function index_get()
    {
        $projects = $this->Project->get_for_user(get_user_id());
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
        /* Validate add */
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Project Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('type_id', 'Type ID', 'trim|required|integer|xss_clean|callback_validate_project_type');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $data = array(
                'name' => $this->post('name', TRUE),
                'type_id' => intval($this->post('type_id', TRUE))
            );

            $project = $this->decorate_object($this->Project->load($this->Project->add($data)));
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
     *   @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the project",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   ),
     *
     *  @SWG\Operation(
     *    method="DELETE",
     *    type="Response",
     *    summary="Deletes a project with the specified UUID",
     *   @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the project",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */
    public function project_put($uuid='')
    {
        /* Validate update - have to copy the fields from put to $_POST for validation */
        $_POST['name'] = $this->put('name');
        $_POST['type_id'] = $this->put('type_id');

        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Project Name', 'trim|xss_clean');
        $this->form_validation->set_rules('type_id', 'Type ID', 'trim|integer|xss_clean|callback_validate_project_type');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: '.validation_errors(' ', ' '));
        } else {
            $data = $this->get_put_fields($this->Project->get_fields());
            $this->Project->update_by_uuid($uuid, $data);
            $this->project_get($uuid);
        }
    }

    /**
     * Returns a single user referenced by their uuid
     * @param string $uuid
     */
    public function project_get($uuid = '')
    {
        if (!$uuid) {
            json_error('uuid is required');
            exit;
        }
        $project = $this->Project->load_by_uuid($uuid);
        if (!$project) {
            json_error('There is no project with that id');
            exit;
        } else {
            /* Validate that the user is on the project */
            if(!$this->User->is_on_project($project->id, get_user_id())) {
                json_error('You are not authorized to view this project.');
                exit;
            }
            $this->response($this->decorate_object($project));
        }
    }

    /**
     * Deletes a project by its uuid
     * @param string $uuid
     */
    public function project_delete($uuid = '')
    {
        if (!$uuid) {
            json_error('uuid is required');
            exit;
        }
        $project = $this->Project->load_by_uuid($uuid);
        if (!$project) {
            json_error('There is no project with that id');
            exit;
        } else {
            /* Validate that the user is on the project */
            if(!$this->User->is_on_project($project->id, get_user_id())) {
                json_error('You are not authorized to delete this project.');
                exit;
            }
            $this->Project->delete($project->id);
            json_success("Project deleted successfully.");
        }
    }

    /**
     * Duplicates an existing project along with the list of users that is assigned to that project
     * @param string $uuid
     */
    public function project_duplicate($uuid = '') {

    }

    public function validate_project_type($type_id=0)
    {
        if(intval($type_id)) {
            $type = table_lookup('project_type', $type_id);
            if($type) {
                return TRUE;
            }
        }
        $this->form_validation->set_message('validate_project_type', 'The %s is an invalid type.');
        return FALSE;
    }

    protected function decorate_object($object) {
        unset($object->deleted);

        $users = $this->User->get_for_project($object->id);
        $object->users = $users;
        return $object;
    }
}

?>