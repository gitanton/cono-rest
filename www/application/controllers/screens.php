<?
use Swagger\Annotations as SWG;

/**
 *
 * @SWG\Model(id="Screen",required="uuid")
 * @SWG\Property(name="id",type="integer",description="The unique ID of the Screen (for private use in referencing other objects)")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Screen (for public consumption)")
 * @SWG\Property(name="creator_id",type="integer",description="The id of the user who created the screen")
 * @SWG\Property(name="ordering",type="integer",description="The ordering of how the screen should be displayed in the list of screens for this project")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this screen was created")
 * @SWG\Property(name="image_width",type="float",description="The width of the image")
 * @SWG\Property(name="image_height",type="float",description="The height of the image")
 * @SWG\Property(name="file_size",type="float",description="The size of the image")
 * @SWG\Property(name="url",type="string",description="The url of the screenshot image")
 * @SWG\Property(name="file_type",type="string",description="The file type of the screenshot image")
 * @SWG\Property(name="hotspots",type="array",@SWG\Items("Hotspot"),description="The hotspots assigned to this screen")
 *
 * @SWG\Model(id="Hotspot",required="uuid")
 * @SWG\Property(name="id",type="integer",description="The unique ID of the Screen (for private use in referencing other objects)")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Screen (for public consumption)")
 * @SWG\Property(name="creator_id",type="integer",description="The id of the user who created the screen")
 * @SWG\Property(name="screen_id",type="integer",description="The id of the screen for whom the hotspot is provided")
 * @SWG\Property(name="ordering",type="integer",description="The ordering of how the screen should be displayed in the list of screens")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this screen was created")
 * @SWG\Property(name="data",type="string",description="The json data for the html5 canvas object")
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
        $this->load->model(array('Screen', 'Hotspot'));
    }

    /**
     *
     * @SWG\Api(
     *   path="/project/{project_uuid}",
     *   description="API for screen actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[Screen]",
     *    summary="Returns a list of screens for the specified project"
     *   ),
     * @SWG\Operation(
     *    method="POST",
     *    type="Screen",
     *    summary="Create a new screen for the given project",
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
    public function project_get($project_uuid='')
    {
        $project = validate_project_uuid($project_uuid);
        $screens = $this->Screen->get_for_project($project->id);
        $this->response($this->decorate_objects($screens));
    }

    public function project_post($project_uuid='')
    {
        $project = validate_project_uuid($project_uuid);

        $config['upload_path'] = $this->config->item('upload_dir');
        $config['allowed_types'] = $this->config->item('upload_types');
        $config['max_size']	= $this->config->item('max_file_upload_size');
        $config['encrypt_name'] = true;

        /* Handle the file upload */
        $this->load->library('upload', $config);
        if ($this->upload->do_upload('file')) {
            $data = $this->upload->data();
            $insert = array(
                'creator_id' => get_user_id(),
                'project_id' => $project->id,
                'ordering' => $this->Screen->get_max_ordering_for_project() + 1,
                'url' => $data['file_name'],
                'file_type' => $data['file_type'],
                'file_size' => $data['file_size'],
                'image_height' => $data['image_height'],
                'image_width' => $data['image_width']
            );
            $screen = $this->decorate_object($this->Screen->load($this->Screen->add($insert)));
        }

        /* Handle the download situation */
        $this->response($screen);
    }

    protected function decorate_object($object)
    {
        unset($object->deleted, $object->project_id);

        $hospots = $this->Hotspot->get_for_screen($object->id);
        $object->hotspots = $hospots;
        return $object;
    }
}

?>