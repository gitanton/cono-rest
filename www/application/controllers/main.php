<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Main extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index_get() {
        $this->response(array());
    }

}