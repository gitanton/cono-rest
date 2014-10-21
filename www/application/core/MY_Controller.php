<?php

class MY_Controller extends CI_Controller
{

    var $data;
    var $user_id;
    var $user;

    public function __construct()
    {
        parent::__construct();

        $this->load->library('carabiner');

        /* On production, we use the minified, concated files produced by the packager ruby script.  If you
        * add anything here, make sure you add it to /assets/scripts/static-footer.js */
        if (!IS_TEST) {

        } else {

            $this->carabiner->css('stylesheets/lib/bootstrap.css');
            $this->carabiner->css('stylesheets/lib/glyphicons.css');
            $this->carabiner->css('stylesheets/lib/datepicker.css');
            $this->carabiner->css('stylesheets/screen.css?v='.VERSION);

            $this->carabiner->js('scripts/lib/loggly.tracker.js');
            $this->carabiner->js('scripts/lib/jquery/jquery.dateFormat-1.0.js');
            $this->carabiner->js('scripts/lib/jquery/jquery.validate.js');
            $this->carabiner->js('scripts/lib/jquery/jquery.validate.additional.methods.js');
            $this->carabiner->js('scripts/lib/accounting.js');
            $this->carabiner->js('scripts/lib/bootstrap.js');
            $this->carabiner->js('scripts/lib/modernizr.js');
            $this->carabiner->js('scripts/lib/underscore.js');
            $this->carabiner->js('scripts/lib/backbone.js');
            $this->carabiner->js('scripts/lib/jquery/jquery.dataTables.js');
            $this->carabiner->js('scripts/lib/jquery/jquery.dateFormat-1.0.js');
            $this->carabiner->js('scripts/lib/jquery/jquery.iframe-transport.js');
            $this->carabiner->js('scripts/lib/bootstrap-datepicker.js');

            $this->carabiner->js('scripts/app/util/template.cache.js?v='.VERSION);
            $this->carabiner->js('scripts/app/util/serialize.object.js?v='.VERSION);
            $this->carabiner->js('scripts/app/routers/Base.js?v='.VERSION);
            $this->carabiner->js('scripts/app/collections/Base.js?v='.VERSION);
            $this->carabiner->js('scripts/app/models/Base.js?v='.VERSION);
            $this->carabiner->js('scripts/app/views/Base.js?v='.VERSION);
            $this->carabiner->js('scripts/app/views/Confirm.js?v='.VERSION);
            $this->carabiner->js('scripts/app/views/Modal.js?v='.VERSION);
            $this->carabiner->js('scripts/app/views/BaseForm.js?v='.VERSION);
            $this->carabiner->js('scripts/app/views/BaseList.js?v='.VERSION);

            $header_js = array(
                array('scripts/lib/jquery/jquery-1.10.2.min.js'),
                array('scripts/app.js?v='.VERSION)
            );
        }
        $this->carabiner->js(site_detect_url('partials/all?v='.VERSION));
        $this->carabiner->group('header_js', array('js' => $header_js));

        $this->carabiner->group('iefix', array('css'=> array(
            array('stylesheets/ie.css?v='.VERSION)
        )));

        $this->data['flash_success'] = $this->session->flashdata('success');
        $this->data['flash_info'] = $this->session->flashdata('info');
        $this->data['flash_error'] = $this->session->flashdata('error');
    }

    /** OVERRIDE THESE **/
    protected function decorate_objects($objects)
    {
        return $objects;
    }

    protected function backbone_app($scripts, $minified_script = '') {

        /* On production, we use the minified, concated files produced by the packager ruby script.  If you
        * add anything here, make sure you add it to $minified_script in /assets/scripts/ */
        if(!IS_TEST && $minified_script) {
            $this->carabiner->js($minified_script.'?v='.VERSION);
        } else {
            /* Add all of the views */
            foreach ($scripts as $script) {
                $this->carabiner->js($script.'?v='.VERSION);
            }
        }
    }

    protected function validate()
    {
        $this->load->library('form_validation');
    }

    function setup_email($is_html = TRUE)
    {
        $config = array();
        if ($is_html)
            $config['mailtype'] = 'html';

        $config['protocol'] = 'smtp';
        $config['smtp_host'] = $this->config->item('smtp_host');
        $config['smtp_port'] = $this->config->item('smtp_port');
        $config['smtp_user'] = $this->config->item('notifications_user');
        $config['smtp_pass'] = $this->config->item('notifications_password');
        $config['smtp_timeout'] = 5;
        $config['charset'] = 'iso-8859-1';
        $config['wordwrap'] = TRUE;

        $this->load->library('email', $config);
        $this->email->set_newline("\r\n");
    }

}

?>