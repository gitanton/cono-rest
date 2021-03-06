<?php
use Swagger\Annotations as SWG;

/**
 * @SWG\Model(id="Hotspot",required="uuid,data")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the Hotspot (for public consumption)")
 * @SWG\Property(name="screen_uuid",type="string",description="The uuid of the screen for whom the hotspot is provided")
 * @SWG\Property(name="video_uuid",type="string",description="The uuid of the video for whom the hotspot is provided")
 * @SWG\Property(name="ordering",type="integer",description="The ordering of how the screen should be displayed in the list of hotspots")
 * @SWG\Property(name="begin_x",type="integer",description="The begin x property")
 * @SWG\Property(name="begin_y",type="integer",description="The begin y property")
 * @SWG\Property(name="end_x",type="integer",description="The end x property")
 * @SWG\Property(name="end_y",type="integer",description="The end y property")
 * @SWG\Property(name="link_to",type="string",description="The link to property")
 * @SWG\Property(name="time",type="string",format="time",description="The time of the video for this hotspot")
 * @SWG\Property(name="creator_uuid",type="string",description="The id of the user who created the hotspot")
 * @SWG\Property(name="created",type="string",format="date",description="The date/time that this hotspot was created")
 *
 * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="2.0",
 *     resourcePath="/hotspots",
 *     basePath="http://conojoapp.scmreview.com/rest/hotspots"
 * )
 */
class Hotspots extends REST_Controller
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
     *   path="/hotspot/{uuid}",
     *   description="API for hotspot actions",
     * @SWG\Operation(
     *    method="GET",
     *    nickname="Get Hotspot",
     *    type="Hotspot",
     *    summary="Returns a hotspot for the given uuid",
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
     *    type="Hotspot",
     *    summary="Updates an existing hotspot",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="Unique ID of the hotspot",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="body",
     *     description="Hotspot object that needs to be updated",
     *     paramType="body",
     *     required=true,
     *     type="Hotspot"
     *     )
     *   ),
     * @SWG\Operation(
     *    method="DELETE",
     *    nickname="Delete Hotspot",
     *    type="Response",
     *    summary="Deletes a hotspot with the specified UUID",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the hotspot",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */
    public function hotspot_get($uuid = '')
    {
        validate_team_read(get_team_id());
        $hotspot = validate_hotspot_uuid($uuid);
        $this->response($this->decorate_object($hotspot));
    }

    public function hotspot_put($uuid = '')
    {
        validate_team_read(get_team_id());
        $hotspot = validate_hotspot_uuid($uuid);
        $data = $this->get_put_fields($this->Hotspot->get_fields());
        $this->Hotspot->update_by_uuid($uuid, $data);
        $this->hotspot_get($uuid);
    }

    /**
     * Deletes a hotspot by its uuid
     * @param string $uuid
     */
    public function hotspot_delete($uuid = '')
    {
        validate_team_read(get_team_id());
        $hotspot = validate_hotspot_uuid($uuid);
        activity_delete_hotspot($hotspot->id);
        $this->Hotspot->delete($hotspot->id);
        json_success("Hotspot deleted successfully.");
    }

    protected function decorate_object($object)
    {
        return decorate_hotspot($object);
    }
}

?>