<?
use Swagger\Annotations as SWG;

/**
 *
 * @SWG\Model(id="Video",required="uuid")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Video (for public consumption)")
 * @SWG\Property(name="creator_uuid",type="string",description="The id of the user who created the video")
 * @SWG\Property(name="ordering",type="integer",description="The ordering of how the video should be displayed in the list of videos for this project")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this video was created")
 * @SWG\Property(name="file_size",type="float",description="The size of the image")
 * @SWG\Property(name="url",type="string",description="The url of the video")
 * @SWG\Property(name="file_type",type="string",description="The file type of the video")
 * @SWG\Property(name="project_uuid",type="string",description="The uuid of the project for whom the video is provided")
 * @SWG\Property(name="comments",type="array",@SWG\Items("Comment"),description="The comments assigned to this video")
 * @SWG\Property(name="hotspots",type="array",@SWG\Items("HotSpot"),description="The hotspots assigned to this video")
 *
 * @SWG\Model(id="Comment",required="uuid")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Comment (for public consumption)")
 * @SWG\Property(name="screen_uuid",type="string",description="The uuid of the screen for whom the comment is provided")
 * @SWG\Property(name="video_uuid",type="string",description="The uuid of the video for whom the comment is provided")
 * @SWG\Property(name="ordering",type="integer",description="The ordering of how the comment should be displayed in the list of comments")
 * @SWG\Property(name="content",type="string",description="The content of the comment")
 * @SWG\Property(name="time",type="string",format="time",description="The time of the video for this comment")
 * @SWG\Property(name="data",type="string",description="The json data for the html5 canvas object")
 * @SWG\Property(name="creator_uuid",type="string",description="The id of the user who created the comment")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this comment was created")
 *
 *
 * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="2.0",
 *     resourcePath="/videos",
 *     basePath="http://conojoapp.scmreview.com/rest/videos"
 * )
 */
