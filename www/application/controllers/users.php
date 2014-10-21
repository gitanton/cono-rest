<?
class Users extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('json');
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