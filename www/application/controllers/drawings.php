<?php
use Swagger\Annotations as SWG;

/**
 *
 * @SWG\Model(id="Drawing",required="uuid")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Comment (for public consumption)")
 * @SWG\Property(name="video_uuid",type="string",description="The uuid of the video for whom the comment is provided")
 * @SWG\Property(name="screen_uuid",type="string",description="The uuid of the screen for whom the comment is provided")
 * @SWG\Property(name="ordering",type="integer",description="The ordering of how the comment should be displayed in the list of comments")
 * @SWG\Property(name="data",type="string",description="The json drawing data for the drawing")
 * @SWG\Property(name="creator_uuid",type="string",description="The id of the user who created the comment")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this comment was created")
 *
 * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="2.0",
 *     resourcePath="/drawings",
 *     basePath="http://conojoapp.scmreview.com/rest/drawings"
 * )
 */
class Drawings extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->validate_user();
        $this->load->helper('json');
        $this->load->model(array('Project', 'Screen', 'Drawing'));
    }

    /**
     *
     * @SWG\Api(
     *   path="/drawing/{uuid}",
     *   description="API for drawing actions",
     * @SWG\Operation(
     *    method="GET",
     *    nickname="Get Drawing",
     *    type="Drawing",
     *    summary="Returns a drawing for the given uuid",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the project",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   ),
     * @SWG\Operation(
     *    method="PUT",
     *    type="Drawing",
     *    summary="Updates an existing drawing",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="Unique ID of the drawing",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="body",
     *     description="Drawing object that needs to be updated",
     *     paramType="body",
     *     required=true,
     *     type="Drawing"
     *     )
     *   ),
     * @SWG\Operation(
     *    method="DELETE",
     *    nickname="Delete Drawing",
     *    type="Response",
     *    summary="Deletes a drawing with the specified UUID",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the drawing",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */
    public function drawing_get($uuid = '')
    {
        validate_team_read(get_team_id());
        $drawing = validate_drawing_uuid($uuid);
        $this->response($this->decorate_object($drawing));
    }

    public function drawing_put($uuid = '')
    {
        validate_team_read(get_team_id());
        $drawing = validate_drawing_uuid($uuid);
        $data = $this->get_put_fields($this->Drawing->get_fields());
        $this->Drawing->update_by_uuid($uuid, $data);
        $this->drawing_get($uuid);
    }

    /**
     * Deletes a drawing by its uuid
     * @param string $uuid
     */
    public function drawing_delete($uuid = '')
    {
        validate_team_read(get_team_id());
        $drawing = validate_drawing_uuid($uuid);
        activity_delete_drawing($drawing->id);
        $this->Drawing->delete($drawing->id);
        json_success("Drawing deleted successfully.");
    }

    protected function decorate_object($object)
    {
        return decorate_drawing($object);
    }
}

?>