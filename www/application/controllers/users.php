<?
use Swagger\Annotations as SWG;

/**
 *
 * @SWG\Model(id="User",required="uuid,username")
 *
 * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="2.0",
 *     resourcePath="/users",
 *     basePath="http://conojoapp.scmreview.com/rest/users"
 * )
 */
class Users extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('json');
    }

    /**
     *
     * @SWG\Api(
     *   path="/",
     * @SWG\Operation(
     *    method="GET",
     *    type="array",
     *    summary="Returns a list of current users (Will eventually require an admin level user)"
     *   )
     * )
     */
    public function index_get()
    {
        $users = $this->User->get_all();

        $this->response($users);
    }

    /**
     *
     * @SWG\Api(
     *   path="/user",
     *   description="API for updating, retrieving a specific user",
     * @SWG\Operation(
     *    method="POST",
     *    type="User",
     *    summary="Registers a new user",
     * @SWG\Parameter(
     *     name="firstname",
     *     description="First Name of the user",
     *     paramType="body",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="lastname",
     *     description="Last Name of the user",
     *     paramType="body",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="email",
     *     description="Email address of the user",
     *     paramType="body",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="username",
     *     description="Username of the user",
     *     paramType="body",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */
    public function user_post()
    {
        $user = $this->User->blank();
        $this->response($user);
    }

    public function query_username_get()
    {
        $username = $this->input->get('username', TRUE);
        $user = $this->User->load_by_username($username, TRUE);

        $user_id = get_user_id();
        if ($this->input->get('uuid')) {
            $tmp_user = $this->User->load_by_uuid($this->input->get('uuid'));
            $user_id = $tmp_user->id;
        }

        if ($user && ($user->id != $user_id)) {
            echo json_encode("The username you are attempting to use is currently in use, please choose another");
        } else {
            echo "true";
        }
    }

    public function query_email_get()
    {
        $email = $this->input->get('email', TRUE);
        $user = $this->User->load_by_email($email);

        $user_id = get_user_id();
        if ($this->input->get('uuid')) {
            $tmp_user = $this->User->load_by_uuid($this->input->get('uuid'));
            $user_id = $tmp_user->id;
        }

        if ($user && ($user->id != $user_id)) {
            echo json_encode("The email address you are attempting to use is currently in use, please choose another");
        } else {
            echo "true";
        }
    }
}

?>