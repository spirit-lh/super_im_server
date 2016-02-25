<?php
header("Content-type: text/html; charset=utf-8");
header('Access-Control-Allow-Origin:*');
header('Content-type: text/json');

class user_info extends CI_Controller {
	/**
	 * 构造器
	 */
	public function __construct() {
		parent::__construct();
		$this -> load -> model('user/user_info_model');
	}

	/**
	 * 查询用户基本信息
	 */
	public function getUserBaseInfo() {
		$userID = isset($_GET['userID']) ? $_GET['userID'] : 0;
		$ownerID = isset($_GET['ownerID ']) ? $_GET['ownerID '] : 0;
        $userLat = isset($_GET['userLat'])?$_GET['userLat']:0;
        $userLon = isset($_GET['userLon'])?$_GET['userLon']:0;

		$userInfo = $this -> user_info_model -> getUserBaseInfo($userID,$ownerID,$userLat,$userLon);
		$visitorNum = $this -> user_info_model -> getVisitorNum($userID);
		$dynamicNum = $this -> user_info_model -> getUserDynamicNum($userID);
		$userDynamicImage = $this -> user_info_model -> getUserDynamicImage($userID);

		$json_data = "{\"user_info\":" . json_encode($userInfo) . "," . "\"visitorNum\":" . json_encode($visitorNum) . "," . "\"dynamicNum\":" . json_encode($dynamicNum) . "," . "\"userDynamicImage\":" . json_encode($userDynamicImage) . "}";
		echo $json_data;
	}

	public function listContact(){
		$condition= array();
		echo  $this -> user_info_model -> listContact($condition);
	}
	
	/**
	 * 用户登录
	 */
	public function check_login(){
		$username = isset($_POST['username']) ? $_POST['username'] : NULL;
		$password = isset($_POST['password']) ? $_POST['password'] : NULL;
		$userRow = $this->user_info_model->getUserByName($username);
		if($userRow){
			if($userRow['phone'] == $username && $userRow['pass'] == md5($password)){
				//更新登录时间
				$this->user_info_model->updatePassword(array('c_user_lastLoginTime' =>date('y-m-d h:i:s',time())),$username);
	
				echo '{"uid":"'.$userRow['uid'].'", "omcode":"'.$userRow['omcode'].'", "username":"'.$userRow['username'].'", "password":"'.md5($password).'","userImg":"'.$userRow['userImg'].'", "message":'.'"success"'.'}';
			}else if($userRow['phone'] != $username){
				echo '{"username":"'.$username.'", "password":"'.md5($password).'", "message":'.'"手机号错误！"'.'}';
			}else if($userRow['pass'] != md5($password)){
				echo '{"username":"'.$username.'", "password":"'.md5($password).'", "message":'.'"密码错误！"'.'}';
			}
		}else{
			echo '{"username":"'.$username.'", "password":"'.md5($password).'", "message":'.'"手机号和密码错误！"'.'}';
		}
	
	}
	/**
	 * 找回密码
	 */
	public function findpwd(){
		$username = isset($_POST['username']) ? $_POST['username'] : NULL;
		$password = isset($_POST['new_pwd']) ? $_POST['new_pwd'] : NULL;
		$smscode = isset($_POST['smscode']) ? $_POST['smscode'] : NULL;
	
	
		$userRow = $this->user_info_model->getUserByName($username);
		$result_sms = $this->Index_model->check_smscode($username,$smscode);
		$result_sms = 'success';
		if(!$userRow){
			echo '{"username":"'.$username.'", "password":"'.md5($password).'", "message":'.'"该手机号在系统中不存在！"'.'}';
		}else if($result_sms != 'success'){
			echo '{"username":"'.$username.'", "password":"'.md5($password).'", "message":"'.$result_sms.'"}';
		}else{
			$datas = array(
					'c_user_password' => md5($password),
					'c_user_lastLoginTime' =>date('y-m-d h:i:s',time())
			);
			$this->user_info_model->updatePassword($datas,$username);
			echo '{"uid":"'.$userRow['uid'].'", "omcode":"'.$userRow['omcode'].'", "username":"'.$userRow['username'].'", "password":"'.md5($password).'","userImg":"'.$userRow['userImg'].'", "message":'.'"success"'.'}';
		}
	
	
	}
	
	/**
	 * 检验用户ID合法性
	 */
	public function check_uid_legal(){
		$uid = isset($_POST['uid']) ? $_POST['uid'] : NULL;
		$userRow = $this->user_info_model->getUserNumsByID($uid);
		$json_data="{\"userRow\":".$userRow."}";
		echo $json_data;
	}
}
