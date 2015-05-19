<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Main extends User_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index() {
        $this->response(array());
    }

}