<?php
use Swagger\Annotations as SWG;
use Aws\S3\S3Client;

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
 * @SWG\Property(name="comments",type="array",@SWG\Items("Comment"),description="The comments assigned to this screen")
 * @SWG\Property(name="hotspots",type="array",@SWG\Items("Hotspot"),description="The hotspots assigned to this screen")
 * @SWG\Property(name="drawings",type="array",@SWG\Items("Drawing"),description="The drawings assigned to this screen")
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
        $this->load->model(array('Project', 'Screen', 'Comment', 'Hotspot', 'Project_Statistic', 'Drawing'));
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
        validate_team_read(get_team_id());
        $project = validate_project_uuid($project_uuid);
        $screens = $this->Screen->get_for_project($project->id);
        $this->response($this->decorate_objects($screens));
    }

    public function project_post($project_uuid = '')
    {
        $project = validate_project_uuid($project_uuid);
        $screen = null;

        if (isset($_FILES['file'])) {
            $screen = $this->add_screen_upload($project);
        } else if ($this->post('url')) {
            $screen = $this->add_screen_url($project);
        }

        if (!$screen) {
            json_error('You must provide either a url or uploaded file for the screenshot.');
            exit;
        }

        if ($screen) {
            /* Add the activity item to indicate that a screen was added */
            activity_add_screen($screen->id, get_user_id());
        }

        /* Handle the download situation */
        $this->response($this->decorate_object($screen));
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
        validate_team_read(get_team_id());
        $screen = validate_screen_uuid($uuid);
        if ($action && $action === 'hotspots') {
            $hotspots = $this->Hotspot->get_for_screen($screen->id);
            $this->response(decorate_hotspots($hotspots));
        } else if ($action && $action === 'comments') {
            $comments = $this->Comment->get_for_screen($screen->id);
            $this->response(decorate_comments($comments));
        } else if ($action && $action === 'drawings') {
            $drawings = $this->Drawing->get_for_screen($screen->id);
            $this->response(decorate_drawings($drawings));
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

        /* Add the activity item to indicate that a screen was deleted */
        activity_delete_screen($screen->id, get_user_id());
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
     *     name="begin_x",
     *     description="The begin x property",
     *     paramType="form",
     *     required=false,
     *     type="integer"
     *     ),
     * @SWG\Parameter(
     *     name="begin_y",
     *     description="The begin y property",
     *     paramType="form",
     *     required=false,
     *     type="integer"
     *     ),
     * @SWG\Parameter(
     *     name="end_x",
     *     description="The end x property",
     *     paramType="form",
     *     required=false,
     *     type="integer"
     *     ),
     * @SWG\Parameter(
     *     name="end_y",
     *     description="The end y property",
     *     paramType="form",
     *     required=false,
     *     type="integer"
     *     ),
     * @SWG\Parameter(
     *     name="link_to",
     *     description="The link to property",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     )
     *   )
     * )
     */

    /**
     *
     * @SWG\Api(
     *   path="/screen/{screen_uuid}/comments/",
     *   description="API for screen actions",
     * @SWG\Operation(
     *    method="GET",
     *    nickname="List Comments",
     *    type="array[Comment]",
     *    summary="Returns a list of comments for the specified screen",
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
     *    type="Comments",
     *    nickname="Add Comments",
     *    summary="Create a new comment for the given screen",
     * @SWG\Parameter(
     *     name="screen_uuid",
     *     description="The unique ID of the screen",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="content",
     *     description="The comment content for the screen",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="is_task",
     *     description="Whether this comment should be assigned as a task or not",
     *     paramType="form",
     *     required=false,
     *     type="integer"
     *     ),
     * @SWG\Parameter(
     *     name="marker",
     *     description="Whether this comment should be marked as important or not",
     *     paramType="form",
     *     required=false,
     *     type="integer"
     *     ),
     * @SWG\Parameter(
     *     name="assignee_uuid",
     *     description="The uuid of the user this task should be assigned to",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="time",
     *     description="The time of the screen that the comment was added",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="begin_x",
     *     description="The begin x property",
     *     paramType="form",
     *     required=false,
     *     type="integer"
     *     ),
     * @SWG\Parameter(
     *     name="begin_y",
     *     description="The begin y property",
     *     paramType="form",
     *     required=false,
     *     type="integer"
     *     ),
     * @SWG\Parameter(
     *     name="end_x",
     *     description="The end x property",
     *     paramType="form",
     *     required=false,
     *     type="integer"
     *     ),
     * @SWG\Parameter(
     *     name="end_y",
     *     description="The end y property",
     *     paramType="form",
     *     required=false,
     *     type="integer"
     *     ),
     * @SWG\Parameter(
     *     name="left_x",
     *     description="The left x property",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     )
     *   )
     * )
     */

    /**
     *
     * @SWG\Api(
     *   path="/screen/{screen_uuid}/drawings/",
     *   description="API for screen actions",
     * @SWG\Operation(
     *    method="GET",
     *    nickname="List Drawing",
     *    type="array[Drawing]",
     *    summary="Returns a list of comments for the specified screen",
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
     *    type="Drawing",
     *    nickname="Add Drawing",
     *    summary="Create a new drawing for the given screen",
     * @SWG\Parameter(
     *     name="screen_uuid",
     *     description="The unique ID of the screen",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="data",
     *     description="The comment content for the screen",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */

    /**
     *
     * @SWG\Api(
     *   path="/screen/{screen_uuid}/comments/search",
     *   description="API for screen actions",        *
     * @SWG\Operation(
     *    method="POST",
     *    type="Comments",
     *    nickname="Search Comments",
     *    summary="Search a list of comments",
     * @SWG\Parameter(
     *     name="screen_uuid",
     *     description="The unique ID of the screen",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="filter",
     *     description="The filter (represented as an object) with terms to search for",
     *     paramType="form",
     *     required=true,
     *     type="CommentFilter"
     *     )
     *   )
     * )
     */
    public function screen_post($uuid = '', $action = '', $action2 = '')
    {
        $screen = validate_screen_uuid($uuid);
        if ($action && $action === 'hotspots') {
            $this->add_hotspot($screen);
        } else if ($action && $action === 'comments') {
            if ($action2 == 'search') {
                $this->search_comments($screen);
            } else {
                $this->add_comment($screen);
            }
        } else if ($action && $action === 'drawings') {
            $this->add_drawing($screen);
        } else {
            json_error('Invalid request, action \''.$action.'\' is not supported', null, 405);
        }
    }

    /**
     * Creates a new hotspot on the screen
     * @param $screen
     */
    private function add_hotspot($screen)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('time', 'Time', 'trim|xss_clean');
        $this->form_validation->set_rules('begin_x', 'Begin X', 'trim|xss_clean');
        $this->form_validation->set_rules('begin_y', 'Begin Y', 'trim|xss_clean');
        $this->form_validation->set_rules('end_x', 'End X', 'trim|xss_clean');
        $this->form_validation->set_rules('end_y', 'End Y', 'trim|xss_clean');
        $this->form_validation->set_rules('link_to', 'Link To', 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: '.validation_errors(' ', ' '));
        } else {
            $hotspot_id = $this->Hotspot->add(array(
                'screen_id' => $screen->id,
                'ordering' => $this->Hotspot->get_max_ordering_for_screen($screen->id) + 1,
                'creator_id' => get_user_id(),
                'time' => $this->post('time', TRUE),
                'begin_x' => $this->post('begin_x', TRUE),
                'begin_y' => $this->post('begin_x', TRUE),
                'end_x' => $this->post('end_x', TRUE),
                'end_y' => $this->post('end_y', TRUE),
                'link_to' => $this->post('link_to', TRUE)
            ));
            activity_add_hotspot_screen($hotspot_id);
            $hotspot = decorate_hotspot($this->Hotspot->load($hotspot_id));
            $this->response($hotspot);
        }
    }

    /**
     * Creates a new comment on the screen
     * @param $screen
     */
    private function add_comment($screen)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('content', 'Content', 'trim|required|xss_clean');
        $this->form_validation->set_rules('time', 'Time', 'trim|xss_clean');
        $this->form_validation->set_rules('begin_x', 'Begin X', 'trim|xss_clean');
        $this->form_validation->set_rules('begin_y', 'Begin Y', 'trim|xss_clean');
        $this->form_validation->set_rules('end_x', 'End X', 'trim|xss_clean');
        $this->form_validation->set_rules('end_y', 'End Y', 'trim|xss_clean');
        $this->form_validation->set_rules('left_x', 'Left X', 'trim|xss_clean');
        $this->form_validation->set_rules('is_task', 'Is Task', 'trim|integer|xss_clean');
        $this->form_validation->set_rules('marker', 'Marker', 'trim|integer|xss_clean');
        $this->form_validation->set_rules('assignee_uuid', 'Assignee', 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: '.validation_errors(' ', ' '));
        } else {
            $data = array(
                'screen_id' => $screen->id,
                'project_id' => $screen->project_id,
                'is_task' => intval($this->post('is_task', TRUE)),
                'marker' => intval($this->post('marker', TRUE)),
                'ordering' => $this->Comment->get_max_ordering_for_screen($screen->id) + 1,
                'creator_id' => get_user_id(),
                'time' => $this->post('time', TRUE),
                'begin_x' => $this->post('begin_x', TRUE),
                'begin_y' => $this->post('begin_x', TRUE),
                'end_x' => $this->post('end_x', TRUE),
                'end_y' => $this->post('end_y', TRUE),
                'left_x' => $this->post('left_x', TRUE),
                'content' => $this->post('content', TRUE)
            );

            $assignee_uuid = $this->post('assignee_uuid', TRUE);
            if ($assignee_uuid) {
                $assignee_id = $this->User->get_id($assignee_uuid);

                if (!$assignee_id) {
                    json_error('There is no user with that id to assign this task to');
                    return;

                }

                if (!$this->User->is_on_project($screen->project_id, $assignee_id)) {
                    json_error('You cannot assign a task to a user who is not assigned to this project');
                    return;
                }

                $data['assignee_id'] = $assignee_id;
                $data['is_task'] = 1;
            }

            $comment_id = $this->Comment->add($data);
            activity_add_comment_screen($comment_id);
            $this->Project_Statistic->comment_project($screen->project_id);
            $comment = decorate_comment($this->Comment->load($comment_id));
            $this->response($comment);
        }
    }


    /**
     * Creates a new drawing on the screen
     * @param $screen
     */
    private function add_drawing($screen)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('data', 'Data', 'trim');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: '.validation_errors(' ', ' '));
        } else {
            $drawing_id = $this->Drawing->add(array(
                'screen_id' => $screen->id,
                'ordering' => $this->Drawing->get_max_ordering_for_screen($screen->id) + 1,
                'creator_id' => get_user_id(),
                'data' => $this->post('data', TRUE)
            ));
            activity_add_drawing_screen($drawing_id);
            $drawing = decorate_drawing($this->Drawing->load($drawing_id));
            $this->response($drawing);
        }
    }

    /**
     * Provides the ability to search for a list of comments on a given screen
     * @param $screen
     */
    private function search_comments($screen)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('filter', 'Filter', 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: '.validation_errors(' ', ' '));
        } else {
            $filter = json_decode($this->post('filter', TRUE));
            $filter->screen_id = $screen->id;
            $comments = decorate_comments($this->Comment->search($filter));
            $this->response($comments);
        }
    }

    /**
     * Adds a screenshot to a project via a file upload.  Requires that the file be uploaded as 'upload'
     * @param $project
     */
    private function add_screen_upload($project)
    {
        $config = array(
            'upload_path' => $this->config->item('screen_upload_dir'),
            'allowed_types' => $this->config->item('screen_upload_types'),
            'max_size' => $this->config->item('max_screen_upload_size'),
            'encrypt_name' => true
        );

        /* Handle the file upload */
        $this->load->library('upload', $config);
        if ($this->upload->do_upload('file')) {
            $data = $this->upload->data();

            /* Upload to s3 */
            $client = S3Client::factory(array(
                'credentials' => array(
                    'key' => $this->config->item('s3_access_key_id'),
                    'secret' => $this->config->item('s3_secret')
                ),
                'region' => $this->config->item('s3_region'),
                'version' => $this->config->item('s3_version')
            ));
            $object = array(
                'Bucket' => $this->config->item('s3_bucket'),
                'Key' => $data['file_name'],
                'SourceFile' => $data['full_path'],
                'ACL' => 'public-read'
            );
            $result = $client->putObject($object);

            if ($result['ObjectURL']) {
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
                $screen = $this->Screen->load($this->Screen->add($insert));
                unlink($data['full_path']);
                return $screen;
            } else {
                log_message('info', '[File Add] putObject Result: '.print_r($result, TRUE));
                return json_error('File Upload to S3 Failed: ', $result);
            }
        } else {
            json_error($this->upload->display_errors());
            exit;
        }
    }

    /**
     * Adds a screenshot to a project via a file upload.  Requires that the file be uploaded as 'upload'
     * @param $project
     */
    private function add_screen_url($project)
    {
        $this->load->library('upload');
        /* encrypt the filename */

        $file_ext = $this->upload->get_extension($this->post('url'));
        if (!in_array(str_replace(".", "", $file_ext), explode("|", $this->config->item('screen_upload_types')))) {
            json_error("The image url is invalid.  Only ".implode(", ", explode("|", $this->config->item('screen_upload_types')))." are allowed.");
            exit;
        }
        $file = file_get_contents($this->post('url'));
        if ($file) {
            $file_name = md5(uniqid(mt_rand())).$file_ext;
            $full_path = $this->config->item('screen_upload_dir').$file_name;
            file_put_contents($full_path, $file);
            $file_size = filesize($full_path) / 1000;
            $file_dimensions = getimagesize($full_path);

            /* Upload to s3 */
            $client = S3Client::factory(array(
                'credentials' => array(
                    'key' => $this->config->item('s3_access_key_id'),
                    'secret' => $this->config->item('s3_secret')
                ),
                'region' => $this->config->item('s3_region'),
                'version' => $this->config->item('s3_version')
            ));
            $object = array(
                'Bucket' => $this->config->item('s3_bucket'),
                'Key' => $file_name,
                'SourceFile' => $full_path,
                'ACL' => 'public-read'
            );
            $result = $client->putObject($object);

            if ($result['ObjectURL']) {
                $insert = array(
                    'creator_id' => get_user_id(),
                    'project_id' => $project->id,
                    'ordering' => $this->Screen->get_max_ordering_for_project($project->id) + 1,
                    'url' => $file_name,
                    'file_size' => $file_size,
                    'image_height' => $file_dimensions[1],
                    'image_width' => $file_dimensions[0]
                );
                unlink($full_path);
                $screen = $this->Screen->load($this->Screen->add($insert));
                return $screen;
            } else {
                log_message('info', '[File Add] putObject Result: '.print_r($result, TRUE));
                return json_error('File Upload to S3 Failed: ', $result);
            }
        }
    }

    protected function decorate_object($object)
    {
        return decorate_screen($object);
    }
}

?>