<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Content-type: application/json");

/**
 * 首页页面控制器
 * Create by Cuikai 2015-12-05
 */
class Im_ajax extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('im');
		$this -> load -> model('user/user_info_model');
	}
	
	public function getIMToken()
	{
		$userid = $_POST['userid'];
		//$userid = 1;
		
		$result = $this->im->getToken($userid, $userid,'3');
		echo $result;
	}
}
