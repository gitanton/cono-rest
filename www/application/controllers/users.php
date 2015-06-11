<?php
use Swagger\Annotations as SWG;
use Aws\S3\S3Client;

/**
 * @SWG\Model(id="Response")
 * @SWG\Property(name="status",type="string",description="Either success or error")
 * @SWG\Property(name="message",type="string",description="The success/error message related to the response")
 * @SWG\Property(name="data",type="object",description="Miscellaneous data associated with the message or error")  *
 *
 * @SWG\Model(id="NotificationProjectSettings", required="uuid,notify")
 * @SWG\Property(name="uuid",type="string",description="The uuid of the project")
 * @SWG\Property(name="name",type="string",description="The name of hte project")
 * @SWG\Property(name="notify",type="boolean",description="Whether to notify them on that project")
 *
 * @SWG\Model(id="NotificationSettings")
 * @SWG\Property(name="notify_general",type="boolean",description="Either success or error")
 * @SWG\Property(name="notify_promitions",type="boolean",description="The success/error message related to the response")
 * @SWG\Property(name="projects",type="array",@SWG\Items("NotificationProjectSettings"),description="A list of projects the user belongs to and whether to notify them")
 *
 * @SWG\Model(id="User",required="uuid,username")
 * @SWG\Property(name="uuid",type="string",description="The unique ID of the User (for public use)")
 * @SWG\Property(name="fullname",type="string",description="The full name of the User")
 * @SWG\Property(name="email",type="string",description="The email address of the User")
 * @SWG\Property(name="username",type="string",description="The username of the User")
 * @SWG\Property(name="avatar",type="string",description="The avatar of the User")
 * @SWG\Property(name="last_login",type="string",format="date",description="The date/time of the last login of the user")
 * @SWG\Property(name="timezone",type="string",format="date",description="The timezone that the user belongs to")
 *
 * @SWG\Model(id="Invoice",required="date")
 * @SWG\Property(name="date",type="string",format="date",description="The date of the invoice")
 * @SWG\Property(name="period_start",type="string",format="date",description="The date of the start of the billing period")
 * @SWG\Property(name="period_end",type="string",format="date",description="The date of the end of the billing period")
 * @SWG\Property(name="subtotal",type="float",description="The subtotal of the invoice")
 * @SWG\Property(name="total",type="float",description="The total of the invoice")
 * @SWG\Property(name="paid",type="boolean",description="Whether the invoice was successful or not")
 * @SWG\Property(name="attempt_count",type="integer",description="The number of times payment was attempted")
 * @SWG\Property(name="amount_due",type="float",description="The total amount due for the billing cycle")
 * @SWG\Property(name="discount",type="float",description="The amount of any discount applied to the invoice")
 * @SWG\Property(name="tax_percent",type="string",description="The percent of tax applied to the invoice")
 * @SWG\Property(name="tax",type="float",description="The amount of tax applied to the invoice")
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
        json_error('You do not have permission for this.', null, 403);
        exit;
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
     *     name="timezone",
     *     description="Timezone of the user",
     *     paramType="form",
     *     required=false,
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
        $this->load->helper('notification');

        /* Validate add */
        $this->load->library('form_validation');
        $this->form_validation->set_rules('invite_id', 'Invite ID', 'trim|alpha_dash|xss_clean');
        $this->form_validation->set_rules('fullname', 'Full Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[5]|xss_clean|is_unique[user.username]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|valid_email|required|is_unique[user.email]');

        if ($this->form_validation->run() == FALSE) {
            $data = array();
            if(form_error('email') && form_error('username')) {
                $data['level'] = USER_SIGNUP_ERROR_USERNAME_EMAIL_LEVEL;
            } else if(form_error('email')) {
                $data['level'] = USER_SIGNUP_ERROR_EMAIL_LEVEL;
            } else if(form_error('username')) {
                $data['level'] = USER_SIGNUP_ERROR_USERNAME_LEVEL;
            }
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '), $data);
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
            if ($invite) {
                $this->process_invite($invite, $user);
            } else {
                $team_id = $this->Team->add(array(
                    'owner_id' => $user_id,
                    'name' => $user->fullname.'\'s Team'
                ));
            }

            /* Set the team on the session */
            if ($team_id) {
                $this->session->set_userdata(SESS_TEAM_ID, $team_id);
                $user->team_id = $team_id;
            }

            try {
                notify_new_user($user->id, $this->post('password', TRUE));
            } catch(Exception $e) {
                log_message('error', "Error while notifying new user: ".$e->getMessage());
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
    public function login_post()
    {
        $this->load->model(array('Team', 'Team_Invite', 'Project_Invite', 'Project', 'Subscription'));

        $this->load->library('form_validation');
        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[5]|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|xss_clean');
        $this->form_validation->set_rules('invite_key', 'Invite Key', 'trim|xss_clean');
        $this->form_validation->set_rules('invite_type', 'Invite Type', 'trim|xss_clean|callback_validate_invite_type');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
            exit;
        } else {

            $username = $this->post('username', TRUE);
            $password = $this->post('password', TRUE);
            $user = $this->User->login($username, $password);
            if ($user && $user->id) {
                session_clear();
                $invite = $this->validate_invite($user);
                if ($invite) {
                    $this->process_invite($invite, $user);
                }

                $this->session->set_userdata(SESS_USER_ID, $user->id);
                $team = $this->Team->get_active_for_user($user->id);
                if ($team) {
                    $this->session->set_userdata(SESS_TEAM_ID, $team->id);
                }
                $subscription = $this->Subscription->load_by_field('user_id', $user->id);
                if ($subscription && !$subscription->failed) {
                    $this->session->set_userdata(SESS_SUBSCRIPTION_ID, $subscription->id);
                }

                log_message('info', 'Login - User ID: ' . $user->id . ', Username: ' . $user->username);

                $this->User->record_login($user->id);

                /* Decorate the user with their free trial */
                $user->free_trial_expired = !get_subscription_id() && (add_day(FREE_TRIAL_LENGTH, $user->created) < now());
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
     *   path="/subscription",
     *   description="API for user actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="Response",
     *    summary="Adds/Updates the subscription for the current user",
     * @SWG\Parameter(
     *     name="token",
     *     description="The token received from stripe for this transaction",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="plan_id",
     *     description="The id of the plan that the user is signing up for",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="additional_users",
     *     description="The number of additional users that the user is signing up for",
     *     paramType="form",
     *     required=false,
     *     type="string"
     *     ),
     *   ),
     * @SWG\Operation(
     *    method="DELETE",
     *    type="Response",
     *    summary="Deletes/Cancels the user's subscription",
     *   )
     * )
     */
    public function subscription_post()
    {
        $this->validate_user();
        $this->load->model(array('Plan', 'Subscription'));

        $this->load->library('form_validation');
        $this->form_validation->set_rules('token', 'Token', 'trim|xss_clean');
        $this->form_validation->set_rules('additional_users', 'Password', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('plan_id', 'Plan ID', 'trim|numeric|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
            exit;
        } else {
            \Stripe\Stripe::setApiKey($this->config->item('stripe_private_key'));
            $plan_id = $this->post('plan_id', TRUE);
            $token = $this->post('token', TRUE);
            $additional_users = intval($this->post('additional_users', TRUE));

            $user = get_user();
            $plan = $this->Plan->load($plan_id);
            if ($plan) {
                $subscription = $this->Subscription->load_by_user_id($user->id);

                /* We are adding a new subscription for this user */
                if (!$subscription) {
                    try {

                        /* We need the token here, so throw if we don't have it */
                        if (!$token) {
                            json_error("Unable to create subscription without token");
                            exit;
                        }

                        $stripe_customer = \Stripe\Customer::create(array(
                            "source" => $token,
                            "email" => $user->email
                        ));
                        $stripe_subscription = $stripe_customer->subscriptions->create(array(
                            "plan" => $plan->stripe_plan_id
                        ));
                        $subscription_id = $this->Subscription->add(array(
                            'user_id' => $user->id,
                            'plan_id' => $plan->id,
                            'additional_users' => 0,
                            'stripe_customer_id' => $stripe_customer->id,
                            'stripe_subscription_id' => $stripe_subscription->id
                        ));

                        if($additional_users>0) {
                            $stripe_additional_subscription = $stripe_customer->subscriptions->create(array(
                                "plan" => STRIPE_PLAN_ADDITIONAL_USER,
                                "quantity" => $additional_users
                            ));

                            /* Handle additional users */
                            $this->Subscription->update($subscription_id, array(
                                'additional_users' => $additional_users,
                                'stripe_additional_subscription_id' => $stripe_additional_subscription->id
                            ));
                        }

                        json_success('Subscription successfully created!', array('subscription_id' => $subscription_id));
                        exit;
                    } catch (Exception $e) {
                        $error = '[Create Stripe Customer] Stripe_Customer::create Exception: ' . $e->getMessage();
                        log_message('info', $error);
                        loggly(array(
                            'error' => $e->getMessage(),
                            'text' => $error,
                            'method' => 'rest.users.subscription_post',
                            'actor_id' => $user->id
                        ));
                        json_error($e->getMessage());
                        exit;
                    }
                } else {
                    /* If they have an existing subscription, retrieve the customer and the subscription and update it */
                    try {
                        $stripe_customer = \Stripe\Customer::retrieve($subscription->stripe_customer_id);
                        $stripe_subscription = $stripe_customer->subscriptions->retrieve($subscription->stripe_subscription_id);
                        $stripe_subscription->plan = $plan->stripe_plan_id;
                        $stripe_subscription->prorate = false;
                        if ($token) {
                            $stripe_subscription->source = $token;
                        }
                        $stripe_subscription->save();

                        $this->Subscription->update($subscription->id, array(
                            'plan_id' => $plan_id,
                            'failed' => 0,
                            'additional_users' => $additional_users
                        ));

                        /* Handle additional users */
                        /* If they have an existing additional users subscription, just update it */
                        if($subscription->stripe_additional_subscription_id) {
                            $stripe_additional_subscription = $stripe_customer->subscriptions->retrieve($subscription->stripe_additional_subscription_id);
                            if($additional_users>0) {
                                $stripe_additional_subscription->quantity = $additional_users;
                                $stripe_additional_subscription->prorate = false;
                                $stripe_additional_subscription->save();

                                $this->Subscription->update($subscription->id, array(
                                    'additional_users' => $additional_users
                                ));
                            } else {
                                $stripe_additional_subscription->cancel();
                                $this->Subscription->update($subscription->id, array(
                                    'additional_users' => $additional_users,
                                    'stripe_additional_subscription_id' => null
                                ));
                            }
                        } else if($additional_users) {
                            /* Otherwise if they don't have one, but we are adding users, create the subscription */
                            $stripe_additional_subscription = $stripe_customer->subscriptions->create(array(
                                "plan" => STRIPE_PLAN_ADDITIONAL_USER,
                                "quantity" => $additional_users
                            ));

                            $this->Subscription->update($subscription->id, array(
                                'additional_users' => $additional_users,
                                'stripe_additional_subscription_id' => $stripe_additional_subscription->id
                            ));
                        }
                        json_success('Subscription successfully updated!');
                        exit;
                    } catch (Exception $e) {
                        $error = '[Update Stripe Subscription] Stripe_Customer::retrieve Exception: ' . $e->getMessage();
                        log_message('info', $error);
                        loggly(array(
                            'error' => $e->getMessage(),
                            'text' => $error,
                            'method' => 'rest.users.subscription_post',
                            'actor_id' => $user->id
                        ));
                        json_error($e->getMessage());
                        exit;
                    }
                }
            }
        }
        json_error('Unable to process subscription info');
    }

    public function subscription_delete() {
        $this->validate_user();
        $this->load->model(array('Plan', 'Subscription'));
        $this->load->helper('notification');
        \Stripe\Stripe::setApiKey($this->config->item('stripe_private_key'));
        $subscription = $this->Subscription->load_by_user_id(get_user_id());

        /* We are deleting the customer and subscription */
        if ($subscription) {
            try {
                $stripe_customer = \Stripe\Customer::retrieve($subscription->stripe_customer_id);
                $stripe_customer->delete();

                $this->Subscription->delete($subscription->id);
                notify_subscription_cancelled(get_user());
                json_success('Subscription successfully deleted!');
                exit;
            } catch (Exception $e) {
                $error = '[Delete Stripe Subscription] Stripe_Customer::retrieve Exception: ' . $e->getMessage();
                log_message('info', $error);
                loggly(array(
                    'error' => $e->getMessage(),
                    'text' => $error,
                    'method' => 'rest.users.subscription_delete',
                    'actor_id' => get_user_id()
                ));
                json_error($e->getMessage());
                exit;
            }
        }
        json_error('Unable to find subscription');
    }


    /**
     *
     * @SWG\Api(
     *   path="/billing_history",
     *   description="API for user actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="array[Invoice]",
     *    summary="Retrieve a user's billing history",
     *   )
     * )
     */
    public function billing_history_get() {
        $this->validate_user();
        $this->load->model(array('Plan', 'Subscription'));
        $this->load->helper('notification');
        \Stripe\Stripe::setApiKey($this->config->item('stripe_private_key'));
        $subscription = $this->Subscription->load_by_user_id(get_user_id());
        if($subscription) {
            try {
                $invoices = \Stripe\Invoice::all(array("customer" => $subscription->stripe_customer_id, "limit" => 12));
                //array_print($invoices);
            } catch(Exception $e) {
                log_message('error', 'Exception while retrieving billing history: '.$e->getMessage());
                json_error('We experienced an error while retrieving your billing history: '.$e->getMessage());
                return;
            }
            $this->response(decorate_invoices($invoices->data));
        } else {
            $this->response(array());
        }
    }


    /**
     *
     * @SWG\Api(
     *   path="/notifications",
     *   description="API for user actions",
     * @SWG\Operation(
     *    method="GET",
     *    type="NotificationSettings",
     *    summary="Retrieve a user's notification settings",
     *   ),
     *  @SWG\Operation(
     *    method="POST",
     *    type="NotificationSettings",
     *    summary="Set a user's notification settings",
     *  @SWG\Parameter(
     *     name="notifications",
     *     description="The token received from stripe for this transaction",
     *     paramType="form",
     *     required=false,
     *     type="NotificationSettings"
     *     )
     *   )
     * )
     */
    public function notifications_get() {
        $this->validate_user();
        $notification_settings = $this->User->get_notifications(get_user_id());
        $this->response($notification_settings);
    }

    public function notifications_post() {
        $this->validate_user();
        $this->load->library('form_validation');
        $this->form_validation->set_rules('notifications', 'Notifications', 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
            exit;
        } else {
            $notifications = $this->post('notifications', true);
            // Update the notifications on the user
            $this->User->set_notifications(get_user_id(), $notifications);

            $notification_settings = $this->User->get_notifications(get_user_id());
            $this->response($notification_settings);
        }
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
    public function logout_post()
    {
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
     *    summary="Returns a user that matches the given uuid.  If no uuid is provided, it will return the currently logged in user",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the user",
     *     paramType="path",
     *     required=false,
     *     type="string"
     *     )
     *   ),
     *
     * @SWG\Operation(
     *    method="DELETE",
     *    type="Response",
     *    summary="Deletes a user with the specified UUID",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="The unique ID of the user",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     */
    public function user_put($uuid = '')
    {
        $this->validate_user();
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
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $data = $this->get_put_fields($this->User->get_fields());
            $this->User->update_by_uuid($uuid, $data);
            $user = $this->decorate_object($this->User->load_by_uuid($uuid));
            $this->response($user);
            exit;
        }
    }


    /**
     *
     * @SWG\Api(
     *   path="/projects/{uuid}",
     *   description="API for user actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="Response",
     *    summary="Set the active projects for a user.  Any other projects on the current team will be removed from the user's access",
     * @SWG\Parameter(
     *     name="uuid",
     *     description="Unique ID of the user to update",
     *     paramType="path",
     *     required=true,
     *     type="string"
     *     ),
     * @SWG\Parameter(
     *     name="projects",
     *     description="A comma separated list of project uuids that this user can contribute to on this team",
     *     paramType="form",
     *     required=true,
     *     type="User"
     *     )
     *   ),
     * )
     *
     * Update the list of projects that a user can have access to on the current team.  Only team creator's can do this
     * so if the current user on the current team isn't the creator, we'll kick back an error.
     */
    public function projects_post($uuid = '')
    {
        $this->validate_user();
        $this->load->model(array('Team', 'Project'));
        $this->load->library('form_validation');
        $this->form_validation->set_rules('projects', 'Projects', 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            json_error('There was a problem with your submission: ' . validation_errors(' ', ' '));
        } else {
            $user = $this->User->load_by_uuid($uuid);
            if ($user) {
                $current_user = get_user();
                $team = $this->Team->load(get_team_id());
                $project_uuids = explode(",", $this->post('projects', TRUE));

                if ($team && $current_user->id == $team->owner_id) {

                    /* Remove the user from all projects on the team */
                    $this->Project->remove_for_user_team($user->id, $team->id);

                    foreach ($project_uuids as $project_uuid) {
                        $project = $this->Project->load_by_uuid(trim($project_uuid));

                        if ($project && $project->team_id == $team->id) {
                            /* Add the user to the project */
                            $this->Project->add_user($project->id, $user->id);
                            activity_user_join_project($project->id, $user->id);
                        }
                    }
                    json_success("Projects updated succesfully");
                    exit;
                } else {
                    json_error('You do not have the authorization to update the permissions of users on this team.');
                }
            }
        }

        json_error('Unable to update user with uuid of ' . $uuid);
    }

    /**
     * Returns a single user referenced by their uuid
     * @param string $uuid
     */
    public function user_get($uuid = '')
    {
        $this->validate_user();
        if (!$uuid) {
            $user = get_user();
        } else {
            $user = $this->User->load_by_uuid($uuid);
        }
        if (!$user) {
            json_error('There is no user with that id');
            exit;
        } else {
            $this->response($this->decorate_object($user));
        }
    }


    /**
     *
     * @SWG\Api(
     *   path="/forgot_password",
     *   description="API for user actions",
     * @SWG\Operation(
     *    method="POST",
     *    type="Response",
     *    summary="Send an email with a new password to a user",
     * @SWG\Parameter(
     *     name="email",
     *     description="The email of the user to reset the password for",
     *     paramType="form",
     *     required=true,
     *     type="string"
     *     )
     *   )
     * )
     *
     * Reorders the list of projects for a user
     */
    public function forgot_password_post() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');
        if ($this->form_validation->run() != FALSE) {
            $email = $this->input->post('email', TRUE);

            /** First try to load the account by username **/
            $user = $this->User->load_by_email($email);

            if (!$user) {

                json_error('There was no account found with that username/email address.  Please try again.');
                exit;
            }

            $new_password = $this->User->reset_password($user->id);
            $this->load->helper('notification');
            notify_reset_password($user, $new_password);

            log_message('info', '[Forgot Password] Sending Username/Password to ' . $user->username . ' <' . $user->email . '>');
            json_success('Your password has been reset and emailed to your email address.  You should receive it shortly.');
            exit;
        }
        json_error('Invalid email address');
    }

    /**
     * Deletes a user by its uuid
     * @param string $uuid
     */
    public function user_delete($uuid = '')
    {
        $this->validate_user();
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
            if ($type != INVITE_TYPE_PROJECT && $type != INVITE_TYPE_TEAM) {
                $this->form_validation->set_message('validate_invite_type', 'The %s is an invalid invite type.');
                return FALSE;
            }
        }
        return TRUE;
    }

    private function validate_invite($user = '')
    {
        $this->load->model(array('Team', 'Project'));
        $invite = null;
        $user_uuid = '';
        $user_id = '';

        if($user) {
            $user_uuid = $user->uuid;
            $user_id = $user->id;
        }

        $invite_key = $this->post('invite_key', TRUE);
        $invite_type = $this->post('invite_type', TRUE);
        if ($invite_key && $invite_type) {
            /* Team Invite */
            if ($invite_type == INVITE_TYPE_TEAM) {
                $invite = $this->Team_Invite->load_by_key($invite_key);
                validate_invite($invite);
                /* Validate that there is room to join the team */
                $team = $this->Team->load_fields($invite->team_id, 'owner_id');
                validate_user_add($team->owner_id);
            } /* Project Invite */
            else {
                $invite = $this->Project_Invite->load_by_key($invite_key);
                validate_invite($invite, $user_id);

                /* Validate that there is room to join the team */
                $project = $this->Project->load($invite->project_id);
                $team = $this->Team->load_fields($project->team_id, 'owner_id');
                validate_user_add($team->owner_id, $user_uuid);
            }
        }

        return $invite;
    }

    private function process_invite($invite, $user)
    {
        $this->load->helper('notification');
        /* Process Invites */
        $invite_key = $this->post('invite_key', TRUE);
        $invite_type = $this->post('invite_type', TRUE);

        if ($invite_key && $invite_type && $invite) {
            /* Team Invite */
            if ($invite_type == INVITE_TYPE_TEAM) {

                /* Add the user to the team */
                $this->Team->add_user($invite->team_id, $user->id);

                /* Update the invite so that the user is set on it */
                $this->Team_Invite->update($invite->id, array(
                    'user_id' => $user->id,
                    'used' => timestamp_to_mysqldatetime(now())
                ));
                activity_user_join_team($invite->team_id, $user->id);
                notify_team_invite_accepted($invite->id);
            } /* Project Invite */
            else {
                $project = $this->Project->load($invite->project_id);

                /* Add the user to the project */
                $this->Project->add_user($invite->project_id, $user->id);

                /* Look up the project to see if the user is already on the team, if not add them */
                if (!$this->User->is_on_team($project->team_id, $user->id)) {
                    $this->Team->add_user($project->team_id, $user->id);
                    activity_user_join_team($project->team_id, $user->id);
                }

                /* Update the invite so that the user is set on it */
                $this->Project_Invite->update($invite->id, array(
                    'user_id' => $user->id,
                    'used' => timestamp_to_mysqldatetime(now())
                ));
                activity_user_join_project($project->id, $user->id);
                notify_project_invite_accepted($invite->id);
            }
        }
    }

    /**
     *
     * @SWG\Api(
     *   path="/avatar",
     *   description="API for user actions",
     * @SWG\Operation(
     *    method="POST",
     *    nickname="Upload avatar",
     *    type="Screen",
     *    summary="Set the avatar for the current user",
     * @SWG\Parameter(
     *     name="file",
     *     description="File Upload of the avatar",
     *     paramType="form",
     *     required=false,
     *     type="file"
     *     )
     *   )
     * )
     */
    public function avatar_post()
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

            if($result['ObjectURL']) {
                $this->User->update(get_user_id(), array(
                    'avatar' => $data['file_name']
                ));
                unlink($data['full_path']);
                $user = $this->decorate_object(get_user());
                $this->response($user);
            } else {
                log_message('info', '[File Add] putObject Result: ' . print_r($result, TRUE));
                return json_error('File Upload to S3 Failed: ', $result);
            }
        } else {
            json_error($this->upload->display_errors());
            exit;
        }
    }
}

?>