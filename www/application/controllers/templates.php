<?php
use Swagger\Annotations as SWG;

/**
 *
 * @SWG\Model(id="Template",required="uuid")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Template (for public consumption)")
 * @SWG\Property(name="creator_uuid",type="string",description="The id of the user who created the template")
 * @SWG\Property(name="ordering",type="integer",description="The ordering of how the template should be displayed in the list of templates for this project")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this template was created")
 * @SWG\Property(name="image_width",type="float",description="The width of the image")
 * @SWG\Property(name="image_height",type="float",description="The height of the image")
 * @SWG\Property(name="file_size",type="float",description="The size of the image")
 * @SWG\Property(name="url",type="string",description="The url of the template image")
 * @SWG\Property(name="file_type",type="string",description="The file type of the template image")
 *
 *
 * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="2.0",
 *     resourcePath="/templates",
 *     basePath="http://conojoapp.scmreview.com/rest/templates"
 * )
 */
class Templates extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->validate_user();
        $this->load->helper('json');
        $this->load->model(array('Template'));
    }

    /**
     *
     * @SWG\Api(
     *   path="/",
     *   description="API for template actions",
     * @SWG\Operation(
     *    method="GET",
     *    nickname="Get Templates",
     *    type="array[Template]",
     *    summary="Returns a list of templates"
     *   ),
     * @SWG\Operation(
     *    method="POST",
     *    nickname="Add Template",
     *    type="Template",
     *    summary="Create a new template",
     * @SWG\Parameter(
     *     name="name",
     *     description="Name of the template",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="file",
     *     description="File Upload of the template",
     *     paramType="form",
     *     required=true,
     *     type="file"
     *     )
     *   )
     * )
     */
    public function index_get()
    {
        $templates = $this->Template->get_all();
        $this->response($this->decorate_objects($templates));
    }

    /**
     * Upload a new template file
     */
    public function index_post()
    {
        validate_admin();

        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $config = array(
                'upload_path' => $this->config->item('template_upload_dir'),
                'allowed_types' => $this->config->item('screen_upload_types'),
                'max_size' => $this->config->item('max_screen_upload_size'),
                'encrypt_name' => true
            );

            /* Handle the file upload */
            $this->load->library('upload', $config);
            if ($this->upload->do_upload('file')) {
                $data = $this->upload->data();
                $insert = array(
                    'creator_id' => get_user_id(),
                    'name' => $this->post('name', TRUE),
                    'ordering' => $this->Template->get_max_ordering() + 1,
                    'url' => $data['file_name'],
                    'file_type' => $data['file_type'],
                    'file_size' => $data['file_size'],
                    'image_height' => $data['image_height'],
                    'image_width' => $data['image_width']
                );
                $template = $this->decorate_object($this->Template->load($this->Template->add($insert)));

                /* Handle the download situation */
                $this->response($template);
            }  else {
                json_error($this->upload->display_errors());
                exit;
            }
        }
    }

    protected function decorate_object($object) {
        return decorate_template($object);
    }

}
?>