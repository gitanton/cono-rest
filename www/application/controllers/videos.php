<?php
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
 * @SWG\Property(name="video_uuid",type="string",description="The uuid of the video for whom the comment is provided")
 * @SWG\Property(name="screen_uuid",type="string",description="The uuid of the screen for whom the comment is provided")
 * @SWG\Property(name="ordering",type="integer",description="The ordering of how the comment should be displayed in the list of comments")
 * @SWG\Property(name="content",type="string",description="The content of the comment")
 * @SWG\Property(name="begin_x",type="integer",description="The begin x property")
 * @SWG\Property(name="begin_y",type="integer",description="The begin y property")
 * @SWG\Property(name="end_x",type="integer",description="The end x property")
 * @SWG\Property(name="end_y",type="integer",description="The end y property")
 * @SWG\Property(name="left_x",type="string",description="The left x property")
 * @SWG\Property(name="marker",type="integer",description="Whether this comment is marked")
 * @SWG\Property(name="is_task",type="integer",description="Whether this is a task or not")
 * @SWG\Property(name="assignee_uuid",type="integer",description="Who the task is assigned to")
 * @SWG\Property(name="time",type="string",format="time",description="The time of the video for this comment")
 * @SWG\Property(name="data",type="string",description="The json data for the html5 canvas object")
 * @SWG\Property(name="creator_uuid",type="string",description="The id of the user who created the comment")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this comment was created")
 *
 * @SWG\Model(id="CommentFilter")
 * @SWG\Property(name="begin_x",type="integer",description="The begin x property")
 * @SWG\Property(name="begin_y",type="integer",description="The begin y property")
 * @SWG\Property(name="end_x",type="integer",description="The end x property")
 * @SWG\Property(name="end_y",type="integer",description="The end y property")
 * @SWG\Property(name="left_x",type="string",description="The left x property")
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
        $this->load->model(array('Project', 'Video', 'Comment', 'Hotspot', 'Project_Statistic'));
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
     *     description="The time of the video that the comment was added",
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
     *   path="/video/{video_uuid}/comments/search",
     *   description="API for video actions",        *
     * @SWG\Operation(
     *    method="POST",
     *    type="Comments",
     *    nickname="Search Comments",
     *    summary="Search a list of comments",
     * @SWG\Parameter(
     *     name="video_uuid",
     *     description="The unique ID of the video",
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
    public function video_post($uuid = '', $action = '', $action2 = '')
    {
        $video = validate_video_uuid($uuid);
        if ($action && $action === 'hotspots') {
            $this->add_hotspot($video);
        } else if ($action && $action === 'comments') {
            if($action2=='search') {
                $this->search_comments($video);
            } else {
                $this->add_comment($video);
            }
        } else {
            json_error('Invalid request, action \''.$action.'\' is not supported', null, 405);
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
        $this->form_validation->set_rules('begin_x', 'Begin X', 'trim|xss_clean');
        $this->form_validation->set_rules('begin_y', 'Begin Y', 'trim|xss_clean');
        $this->form_validation->set_rules('end_x', 'End X', 'trim|xss_clean');
        $this->form_validation->set_rules('end_y', 'End Y', 'trim|xss_clean');
        $this->form_validation->set_rules('link_to', 'Link To', 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $hotspot_id = $this->Hotspot->add(array(
                'video_id' => $video->id,
                'ordering' => $this->Hotspot->get_max_ordering_for_video($video->id) + 1,
                'creator_id' => get_user_id(),
                'time' => $this->post('time', TRUE),
                'begin_x' => $this->post('begin_x', TRUE),
                'begin_y' => $this->post('begin_x', TRUE),
                'end_x' => $this->post('end_x', TRUE),
                'end_y' => $this->post('end_y', TRUE),
                'link_to' => $this->post('link_to', TRUE),
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
        $this->form_validation->set_rules('begin_x', 'Begin X', 'trim|xss_clean');
        $this->form_validation->set_rules('begin_y', 'Begin Y', 'trim|xss_clean');
        $this->form_validation->set_rules('end_x', 'End X', 'trim|xss_clean');
        $this->form_validation->set_rules('end_y', 'End Y', 'trim|xss_clean');
        $this->form_validation->set_rules('left_x', 'Left X', 'trim|xss_clean');
        $this->form_validation->set_rules('is_task', 'Is Task', 'trim|integer|xss_clean');
        $this->form_validation->set_rules('marker', 'Marker', 'trim|integer|xss_clean');
        $this->form_validation->set_rules('assignee_uuid', 'Assignee', 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $data = array(
                'video_id' => $video->id,
                'project_id' => $video->project_id,
                'is_task' => intval($this->post('is_task', TRUE)),
                'marker' => intval($this->post('marker', TRUE)),
                'data' => $this->post('data',TRUE),
                'ordering' => $this->Comment->get_max_ordering_for_video($video->id) + 1,
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

                if (!$this->User->is_on_project($video->project_id, $assignee_id)) {
                    json_error('You cannot assign a task to a user who is not assigned to this project');
                    return;
                }

                $data['assignee_id'] = $assignee_id;
                $data['is_task'] = 1;
            }
            $comment_id = $this->Comment->add($data);
            activity_add_comment_video($comment_id);
            $this->Project_Statistic->comment_project($video->project_id);
            $comment = decorate_comment($this->Comment->load($comment_id));
            $this->response($comment);
        }
    }

    /**
     * Provides the ability to search for a list of comments on a given screen
     * @param $screen
     */
    private function search_comments($video)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('filter', 'Filter', 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $filter = json_decode($this->post('filter', TRUE));
            $filter->video_id = $video->id;
            $comments = decorate_comments($this->Comment->search($filter));
            $this->response($comments);
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

            if($result['ObjectURL']) {

                $insert = array(
                    'creator_id' => get_user_id(),
                    'project_id' => $project->id,
                    'ordering' => $this->Video->get_max_ordering_for_project($project->id) + 1,
                    'url' => $data['file_name'],
                    'file_type' => $data['file_type'],
                    'file_size' => $data['file_size']
                );
                $video = $this->decorate_object($this->Video->load($this->Video->add($insert)));
                unlink($data['full_path']);
                return $video;
            } else {
                log_message('info', '[File Add] putObject Result: ' . print_r($result, TRUE));
                return json_error('File Upload to S3 Failed: ', $result);
            }
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

            if($result['ObjectURL']) {
                $insert = array(
                    'creator_id' => get_user_id(),
                    'project_id' => $project->id,
                    'ordering' => $this->Video->get_max_ordering_for_project($project->id) + 1,
                    'url' => $file_name,
                    'file_size' => $file_size
                );
                $video = $this->decorate_object($this->Video->load($this->Video->add($insert)));
                unlink($full_path);
                return $video;
            }else {
                log_message('info', '[File Add] putObject Result: ' . print_r($result, TRUE));
                return json_error('File Upload to S3 Failed: ', $result);
            }
        }
    }

    protected function decorate_object($object)
    {
        return decorate_video($object);
    }
}

?>