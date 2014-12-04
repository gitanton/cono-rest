<?
use Swagger\Annotations as SWG;

/**
 * @SWG\Model(id="Response")
 * @SWG\Property(name="status",type="string",description="Either success or error")
 * @SWG\Property(name="message",type="string",description="The success/error message related to the response")
 * @SWG\Property(name="data",type="object",description="Miscellaneous data associated with the message or error")
 *
 * @SWG\Model(id="User",required="uuid,username")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the User (for public use)")
 * @SWG\Property(name="fullname",type="string",description="The full name of the User")
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
     *   description="API for user actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="User",
     *    summary="Registers a new user.  If the post includes an invite id, it will try to validate that invite id against a team invitation and add them to the team.  Otherwise, it will create a new team for that user",
     * @SWG\Parameter(
     *     name="invite_key",
     *     description="The invite key that the user is using to join a team",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="invite_type",
     *     description="The invite type that the user is using (either 'team' or 'project')",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="fullname",
     *     description="Name of the user",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="email",
     *     description="Email address of the user",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="username",
     *     description="Username of the user (Should be at least five characters long)",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="password",
     *     description="Password of the user (Should be at least six characters long)",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */
    public function index_post()
    {
        $this->load->model(array('Team', 'Team_Invite', 'Project_Invite', 'Project'));

        /* Validate add */
        $this->load->library('form_validation');
        $this->form_validation->set_rules('invite_id', 'Invite ID', 'trim|alpha_dash|xss_clean');
        $this->form_validation->set_rules('fullname', 'Full Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[5]|xss_clean|is_unique[user.username]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|valid_email|required|is_unique[user.email]');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: '.validation_errors(' ', ' '));
        } else {
            $data = array(
                'fullname' => $this->post('fullname', TRUE),
                'username' => $this->post('username', TRUE),
                'email' => $this->post('email', TRUE),
                'password' => $this->post('password', TRUE)
            );

            $team_id = 0;

            /* Get the invite if there is an invite key/invite type on the request */
            $invite = $this->validate_invite();

            $user_id = $this->User->add($data);
            $user = $this->User->load($user_id);
            $this->session->set_userdata(SESS_USER_ID, $user->id);

            /* If the invite is not null, we will process the invite and add the new user to the team/project */
            if($invite) {
                $this->process_invite($invite, $user);
            } else {
                $team_id = $this->Team->add(array(
                    'owner_id' => $user_id
                ));
            }

            /* Set the team on the session */
            if($team_id) {
                $this->session->set_userdata(SESS_TEAM_ID, $team_id);
                $user->team_id = $team_id;
            }

            $this->response($this->decorate_object($user));
        }
    }

    /**
     *
     * @SWG\Api(
     *   path="/login",
     *   description="API for user actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="User",
     *    summary="Logs in a user",
     * @SWG\Parameter(
     *     name="username",
     *     description="Username of the user (Should be at least five characters long)",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="password",
     *     description="Password of the user (Should be at least six characters long)",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="invite_key",
     *     description="The invite key that the user is using to join a team",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="invite_type",
     *     description="The invite type that the user is using (either 'team' or 'project')",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     )
     *   )
     * )
     */
    public function login_post() {
        $this->load->model(array('Team', 'Team_Invite', 'Project_Invite', 'Project'));

        $this->load->library('form_validation');
        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[5]|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|xss_clean');
        $this->form_validation->set_rules('invite_key', 'Invite Key', 'trim|xss_clean');
        $this->form_validation->set_rules('invite_type', 'Invite Type', 'trim|xss_clean|callback_validate_invite_type');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: '.validation_errors(' ', ' '));
            exit;
        } else {
            $username = $this->post('username', TRUE);
            $password = $this->post('password', TRUE);
            $user = $this->User->login($username, $password);
            if ($user && $user->id) {

                $invite = $this->validate_invite($user->id);
                if($invite) {
                    $this->process_invite($invite, $user);
                }

                $this->session->set_userdata(SESS_USER_ID, $user->id);
                $team = $this->Team->get_active_for_user($user->id);
                if($team) {
                    $this->session->set_userdata(SESS_TEAM_ID, $team->id);
                }

                log_message('info', 'Login - User ID: ' . $user->id . ', Username: ' . $user->username);

                $this->User->record_login($user->id);
                $user = $this->decorate_object($user);
                $this->response($user);
                exit;
            }
        }
        json_error('The username/password you have entered are invalid.');
    }



    /**
     *
     * @SWG\Api(
     *   path="/logout",
     *   description="API for user actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="User",
     *    summary="Logs out the current user"
     *   )
     * )
     */
    public function logout_post() {
        $this->session->sess_destroy();
        json_success('You have been logged out successfully.');
    }


    /**
     *
     * @SWG\Api(
     *   path="/user/{uuid}",
     *   description="API for user actions",
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
     *    summary="Returns a user that matches the given uuid",
     *   @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the user",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   ),
     *
     *  @SWG\Operation(
     *    method="DELETE",
     *    type="Response",
     *    summary="Deletes a user with the specified UUID",
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
        /* Validate update - have to copy the fields from put to $_POST for validation */
        $_POST['fullname'] = $this->put('fullname');
        $_POST['username'] = $this->put('username');
        $_POST['email'] = $this->put('email');

        $this->load->library('form_validation');
        $this->form_validation->set_rules('fullname', 'Full Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('username', 'Username', 'trim|min_length[5]|xss_clean|is_unique[user.username]');
        $this->form_validation->set_rules('password', 'Password', 'trim|min_length[6]|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|valid_email');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: '.validation_errors(' ', ' '));
        } else {
            $data = $this->get_put_fields($this->User->get_fields());
            $this->User->update_by_uuid($uuid, $data);
        }
    }

    /**
     * Returns a single user referenced by their uuid
     * @param string $uuid
     */
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

    /**
     * Deletes a user by its uuid
     * @param string $uuid
     */
    public function user_delete($uuid = '')
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
            $this->User->delete($user->id);
            json_success("User deleted successfully.");
        }
    }

    protected function decorate_object($object)
    {
        return decorate_user($object);
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

    public function validate_invite_type($type = '')
    {
        if ($type) {
            if($type!=INVITE_TYPE_PROJECT && $type!=INVITE_TYPE_TEAM) {
                $this->form_validation->set_message('validate_invite_type', 'The %s is an invalid invite type.');
                return FALSE;
            }
        }
        return TRUE;
    }

    private function validate_invite($user_id = 0) {
        $invite = null;

        $invite_key = $this->post('invite_key', TRUE);
        $invite_type = $this->post('invite_type', TRUE);
        if($invite_key && $invite_type) {
            /* Team Invite */
            if($invite_type==INVITE_TYPE_TEAM) {
                $invite = $this->Team_Invite->load_by_key($invite_key);
                validate_invite($invite);
            }
            /* Project Invite */
            else {
                $invite = $this->Project_Invite->load_by_key($invite_key);
                validate_invite($invite, $user_id);
            }
        }

        return $invite;
    }

    private function process_invite($invite, $user) {
        /* Process Invites */
        $invite_key = $this->post('invite_key', TRUE);
        $invite_type = $this->post('invite_type', TRUE);
        if($invite_key && $invite_type && $invite) {
            /* Team Invite */
            if($invite_type==INVITE_TYPE_TEAM) {
                /* Add the user to the team */
                $this->Team->add_user($invite->team_id, $user->id);

                /* Update the invite so that the user is set on it */
                $this->Team_Invite->update($invite->id, array(
                    'user_id' => $user->id,
                    'used' => timestamp_to_mysqldatetime(now())
                ));
            }
            /* Project Invite */
            else {
                /* Add the user to the project */
                $this->Project->add_user($invite->project_id, $user->id);

                /* Look up the project to see if the user is already on the team, if not add them */
                $project = $this->Project->load($invite->project_id);
                if(!$this->User->is_on_team($project->team_id, $user->id)) {
                    $this->Team->add_user($project->team_id, $user->id);
                }

                /* Update the invite so that the user is set on it */
                $this->Project_Invite->update($invite->id, array(
                    'user_id' => $user->id,
                    'used' => timestamp_to_mysqldatetime(now())
                ));
            }
        }
    }
}

?>