<?php
use Swagger\Annotations as SWG;

/**
 * @SWG\Model(id="Activity",required="uuid,title,content")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Activity (for public consumption)")
 * @SWG\Property(name="title",type="integer",description="The title of the activity")
 * @SWG\Property(name="content",type="string",description="The description of what happened with this content")
 * @SWG\Property(name="creator_uuid",type="string",description="The id of the user who created the activity")
 * @SWG\Property(name="project_uuid",type="string",description="The project that has this activity")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this activity was created")
 *
 * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="2.0",
 *     resourcePath="/activities",
 *     basePath="http://conojoapp.scmreview.com/rest/activities"
 * )
 */
class Activities extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->validate_user();
        $this->load->helper('json');
        $this->load->model(array('Team','Project', 'Activity'));
    }

    /**
     *
     * @SWG\Api(
     *   path="/team/{team_uuid}",
     *   description="API for activity actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[Activity]",
     *    summary="Returns a list of the current activities that belong to the team in descending chronological order",
     *    @SWG\Parameter(
     *       name="page",
     *       description="The starting page # of the activities (defaults to 0)",
     *       paramType="query",
     *       required=false,
     *       type="integer"
     *     ),
     *    @SWG\Parameter(
     *       name="limit",
     *       description="The number of results to return per page (defaults to 20)",
     *       paramType="query",
     *       required=false,
     *       type="integer"
     *     ),
     *    @SWG\Parameter(
     *       name="team_uuid",
     *       description="The UUID of the team that this activity is attached to. If not provided, will use the current active team",
     *       paramType="path",
     *       required=false,
     *       type="string"
     *     ),
     *   )
     * )
     */
    public function team_get($uuid = '')
    {
        if($uuid) {
            $team = validate_team_uuid($uuid);
        } else {
            $team = $this->Team->load(get_team_id());
        }
        validate_team_read($team->id);
        $activities = $this->Activity->get_for_team($team->id, $this->get('page', TRUE), $this->get('limit', TRUE));
        $this->response($this->decorate_objects($activities));
    }

    /**
     *
     * @SWG\Api(
     *   path="/project/{project_uuid}",
     *   description="API for activity actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[Activity]",
     *    summary="Returns a list of the current activities that belong to the project in descending chronological order",
     *    @SWG\Parameter(
     *       name="page",
     *       description="The starting page # of the activities (defaults to 0)",
     *       paramType="query",
     *       required=false,
     *       type="integer"
     *     ),
     *    @SWG\Parameter(
     *       name="limit",
     *       description="The number of results to return per page (defaults to 20)",
     *       paramType="query",
     *       required=false,
     *       type="integer"
     *     ),
     *    @SWG\Parameter(
     *       name="project_uuid",
     *       description="The UUID of the project that this activity is attached to",
     *       paramType="path",
     *       required=true,
     *       type="string"
     *     ),
     *   )
     * )
     */
    public function project_get($uuid = '')
    {
        $project = validate_project_uuid($uuid);
        validate_team_read(get_team_id());
        $activities = $this->Activity->get_for_project($project->id, $this->get('page', TRUE), $this->get('limit', TRUE));
        $this->response($this->decorate_objects($activities));
    }

    protected function decorate_object($object)
    {
        return decorate_activity($object);
    }
}