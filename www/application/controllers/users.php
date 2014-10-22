<?
use Swagger\Annotations as SWG;

/**
 *
 * @SWG\Model(id="User",required="uuid,username")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the User")
 * @SWG\Property(name="firstname",type="string",description="The first name of the User")
 * @SWG\Property(name="lastname",type="string",description="The last name of the User")
 * @SWG\Property(name="email",type="string",description="The email address of the User")
 * @SWG\Property(name="username",type="string",description="The username of the User")
 * @SWG\Property(name="last_login",type="string",format="date",description="The date/time of the last login of the user")
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
     *    type="array[User]",
     *    summary="Returns a list of current users (Will eventually require an admin level user)"
     *   )
     * )
     */
    public function index_get()
    {
        $users = $this->User->get_all();

        $this->response($this->decorate_objects($users));
    }

    /**
     *
     * @SWG\Api(
     *   path="/",
     *   description="API for adding a user",
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
     *     description="Username of the user (Should be at least five characters long)",
     *     paramType="body",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="password",
     *     description="Password of the user (Should be at least six characters long)",
     *     paramType="body",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */
    public function index_post()
    {
        /* Validate add */
        $this->load->library('form_validation');
        $this->form_validation->set_rules('firstname', 'First Name', 'trim|alpha|xss_clean');
        $this->form_validation->set_rules('lastname', 'Last Name', 'trim|alpha|xss_clean');
        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[5]|xss_clean|is_unique[user.username]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|valid_email|required');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: '.validation_errors(' ', ' '));
        } else {
            $data = array(
                'firstname' => $this->post('firstname', TRUE),
                'lastname' => $this->post('lastname', TRUE),
                'username' => $this->post('username', TRUE),
                'email' => $this->post('email', TRUE),
                'password' => $this->post('password', TRUE)
            );

            $user = $this->decorate_object($this->User->load($this->User->add($data)));
            $this->response($user);
        }
    }



    /**
     *
     * @SWG\Api(
     *   path="/user/{uuid}",
     *   description="API for adding a user",
     * @SWG\Operation(
     *    method="PUT",
     *    type="User",
     *    summary="Updates an existing user",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="Unique ID of the user",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="body",
     *     description="User object that needs to be updated",
     *     paramType="body",
     *     required=true,
     *     type="User"
     *     )
     *   ),
     *
     *
     * @SWG\Operation(
     *    method="GET",
     *    type="User",
     *    summary="Returns a user that matches the given id",
     *   @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the user",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */
    public function user_put($uuid='')
    {
        echo $this->put('firstname');
        exit;
        /* Validate update - have to copy the fields from put to $_POST for validation */
        $_POST['firstname'] = $this->put('firstname');
        $_POST['lastname'] = $this->put('lastname');
        $_POST['username'] = $this->put('username');
        $_POST['email'] = $this->put('email');

        $this->load->library('form_validation');
        $this->form_validation->set_rules('firstname', 'First Name', 'trim|alpha|xss_clean');
        $this->form_validation->set_rules('lastname', 'Last Name', 'trim|alpha|xss_clean');
        $this->form_validation->set_rules('username', 'Username', 'trim|min_length[5]|xss_clean|is_unique[user.username]');
        $this->form_validation->set_rules('password', 'Password', 'trim|min_length[6]|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|valid_email');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: '.validation_errors(' ', ' '));
        } else {
            $data = $this->get_put_fields($this->User->get_fields());
            array_print($data);
            $this->User->update_by_uuid($uuid, $data);
        }
    }

    public function user_get($uuid = '')
    {
        if (!$uuid) {
            json_error('uuid is required');
            exit;
        }
        $user = $this->User->load_by_uuid($uuid);
        if (!$user) {
            json_error('There is no user with that id');
            exit;
        } else {
            $this->response($this->decorate_object($user));
        }
    }

    protected function decorate_object($object)
    {
        return clean_user($object);
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