<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once __DIR__ .'/../libraries/leclib.php';

/**
 * LenzCreative verify
 */
class Lecverify extends AdminController{
    public function __construct(){
        parent::__construct();
    }

    /**
     * index
     * @return void
     */
    public function index(){
        show_404();
    }

    /**
     * activate
     * @return json
     */
    public function activate()
    {
         $license_code = strip_tags(trim($_POST["purchase_key"]));
         $client_name = strip_tags(trim($_POST["username"]));

         $api = new ApprovifyLic();

         $msg = '';


         $res = array();
         $res['status'] = 1;
         $res['message'] = 'Nulled by anhduc.net';

             $res['original_url']= $this->input->post('original_url');
         

         echo json_encode($res);
    }
}