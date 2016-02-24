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

	//查看用户信息
	public function getUserInfo() {
		$userID = isset($_GET['userID']) ? $_GET['userID'] : 0;

		$uid = isset($_GET['uid']) ? $_GET['uid'] : 0;
        
        $userLat = isset($_GET['userLat'])?$_GET['userLat']:0;
        $userLon = isset($_GET['userLon'])?$_GET['userLon']:0;

		$userInfo = $this -> user_info_model -> getUserBaseInfo($userID,$uid,$userLat,$userLon);
		$imageList = $this -> user_info_model -> getUserImages($userID);
		$dynamicNum = $this -> user_info_model -> getUserDynamicNum($userID);
		$userDynamicImage = $this -> user_info_model -> getUserDynamicImage($userID);
		$userKeyWord = $this -> user_info_model -> getUserKeyWords($userID);

		//保存查看我的信息的人的记录
		if ($userID != $uid) {
			$this -> user_info_model -> saveUserVisitor($userID, $uid);
		}

		$json_data = "{\"user_info\":" . json_encode($userInfo) . "," . "\"dynamicNum\":" . json_encode($dynamicNum) . "," . "\"userDynamicImage\":" . json_encode($userDynamicImage) . "," . "\"userKeyWord\":" . json_encode($userKeyWord) . "," . "\"image_list\":" . json_encode($imageList) . "}";
		echo $json_data;
	}

	//保存用户信息修改
	public function saveUserInfo() {
		$userID = isset($_POST['userID']) ? $_POST['userID'] : 0;

		$userName = isset($_POST['userName']) ? $_POST['userName'] : '';
		$userAge = isset($_POST['userAge']) ? $_POST['userAge'] : '';
		$userAstro = isset($_POST['']) ? $_POST['userAstro'] : '';
		$userBrithday = isset($_POST['userBrithday']) ? $_POST['userBrithday'] : '';
		$userSign = isset($_POST['userSign']) ? $_POST['userSign'] : '';
		$userEmstate = isset($_POST['userEmstate']) ? $_POST['userEmstate'] : '';
		$userHome = isset($_POST['userHome']) ? $_POST['userHome'] : '';
		$userArea = isset($_POST['userArea']) ? $_POST['userArea'] : '';
		$userRemark = isset($_POST['userRemark']) ? $_POST['userRemark'] : '';

		$userKeyWord = isset($_POST['userKeyWords']) ? $_POST['userKeyWords'] : '';

		$userImage = isset($_POST['userImages']) ? $_POST['userImages'] : '';

		//更新用户信息
		$userData = array('c_user_name' => $userName, 'c_user_age' => $userAge, 'c_user_con' => $userAstro, 'c_user_birthday' => $userBrithday, 'c_user_sign' => $userSign, 'c_user_emstate' => $userEmstate, 'c_user_home' => $userHome, 'c_user_area' => $userArea, 'c_user_remark' => $userRemark);
		$this -> db -> update('t_user_base', $userData, array('c_user_id' => $userID));
		//插入用户关键字
		$this -> user_info_model -> deleteUserKeyWord($userID);
		$userKeyWords = explode(",", $userKeyWord);
		for ($i = 0; $i < count($userKeyWords); $i++) {
			if (isset($userKeyWords[$i]) && $userKeyWords[$i] != ' ' && $userKeyWords[$i] != '' && $userKeyWords[$i] != null) {
				$data = array('c_user_id' => $userID, 'c_key_keyword' => $userKeyWords[$i]);
				$this -> user_info_model -> insertUserKeyWord($data);
			}
		}
		$t = time();
		$imgType01 = "data:image/jpg;base64,";
		$imgType02 = "data:image/pjpeg;base64,";
		$imgType03 = "data:image/jpeg;base64,";
		$imgType04 = "data:image/gif;base64,";
		$imgType05 = "data:image/png;base64,";
		if (strpos($userImage, $imgType01) >= 0 || strpos($userImage, $imgType02) >= 0 || strpos($userImage, $imgType03) >= 0) {
			$imgTypeNew = 'jpg';
	} else if (strpos($userImage, $imgType04) >= 0) {
			$imgTypeNew = 'gif';
		} else if (strpos($userImage, $imgType05) >= 0) {
			$imgTypeNew = 'png';
		}
		$imgPath = 'img/user/'.$t.'.'.$imgTypeNew;
		$userData = base64_decode(str_replace('data:image/'.$imgTypeNew.';base64,','',$userImage));
		file_put_contents($imgPath, $userData);
		
		$data = array('c_image_url'=>$imgPath,
		               'c_user_id'=>$userID);
		$this -> user_info_model ->saveUserImage($data);

		$json_data = "{\"result\":\"success\"}";
		echo $json_data;
	}

	//查询用户动态信息
	public function getUserDynamic() {
		$userID = isset($_GET['userID']) ? $_GET['userID'] : 0;

		$searchCondition = array("userID" => $userID, "cur_page" => 1, "page_num" => 20);

		$userDynamicList = $this -> user_info_model -> getUserDynamicList($searchCondition);

		$json_data = "{\"userDynamicList\":" . json_encode($userDynamicList) . "}";
		echo $json_data;
	}

	//查询黑名单
	public function getDefriendList() {
		$userID = isset($_GET['userID']) ? $_GET['userID'] : 0;

		$deFriend = $this -> user_info_model -> getDefriendList($userID);

		$json_data = "{\"deFriend\":" . json_encode($deFriend) . "}";
		echo $json_data;
	}

	//查询看过我的人
	public function getVisitorList() {
		$userID = isset($_GET['userID']) ? $_GET['userID'] : 0;

		$searchCondition = array("userID" => $userID, "cur_page" => 1, "page_num" => 20);

		$visitorList = $this -> user_info_model -> getVisitorList($searchCondition);

		$visitorNum = $this -> user_info_model -> getVisitorNum($userID);

		$visitorDynamicNum = $this -> user_info_model -> getVisitorDynamicNum($userID);

		$json_data = "{\"visitorList\":" . json_encode($visitorList) . "," . "\"visitorNum\":" . json_encode($visitorNum) . "," . "\"visitorDynamicNum\":" . json_encode($visitorDynamicNum) . "}";
		echo $json_data;
	}

	//删除看过我的人员记录
	public function deleteUserVisitor() {
		$userID = isset($_POST['userID']) ? $_POST['userID'] : 0;

		$this -> user_info_model -> deleteUserVisitor($userID);
		
		$json_data = "{\"result\":\"success\"}";
		echo $json_data;
	}

	//查看过我的动态的人
	public function getVisitorDynamic() {
		$userID = isset($_GET['userID']) ? $_GET['userID'] : 0;

		$searchCondition = array("userID" => $userID, "cur_page" => 1, "page_num" => 20);

		$visitorList = $this -> user_info_model -> getVisitorDynamicList($searchCondition);

		$visitorNum = $this -> user_info_model -> getVisitorNum($userID);

		$visitorDynamicNum = $this -> user_info_model -> getVisitorDynamicNum($userID);

		$json_data = "{\"visitorList\":" . json_encode($visitorList) . "," . "\"visitorNum\":" . json_encode($visitorNum) . "," . "\"visitorDynamicNum\":" . json_encode($visitorDynamicNum) . "}";
		echo $json_data;
	}

	//删除看过我的动态的人员记录
	public function deleteUserDynamicVisitor() {
		$userID = isset($_POST['userID']) ? $_POST['userID'] : 0;

		$this -> user_info_model -> deleteUserDynamicVisitor($userID);
		
		$json_data = "{\"result\":\"success\"}";
		echo $json_data;
	}

	//查询用户图片信息
	public function getUserImages() {
		$userID = isset($_GET['userID']) ? $_GET['userID'] : 0;
		$imageList = $this -> user_info_model -> getUserImages($userID);
		$json_data = "{\"image_list\":" . json_encode($imageList) . "}";
		echo $json_data;
	}

	//更新用户姓名信息
	public function updateUserInfo_userName() {
		$userName = isset($_POST['userName']) ? $_POST['userName'] : NULL;
		$userID = isset($_POST['userID']) ? $_POST['userID'] : NULL;

		$this -> db -> update('t_user_base', array('c_user_name' => $userName), array('c_user_id' => $userID));

	}

	//获取用户密码
	public function getUserPassWord() {
		$userID = isset($_GET['userID']) ? $_GET['userID'] : 0;

		$user_passWord = $this -> user_info_model -> getUserPassWord($userID);

		echo "{\"user_passWord\":" . json_encode($user_passWord) . "}";
	}

	//修改用户密码
	public function updateUserPassWord() {
		$password = isset($_POST['password']) ? $_POST['password'] : NULL;
		$userID = isset($_POST['userID']) ? $_POST['userID'] : NULL;

		$this -> db -> update('t_user_base', array('c_user_password' => $password), array('c_user_id' => $userID));

		echo "{\"result\":" . '1' . "}";
	}

	//保存用户信息（经纬度）
	public function saveUpdateUserInfoLon() {
		$userID = isset($_POST['userID']) ? $_POST['userID'] : 0;

		$userLon = isset($_POST['userLon']) ? $_POST['userLon'] : '';
		$userLat = isset($_POST['userLat']) ? $_POST['userLat'] : '';
		$userLoginDate = date('y-m-d h:i:s', time());
		//更新用户信息
		$userData = array('c_user_lon' => $userLon, 'c_user_lat' => $userLat, 'c_user_lastLoginTime' => $userLoginDate);
		$this -> db -> update('t_user_base', $userData, array('c_user_id' => $userID));
		$json_data = "{\"result\":\"success\"}";
		echo $json_data;
	}

	//拉黑用户
	public function insertDefriend() {
		$userID = isset($_POST['userID']) ? $_POST['userID'] : 0;
		$toUserID = isset($_POST['toUserID']) ? $_POST['toUserID'] : 0;
		
		$this->user_info_model->deleteDefriend($toUserID,$userID);
		
		$data = array('c_from_user_id' => $userID, 'c_to_user_id' => $toUserID, 'c_rel_type' => '1', 'c_in_time' => date('Y-m-d H:i:s', time()));
		$this -> user_info_model -> insertDefriend($data);

		echo "{\"result\":\"success\"}";
	}
	//解除黑名单
	public function deleteDefriend(){
		$userID = isset($_GET['userID']) ? $_GET['userID'] : 0;
		$uid = isset($_GET['uid']) ? $_GET['uid'] : 0;
		
		$this->user_info_model->deleteDefriend($userID,$uid);
		
		echo "{\"result\":\"success\"}";
	}
	//关注用户
	public function addRelation(){
		$formUserID = isset($_POST['formUserID']) ? $_POST['formUserID'] : 0;
		$toUserID =isset($_POST['toUserID']) ? $_POST['toUserID'] : 0;
		$reName =isset($_POST['reName']) ? $_POST['reName'] : NULL;
		
		
		
		$exitRelation  =  $this->user_info_model->exitRelation($toUserID,$formUserID);
		
		
		if($exitRelation>0){
			//如果对方也关注了我，则删除
			//$this->user_info_model->deleteRelation($toUserID,$formUserID);
			$data1=array('c_from_user_id'=>$formUserID,
			            'c_to_user_id'=>$toUserID,
						'c_rel_type'=>'2',
						'c_remark_name'=>$reName,
						'c_in_time'=>date('y-m-d h:i:s', time()));
			$this->user_info_model->insertRelation($data1);
			$this->user_info_model->updateFriendState($toUserID,$formUserID);
		}else{
			//如果对方没有关注我，则直接插入
			$data=array('c_from_user_id'=>$formUserID,
			            'c_to_user_id'=>$toUserID,
						'c_rel_type'=>'1',
						'c_remark_name'=>$reName,
						'c_in_time'=>date('y-m-d h:i:s', time()));
			$this->user_info_model->insertRelation($data);
		}
       echo "{\"result\":" . json_encode($exitRelation) . "}";
	}
	
	/**
	 * 保存微信账号绑定
	 */
	public function saveWeiXinBind(){
		$uid = isset($_POST['uid']) ? $_POST['uid'] : null;
		$dynamicToken = isset($_POST['dynamicToken']) ? $_POST['dynamicToken'] : null;
		$openid = isset($_POST['openid']) ? $_POST['openid'] : null;
		// 		$nickname = isset($_POST['nickname']) ? $_POST['nickname'] : null;
		// 		$sex = isset($_POST['sex']) ? $_POST['sex'] : null;
		// 		$headimgurl = isset($_POST['headimgurl']) ? $_POST['headimgurl'] : null;
		$datas = array(
				'c_login_type'=>'weixin',
				'c_login_openid'=>$openid,
				'c_login_token'=>$dynamicToken,
				'c_login_time'=>date('y-m-d h:i:s',time()),
				'c_user_id'=>$uid
		);
		$this->user_info_model->saveOMBind($datas);
		echo '{"message":'.'"success"'.'}';
	}
	
	/**
	 * 保存通讯录绑定
	 */
	public function openContacts(){
		//1、解析json将通讯录持久化到数据库
		$uid = isset($_POST['uid']) ? $_POST['uid'] : 0;
		$data = isset($_POST['result']) ? $_POST['result'] : null;
		$all = json_decode($data);
		$contacts = $all->contacts;
	
		$results = array();
		$arr_index = 0;
	
		foreach ($contacts as $person){
			$numbers = $person->phones;
			$fullName = $person->fullName;
				
			$phone = "";
			foreach ($numbers as $ph){
				if(isset($ph->手机)){
					$phone = $ph->手机;
				}
			}
				
			if (! empty ($phone)) {
				$results [$arr_index] = array (
						'c_owner' => $uid,
						'c_name' => $fullName,
						'c_phone'=>$phone
				);
				$arr_index ++;
			}
		}
		if(!empty($results)){
			$this->user_info_model->deleteUserContacts($uid);
			$this->user_info_model->saveUserContacts($results, $uid);
		}
		echo $this->user_info_model->searchUserContcts($uid);
	}
	/**
	 * 查询绑定后的通讯录
	 */
	public function searchHavenContacts(){
		$uid = isset($_POST['uid']) ? $_POST['uid'] : 0;
		echo $this->user_info_model->searchUserContcts($uid);
	}
	/**
	 * 解绑通讯录
	 */
	public  function cancelContactsBind(){
		$uid =  $_POST['uid'];
		$this->user_info_model->cancelContactsBind($uid);
		echo '{"message":'.'"success"'.'}';
	}
	/**
	 * 检测用户绑定的账号
	 */
	public function searchUserBind(){
		$uid =  $_POST['uid'];
		echo $this->user_info_model->searchUserBind($uid);
	}
	
	/**
	 * 修改用户头像
	 */
	public function updateHeadPortrait(){
		$uid = isset($_POST['uid']) ? $_POST['uid'] : 0;
		$t=time();
		$imgData =  isset($_POST['imageUrl']) ? $_POST['imageUrl'] : NULL;
		$imgType01 = "data:image/jpg;base64,";
		$imgType02 = "data:image/pjpeg;base64,";
		$imgType03 = "data:image/jpeg;base64,";
		$imgType04 = "data:image/gif;base64,";
		$imgType05 = "data:image/png;base64,";
		if(strpos($imgData,$imgType01) >= 0 || strpos($imgData,$imgType02) >= 0 || strpos($imgData,$imgType03) >= 0){
			$imgTypeNew = 'jpg';
		}else if(strpos($imgData,$imgType04) >= 0){
			$imgTypeNew = 'gif';
		}else if(strpos($imgData,$imgType05) >= 0){
			$imgTypeNew = 'png';
		}
		$imgPath = 'img/headPortrait/'.$t.'.'.$imgTypeNew;
		$imgCommunity = base64_decode(str_replace('data:image/'.$imgTypeNew.';base64,', '', $imgData));
		file_put_contents($imgPath, $imgCommunity);
		$row = $this->user_info_model->updateHeadPortrait($uid, $imgPath);
		if($row > 0){
			echo '{"message":'. json_encode($imgPath) .'}';
		}else{
			echo '{"message":'.'"error"'.'}';
		}
	}
	
	public function listContact(){
		$condition= array();
		echo  $this -> user_info_model -> listContact($condition);
	}
}