class Videos extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->validate_user();
        $this->load->helper('json');
        $this->load->model(array('Project', 'Video', 'Comment', 'Hotspot'));
    }

    /**
     *
     * @SWG\Api(
     *   path="/project/{project_uuid}",
     *   description="API for video actions",
     * @SWG\Operation(
     *    method="GET",
     *    nickname="Get Videos",
     *    type="array[Video]",
     *    summary="Returns a list of videos for the specified project",
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
     *    nickname="Add Video",
     *    type="Video",
     *    summary="Create a new video for the given project",
     * @SWG\Parameter(
     *     name="project_uuid",
     *     description="The unique ID of the project",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="file",
     *     description="File Upload of the video",
     *     paramType="form",
     *     required=false,
     *     type="file"
     *     ),
     * @SWG\Parameter(
     *     name="url",
     *     description="A url for where to fetch the image for the video",
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
        $videos = $this->Video->get_for_project($project->id);
        $this->response($this->decorate_objects($videos));
    }

    public function project_post($project_uuid = '')
    {
        $project = validate_project_uuid($project_uuid);
        $video = null;

        if (isset($_FILES['file'])) {
            $video = $this->add_video_upload($project);
        } else if ($this->post('url')) {
            $video = $this->add_video_url($project);
        }

        if (!$video) {
            json_error('You must provide either a url or uploaded file for the video.');
            exit;
        }

        /* Handle the download situation */
        $this->response($video);
    }


    /**
     *
     * @SWG\Api(
     *   path="/video/{video_uuid}",
     *   description="API for video actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="Video",
     *    nickname="Get Video",
     *    summary="Returns a video specified by the given uuid",
     * @SWG\Parameter(
     *     name="video_uuid",
     *     description="The unique ID of the video",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   ),
     *
     * @SWG\Operation(
     *    method="DELETE",
     *    nickname="Delete Video",
     *    type="Response",
     *    summary="Deletes a video with the specified UUID",
     * @SWG\Parameter(
     *     name="video_uuid",
     *     description="The unique ID of the video",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */

    /**
     * Returns either a video or a list of hotspots for that video depending on what the action is
     * @param string $uuid
     * @param string $action
     */
    public function video_get($uuid = '', $action = '')
    {
        $video = validate_video_uuid($uuid);
        if ($action && $action === 'hotspots') {
            $hotspots = $this->Hotspot->get_for_video($video->id);
            $this->response(decorate_hotspots($hotspots));
        } else if ($action && $action === 'comments') {
            $comments = $this->Comment->get_for_video($video->id);
            $this->response(decorate_comments($comments));
        } else {
            $this->response($this->decorate_object($video));
        }
    }

    /**
     * Deletes a video by its uuid
     * @param string $uuid
     */
    public function video_delete($uuid = '')
    {
        $video = validate_video_uuid($uuid);

        $this->Video->delete($video->id);
        json_success("Video deleted successfully.");
    }


    /**
     *
     * @SWG\Api(
     *   path="/video/{video_uuid}/hotspots/",
     *   description="API for video actions",
     * @SWG\Operation(
     *    method="GET",
     *    nickname="List Hotspots",
     *    type="array[Hotspot]",
     *    summary="Returns a list of hotspots for the specified video",
     * @SWG\Parameter(
     *     name="video_uuid",
     *     description="The unique ID of the video",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   ),
     * @SWG\Operation(
     *    method="POST",
     *    type="Hotspot",
     *    nickname="Add Hotspot",
     *    summary="Create a new hotspot for the given video",
     * @SWG\Parameter(
     *     name="video_uuid",
     *     description="The unique ID of the video",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="time",
     *     description="The time for the hotspot",
     *     paramType="form",
     *     required=false,
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

    /**
     *
     * @SWG\Api(
     *   path="/video/{video_uuid}/comments/",
     *   description="API for video actions",
     * @SWG\Operation(
     *    method="GET",
     *    nickname="List Comments",
     *    type="array[Comment]",
     *    summary="Returns a list of comments for the specified video",
     * @SWG\Parameter(
     *     name="video_uuid",
     *     description="The unique ID of the video",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   ),
     * @SWG\Operation(
     *    method="POST",
     *    type="Comments",
     *    nickname="Add Comments",
     *    summary="Create a new comment for the given video",
     * @SWG\Parameter(
     *     name="video_uuid",
     *     description="The unique ID of the video",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="content",
     *     description="The comment content for the video",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="time",
     *     description="The time of the video that the comment was added",
     *     paramType="form",
     *     required=false,
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
    public function video_post($uuid = '', $action = '')
    {
        $video = validate_video_uuid($uuid);
        if ($action && $action === 'hotspots') {
            $this->add_hotspot($video);
        } else if ($action && $action === 'comments') {
            $this->add_comment($video);
        }
    }

    /**
     * Creates a new hotspot on the video
     * @param $video
     */
    private function add_hotspot($video)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('time', 'Time', 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $hotspot_id = $this->Hotspot->add(array(
                'video_id' => $video->id,
                'ordering' => $this->Hotspot->get_max_ordering_for_video($video->id) + 1,
                'creator_id' => get_user_id(),
                'time' => $this->post('time', TRUE),
                'data' => $this->post('data', TRUE)
            ));
            activity_add_hotspot_video($hotspot_id);
            $hotspot = decorate_hotspot($this->Hotspot->load($hotspot_id));
            $this->response($hotspot);
        }
    }

    /**
     * Creates a new comment on the video
     * @param $video
     */
    private function add_comment($video)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('content', 'Content', 'trim|required|xss_clean');
        $this->form_validation->set_rules('time', 'Time', 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $comment_id = $this->Comment->add(array(
                'video_id' => $video->id,
                'project_id' => $video->project_id,
                'data' => $this->post('data',TRUE),
                'ordering' => $this->Comment->get_max_ordering_for_video($video->id) + 1,
                'creator_id' => get_user_id(),
                'time' => $this->post('time', TRUE),
                'content' => $this->post('content', TRUE)
            ));
            activity_add_comment($comment_id);
            $comment = decorate_comment($this->Comment->load($comment_id));
            $this->response($comment);
        }
    }

    /**
     * Adds a videoshot to a project via a file upload.  Requires that the file be uploaded as 'upload'
     * @param $project
     */
    private function add_video_upload($project)
    {
        $config = array(
            'upload_path' => $this->config->item('video_upload_dir'),
            'allowed_types' => $this->config->item('video_upload_types'),
            'max_size' => $this->config->item('max_video_upload_size'),
            'encrypt_name' => true
        );

        /* Handle the file upload */
        $this->load->library('upload', $config);
        if ($this->upload->do_upload('file')) {
            $data = $this->upload->data();
            $insert = array(
                'creator_id' => get_user_id(),
                'project_id' => $project->id,
                'ordering' => $this->Video->get_max_ordering_for_project($project->id) + 1,
                'url' => $data['file_name'],
                'file_type' => $data['file_type'],
                'file_size' => $data['file_size']
            );
            $video = $this->decorate_object($this->Video->load($this->Video->add($insert)));
            return $video;
        } else {
            json_error($this->upload->display_errors());
            exit;
        }
    }

    /**
     * Adds a video to a project via a file upload.  Requires that the file be uploaded as 'upload'
     * @param $project
     */
    private function add_video_url($project)
    {
        $this->load->library('upload');
        /* encrypt the filename */

        $file_ext = $this->upload->get_extension($this->post('url'));
        if (!in_array(str_replace(".", "", $file_ext), explode("|", $this->config->item('video_upload_types')))) {
            json_error("The video url is invalid.  Only " . implode(", ", explode("|", $this->config->item('video_upload_types'))) . " are allowed.");
            exit;
        }
        $file = file_get_contents($this->post('url'));
        if ($file) {
            $file_name = md5(uniqid(mt_rand())) . $file_ext;
            $full_path = $this->config->item('video_upload_dir') . $file_name;
            file_put_contents($full_path, $file);
            $file_size = filesize($full_path) / 1000;

            $insert = array(
                'creator_id' => get_user_id(),
                'project_id' => $project->id,
                'ordering' => $this->Video->get_max_ordering_for_project($project->id) + 1,
                'url' => $file_name,
                'file_size' => $file_size
            );
            $video = $this->decorate_object($this->Video->load($this->Video->add($insert)));
            return $video;
        }
    }

    protected function decorate_object($object)
    {
        return decorate_video($object);
    }
}

?>