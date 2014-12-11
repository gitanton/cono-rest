<?
use Swagger\Annotations as SWG;

/**
 *
 * @SWG\Model(id="Screen",required="uuid")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Screen (for public consumption)")
 * @SWG\Property(name="creator_uuid",type="string",description="The id of the user who created the screen")
 * @SWG\Property(name="ordering",type="integer",description="The ordering of how the screen should be displayed in the list of screens for this project")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this screen was created")
 * @SWG\Property(name="image_width",type="float",description="The width of the image")
 * @SWG\Property(name="image_height",type="float",description="The height of the image")
 * @SWG\Property(name="file_size",type="float",description="The size of the image")
 * @SWG\Property(name="url",type="string",description="The url of the screenshot image")
 * @SWG\Property(name="file_type",type="string",description="The file type of the screenshot image")
 * @SWG\Property(name="project_uuid",type="string",description="The uuid of the project for whom the screen is provided")
 * @SWG\Property(name="hotspots",type="array",@SWG\Items("Hotspot"),description="The hotspots assigned to this screen")
 *
 *
 * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="2.0",
 *     resourcePath="/screens",
 *     basePath="http://conojoapp.scmreview.com/rest/screens"
 * )
 */
class Screens extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->validate_user();
        $this->load->helper('json');
        $this->load->model(array('Project', 'Screen', 'Hotspot'));
    }

    /**
     *
     * @SWG\Api(
     *   path="/project/{project_uuid}",
     *   description="API for screen actions",
     * @SWG\Operation(
     *    method="GET",
     *    nickname="Get Screens",
     *    type="array[Screen]",
     *    summary="Returns a list of screens for the specified project",
     * @SWG\Parameter(
     *     name="project_uuid",
     *     description="The unique ID of the project",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   ),
     * @SWG\Operation(
     *    method="POST",
     *    nickname="Add Screen",
     *    type="Screen",
     *    summary="Create a new screen for the given project",
     * @SWG\Parameter(
     *     name="project_uuid",
     *     description="The unique ID of the project",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="file",
     *     description="File Upload of the screenshot",
     *     paramType="form",
     *     required=false,
     *     type="file"
     *     ),
     * @SWG\Parameter(
     *     name="url",
     *     description="A url for where to fetch the image for the screenshot",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     )
     *   )
     * )
     */
    public function project_get($project_uuid = '')
    {
        $project = validate_project_uuid($project_uuid);
        $screens = $this->Screen->get_for_project($project->id);
        $this->response($this->decorate_objects($screens));
    }

    public function project_post($project_uuid = '')
    {
        $project = validate_project_uuid($project_uuid);
        $screen = null;

        if (isset($_FILES['file'])) {
            $screen = $this->add_screenshot_upload($project);
        } else if ($this->post('url')) {

        }

        if (!$screen) {
            json_error('You must provide either a url or uploaded file for the screenshot.');
            exit;
        }

        /* Handle the download situation */
        $this->response($screen);
    }


    /**
     *
     * @SWG\Api(
     *   path="/screen/{screen_uuid}",
     *   description="API for screen actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="Screen",
     *    nickname="Get Screen",
     *    summary="Returns a screen specified by the given uuid",
     * @SWG\Parameter(
     *     name="screen_uuid",
     *     description="The unique ID of the screen",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   ),
     *
     * @SWG\Operation(
     *    method="DELETE",
     *    nickname="Delete Screen",
     *    type="Response",
     *    summary="Deletes a screen with the specified UUID",
     * @SWG\Parameter(
     *     name="screen_uuid",
     *     description="The unique ID of the screen",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */

    /**
     * Returns either a screen or a list of hotspots for that screen depending on what the action is
     * @param string $uuid
     * @param string $action
     */
    public function screen_get($uuid = '', $action = '')
    {
        $screen = validate_screen_uuid($uuid);
        if ($action && $action === 'hotspots') {
            $this->response(decorate_hotspots($screen->hotspots));
        } else {
            $this->response($this->decorate_object($screen));
        }
    }

    /**
     * Deletes a screen by its uuid
     * @param string $uuid
     */
    public function screen_delete($uuid = '')
    {
        $screen = validate_screen_uuid($uuid);

        $this->Screen->delete($screen->id);
        json_success("Screen deleted successfully.");
    }


    /**
     *
     * @SWG\Api(
     *   path="/screen/{screen_uuid}/hotspots/",
     *   description="API for screen actions",
     * @SWG\Operation(
     *    method="GET",
     *    nickname="List Hotspots",
     *    type="array[Hotspot]",
     *    summary="Returns a list of hotspots for the specified screen",
     * @SWG\Parameter(
     *     name="screen_uuid",
     *     description="The unique ID of the screen",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   ),
     * @SWG\Operation(
     *    method="POST",
     *    type="Hotspot",
     *    nickname="Add Hotspot",
     *    summary="Create a new hotspot for the given screen",
     * @SWG\Parameter(
     *     name="screen_uuid",
     *     description="The unique ID of the screen",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="data",
     *     description="The hotspot json data in string form",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     )
     *   )
     * )
     */
    public function screen_post($uuid = '', $action = '')
    {
        $screen = validate_screen_uuid($uuid);
        if ($action && $action === 'hotspots') {
            $this->add_hotspot($screen);
        }
    }

    /**
     * Creates a new hotspot on the screen
     * @param $screen
     */
    private function add_hotspot($screen)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('data', 'Data', 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $hotspot_id = $this->Hotspot->add(array(
                'screen_id' => $screen->id,
                'ordering' => $this->Hotspot->get_max_ordering_for_screen($screen->id) + 1,
                'creator_id' => get_user_id(),
                'data' => $this->post('data', TRUE)
            ));
            $hotspot = decorate_hotspot($this->Hotspot->load($hotspot_id));
            $this->response($hotspot);
        }
    }

    /**
     * Adds a screenshot to a project via a file upload.  Requires that the file be uploaded as 'upload'
     * @param $project
     */
    private function add_screenshot_upload($project)
    {
        $config['upload_path'] = $this->config->item('upload_dir');
        $config['allowed_types'] = $this->config->item('upload_types');
        $config['max_size'] = $this->config->item('max_file_upload_size');
        $config['encrypt_name'] = true;

        /* Handle the file upload */
        $this->load->library('upload', $config);
        if ($this->upload->do_upload('file')) {
            $data = $this->upload->data();
            $insert = array(
                'creator_id' => get_user_id(),
                'project_id' => $project->id,
                'ordering' => $this->Screen->get_max_ordering_for_project($project->id) + 1,
                'url' => $data['file_name'],
                'file_type' => $data['file_type'],
                'file_size' => $data['file_size'],
                'image_height' => $data['image_height'],
                'image_width' => $data['image_width']
            );
            $screen = $this->decorate_object($this->Screen->load($this->Screen->add($insert)));
            return $screen;
        }
    }

    protected function decorate_object($object)
    {
        return decorate_screen($object);
    }
}

?>