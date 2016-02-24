<?php
/*
 * 用户基本信息
 */
class user_info_model extends CI_Model {
	//构造器
	public function __construct() {
		$this -> load -> database();

	}

	/**
	 * 查询用户基本信息
	 */
	function getUserBaseInfo($userID, $ownerID,$userLat, $userLon) {
		$sql = 'SELECT e.*,get_relation('.$userID.','.$ownerID.') as c_relation, date_format( e.c_user_createtime, \'%Y-%m-%d\' ) AS c_user_createtimeF, l.c_level_name, l.c_level_image, timestampdiff( MINUTE, e.c_user_lastLoginTime, now()) AS beforeMin, round( 6378.138 * 2 * asin( sqrt( pow( sin(( ?* pi() / 180 - e.c_user_lat * pi() / 180 ) / 2 ), 2 ) + cos( ?* pi() / 180 ) * cos( e.c_user_lat * pi() / 180 ) * pow( sin(( ?* pi() / 180 - e.c_user_lon * pi() / 180 ) / 2 ), 2 ))) * 1000 / 1000, 2 ) AS distance FROM t_user_base e LEFT JOIN t_user_level l ON e.c_user_level = l.c_level_code WHERE c_user_id = ?';
		$query = $this -> db -> query($sql, array($userLat, $userLat, $userLon, intval($userID)));
		if ($query -> num_rows() > 0) {
			return $query -> row_array();
		} else {
			return 0;
		}
	}

	/**
	 * 看过我的人数
	 */
	function getVisitorNum($userID) {
		$sql = 'SELECT count(*) as c_visitorNum
		        FROM
		               t_user_user_visitor r,
		               t_user_base b,
		               t_user_level l 
		        WHERE r.c_visitor_user_id = b.c_user_id 
		              AND b.c_user_level = l.c_level_code 
		              AND r.c_user_id =' . $userID;
		$query = $this -> db -> query($sql);

		return $query -> row() -> c_visitorNum;
	}

	/**
	 * 看过我的评论的人数
	 */
	function getVisitorDynamicNum($userID) {
		$sql = 'SELECT count(*) as c_visitorNum 
		        FROM  t_user_base b,
		              t_user_dynamic_visitor r ,
		              t_user_level l
		        WHERE b.c_user_id = r.c_visitor_id
		          AND b.c_user_level = l.c_level_code 
		          AND r.c_user_id =' . $userID;

		$query = $this -> db -> query($sql);

		return $query -> row() -> c_visitorNum;
	}

	/**
	 * 看过我的人列表
	 */
	function getVisitorList($searchCondition) {
		$cur_page = $searchCondition["cur_page"];
		$page_num = $searchCondition["page_num"];
		$seach_index = ($cur_page - 1) * $page_num;

		$userID = $searchCondition["userID"];

		$sql = 'SELECT b.c_user_id,
		               b.c_user_name,
		               b.c_user_image,
		               b.c_user_age,
		               b.c_user_gender,
		               b.c_user_level,
		               TIMESTAMPDIFF(MINUTE,r.c_visitor_time,SYSDATE()) AS  c_visitor_time,
		               TIMESTAMPDIFF(MINUTE, b.c_user_lastLoginTime, SYSDATE()) AS c_login_time,
		               b.c_user_sign,
		               l.c_level_image
		        FROM
		               t_user_user_visitor r,
		               t_user_base b,
		               t_user_level l 
		        WHERE r.c_visitor_user_id = b.c_user_id 
		              AND b.c_user_level = l.c_level_code 
		              AND r.c_user_id =?';
		$sql .= ' order by r.c_visitor_time limit ?,?';
		$query = $this -> db -> query($sql, array(intval($userID), intval($seach_index), intval($page_num)));
		return $query -> result();
	}

	//保存看过我的人的记录
	function saveUserVisitor($userID, $uid) {
		$this -> db -> where('c_user_id', $userID);
		$this -> db -> where('c_visitor_user_id', $uid);
		$this -> db -> delete('t_user_user_visitor');
		$data = array('c_user_id' => $userID, 'c_visitor_user_id' => $uid, 'c_visitor_time' => date('Y-m-d H:i:s', time()));
		$this -> db -> insert('t_user_user_visitor', $data);
	}

	/**
	 * 删除看过我的人员列表
	 */
	public function deleteUserVisitor($userID) {
		$this -> db -> where('c_user_id', $userID);
		$this -> db -> delete('t_user_user_visitor');
	}

	/**
	 * 查看过我的动态的人
	 */
	function getVisitorDynamicList($searchCondition) {
		$cur_page = $searchCondition["cur_page"];
		$page_num = $searchCondition["page_num"];
		$seach_index = ($cur_page - 1) * $page_num;

		$userID = $searchCondition["userID"];

		$sql = 'SELECT b.c_user_id,
		               b.c_user_name,
		               b.c_user_image,
		               b.c_user_age,
		               b.c_user_gender,
		               b.c_user_level,
		               TIMESTAMPDIFF(MINUTE, r.c_visitor_time, SYSDATE()) AS c_visitor_time,
		               TIMESTAMPDIFF(MINUTE, b.c_user_lastLoginTime,SYSDATE()) AS c_login_time,
		               b.c_user_sign,
		               l.c_level_image 
		        FROM  t_user_base b,
		              t_user_dynamic_visitor r ,
		              t_user_level l
		        WHERE b.c_user_id = r.c_visitor_id
		          AND b.c_user_level = l.c_level_code 
		          AND r.c_user_id = ? ';
		$sql .= 'order by r.c_visitor_time limit ?,?';
		$query = $this -> db -> query($sql, array(intval($userID), intval($seach_index), intval($page_num)));
		return $query -> result();

	}

	/**
	 * 删除看过我动态人员列表
	 */
	public function deleteUserDynamicVisitor($userID) {
		$this -> db -> where('c_user_id', $userID);
		$this -> db -> delete('t_user_dynamic_visitor');
	}

	/**
	 * 查看 用户动态总数
	 */
	function getUserDynamicNum($userID) {
		$sql = 'select count(*) as dynamicNum from  t_personnel_dynamic WHERE c_personnel_id =' . $userID;
		$query = $this -> db -> query($sql);
		return $query -> row() -> dynamicNum;
	}

	/**
	 * 查看用户动态列表信息
	 */
	function getUserDynamicList($searchCondition) {
		$cur_page = $searchCondition["cur_page"];
		$page_num = $searchCondition["page_num"];
		$seach_index = ($cur_page - 1) * $page_num;

		$userID = $searchCondition["userID"];

		$sql = 'SELECT d.c_id,
		              d.c_dynamic_content,
		              b.c_user_id,
		              b.c_user_name,
		              b.c_user_age,
		              b.c_user_image,
		              b.c_user_gender,
		              l.c_level_image,
		              r.c_read,
		              r.c_praise,
		              GROUP_CONCAT(i.c_dynamic_image) AS c_dynamic_image,
		              TIMESTAMPDIFF(MINUTE, d.c_createtime,SYSDATE()) AS c_login_time,
		              COUNT(n.c_id) AS c_user_praise
		        FROM
		              t_user_base b ,
		              t_user_level l,
		              t_personnel_read r,
		              t_personnel_image i,
		              t_personnel_dynamic d
		        LEFT JOIN t_personnel_read_relation n ON d.c_id= n.c_dynamic_id AND n.c_status =\'2\' AND n.c_user_id =?
		        WHERE d.c_personnel_id = b.c_user_id 
		          AND b.c_user_level = l.c_level_code
		          AND d.c_id = r.c_dynamic_id
		          AND i.c_dynamic_id = d.c_id
		          AND c_personnel_id = ? 
		        GROUP BY d.c_id 
		        ORDER BY d.c_createtime DESC,i.c_dynamic_addtime  DESC LIMIT ?,?';
		$query = $this -> db -> query($sql, array(intval($userID), intval($userID), intval($seach_index), intval($page_num)));
		return $query -> result();
	}

	/**
	 * 查询用户图片信息
	 */
	function getUserImages($userID) {
		$sql = 'select * from t_user_image where c_user_id = ' . $userID;

		$query = $this -> db -> query($sql);

		return $query -> result();
	}

	/**
	 * 查看黑名单
	 */
	function getDefriendList($userID) {
		$sql = 'select b.c_user_id, b.c_user_name,c_user_image,n.c_in_time from t_defriend_relation n ,t_user_base b
                    where n.c_to_user_id = b.c_user_id and n.c_from_user_id =' . $userID;
		$query = $this -> db -> query($sql);

		return $query -> result();
	}

	/**
	 * 查询是否存在关注信息
	 */
	function exitRelation($formUserID, $toUserID) {
		$sql = 'SELECT * from t_relation n where n.c_from_user_id = ? and n.c_to_user_id = ?';
		$query = $this -> db -> query($sql, array(intval($formUserID), intval($toUserID)));

		return $query -> num_rows();
	}

	/**
	 * 删除关注关系
	 */
	function deleteRelation($fromUserID, $toUserID) {
		$this -> db -> where('c_from_user_id', $fromUserID);
		$this -> db -> where('c_to_user_id', $toUserID);
		$this -> db -> delete('t_relation');
	}

	/**
	 * 增加关系
	 */
	function insertRelation($arr) {
		$this -> db -> insert('t_relation', $arr);
	}
	
	/**
	 * 更新状态为好友
	 */
	public function updateFriendState($toUserID,$formUserID) {
		
		$this->db->set('c_rel_type', '2');
		$this->db->where('c_from_user_id', $toUserID);
		$this->db->where('c_to_user_id', $formUserID);
		$this->db->where('c_rel_type', 1);
		$this->db->update('t_relation');
	}
	

	/**
	 * 查询用户动态图片
	 */
	function getUserDynamicImage($userID) {
		$sql = 'SELECT i.c_dynamic_image 
			      FROM t_personnel_dynamic d,t_personnel_image i
			      WHERE d.c_id = i.c_dynamic_id AND d.c_personnel_id =?
			      ORDER BY i.c_dynamic_addtime desc
			      LIMIT 0 ,4';
		$query = $this -> db -> query($sql, array(intval($userID)));

		return $query -> result();
	}

	/**
	 * 插入用户动态图片
	 */
	function saveUserImage($arr) {
		$this -> db -> insert('t_user_image', $arr);
	}

	/**
	 * 获取用户关键字
	 */
	function getUserKeyWords($userID) {
		$sql = "SELECT * FROM t_user_keywords s WHERE s.c_user_id=" . $userID;
		$query = $this -> db -> query($sql);

		return $query -> result();
	}

	/**
	 * 删除用户关键字
	 *
	 */
	function deleteUserKeyWord($userID) {
		$this -> db -> where('c_user_id', $userID);
		$this -> db -> delete('t_user_keywords');
	}

	/**
	 * 插入用户关键字
	 */
	function insertUserKeyWord($arr) {
		$this -> db -> insert('t_user_keywords', $arr);
	}

	//获取用户密码
	function getUserPassWord($userID) {
		$sql = 'SELECT c_user_password FROM t_user_base WHERE c_user_id =' . $userID;
		$query = $this -> db -> query($sql);
		return $query -> row() -> c_user_password;
	}

	//加入黑名单
	function insertDefriend($arr) {
		$this -> db -> insert('t_defriend_relation', $arr);
	}
	//移除黑名单
	function deleteDefriend($userID,$uid){
		$this -> db -> where('c_from_user_id', $uid);
		$this -> db -> where('c_to_user_id',$userID);
		$this -> db -> delete('t_defriend_relation');
	}

	/**
	 * 查询用户信息
	 */
	public function getUserByName($userName) {

		$this -> db -> select('c_user_id as uid, c_user_code as omcode, c_user_phone as phone, c_user_name as username,c_user_password as pass,c_user_image as userImg ');
		$this -> db -> where('c_user_phone', $userName);
		$query = $this -> db -> get('t_user_base');

		if ($query -> num_rows() > 0) {
			return $query -> row_array();
		} else {
			return 0;
		}

	}

	/**
	 * 查询用户信息
	 */
	public function getUserById($userId) {

		$this -> db -> select('c_user_age as userage, c_user_gender as usergender, c_user_level as userlevel ');
		$this -> db -> where('c_user_id', $userId);
		$query = $this -> db -> get('t_user_base');

		if ($query -> num_rows() > 0) {
			return $query -> row_array();
		} else {
			return 0;
		}

	}

	/**
	 * 插入用户注册信息
	 */

	public function saveUser($arr) {
		$this -> db -> insert('t_user_base', $arr);
		$uid = $this -> db -> insert_id();
		// 		$this->db->insert('t_user_location',array('c_uid' => $uid));
		return $uid;
	}

	/**
	 * 按编号查询用户信息
	 */
	public function getUserNumsByID($userID) {
		$this -> db -> where('c_user_id', $userID);
		$query = $this -> db -> get('t_user_base');
		return $query -> num_rows();
	}

	/**
	 * 保存关键词
	 * @param unknown $datas
	 */
	public function saveKeywords($datas) {
		$this -> db -> insert_batch('t_user_keywords', $datas);
	}

	/**
	 * 保存绑定账号
	 * @param unknown $datas
	 */
	public function saveOMBind($datas) {
		$this -> db -> insert('t_user_login', $datas);
	}
	/**
	 * 删除通讯录
	 * @param unknown $uid
	 */
	public function deleteUserContacts($uid){
		//删除登录表纪录
		$this -> db -> where('c_login_type','contact');
		$this -> db -> where('c_user_id',$uid);
		$this -> db -> delete('t_user_login');
		//删除通讯录表
		$this -> db -> where('c_owner',$uid);
		$this -> db -> delete('t_user_contacts');
	}
	/**
	 *  解除通讯录绑定   只删除login表的信息  呵呵  你懂的 
	 * @param unknown $uid
	 */
	public function cancelContactsBind($uid){
		//删除登录表纪录
		$this -> db -> where('c_login_type','contact');
		$this -> db -> where('c_user_id',$uid);
		$this -> db -> delete('t_user_login');
	}

	/**
	 * 保存通讯录
	 * @param unknown $results
	 */
	public function saveUserContacts($results, $uid) {
		//插入login表
		$this -> db -> insert('t_user_login', array(
				'c_login_type'=>'contact',
				'c_login_time'=>date('y-m-d h:i:s',time()),
				'c_user_id'=>$uid
		));
		
		//批量插入通讯录信息
		$this -> db -> insert_batch('t_user_contacts', $results);
	}

	/**
	 *生成绑定通讯录后的好友列表
	 * @param unknown $uid
	 */
	public function searchUserContcts($uid) {
		$sql = 'SELECT a.c_user_id as friendid, a.c_user_name as nickname, a.c_user_phone as phone, a.c_user_image as img, b.c_name as name FROM t_user_base a, t_user_contacts b  WHERE a.c_user_phone=b.c_phone and b.c_owner='.$uid;
		$query = $this -> db -> query($sql);
		$result = $query->result();
		$total = $query->num_rows();
		
		$sql2 = 'select c_phone as phone, c_name as name from t_user_contacts where c_owner ='.$uid
		.' and c_phone not in ( SELECT b.c_phone FROM t_user_base a, t_user_contacts b  WHERE a.c_user_phone=b.c_phone and b.c_owner='.$uid.')';
		$query2 = $this -> db -> query($sql2);
		$result2 = $query2->result();
		$total2 = $query2->num_rows();
		
		return '{"useTotal":'.$total.', "used":'.json_encode($result)
		.', "unuseTotal":'.$total2.', "unused":'.json_encode($result2).'}';
	}
	/**
	 * 检测用户绑定的账号
	 * @param unknown $uid
	 */
	public  function searchUserBind($uid){
		$sql = 'select  * from t_user_login where c_user_id='.$uid;
		$result =  $this->db->query($sql)->result();
		return '{"result":'.json_encode($result).'}';
	}

	/**
	 * 更改头像照片
	 */
	public function updateHeadPortrait($uid, $imageUrl) {
		
		$this->db->set('c_user_image', $imageUrl);
		$this->db->where('c_user_id', $uid);
		$this->db->update('t_user_base');
		return $this -> db -> affected_rows();
	}
	/**
	 * 更新密码
	 */
	public function updatePassword($datas,$username){
		$this->db->set($datas);
		$this->db->where('c_user_phone', $username);
		$this->db->update('t_user_base');
	}
	
	public function getUserByUID($uid){
		$this -> db -> select('c_user_id as uid, c_user_code as omcode, c_user_phone as phone, c_user_name as username,c_user_password as pass,c_user_image as userImg ');
		$this -> db -> where('c_user_id', $uid);
		$query = $this -> db -> get('t_user_base');
		if ($query -> num_rows() > 0) {
			return $query -> row_array();
		} else {
			return 0;
		}
		
		
	}
	/**
	 * 用户注册第一步
	 * 数据放入临时表
	 */
	public function saveUserTemp($arr){
		$this -> db -> insert('t_user_base_temp', $arr);
		$uid = $this -> db -> insert_id();
		return $uid;
	}
	/**
	 * 更新头像到临时表
	 */
	public function updateTempHeader($tempuid, $imageUrl){
		$this->db->set('c_user_image', $imageUrl);
		$this->db->where('c_user_id', $tempuid);
		$this->db->update('t_user_base_temp');
		return $this -> db -> affected_rows();
	}
	/**
	 * 将临时表数据复制到用户表
	 * @param unknown $tempuid
	 */
	public function saveUserRegister($tempuid){
		$query = $this->db->query('INSERT INTO t_user_base(c_user_code,c_user_name,c_user_image,c_user_phone, c_user_gender,c_user_password,c_user_createtime,c_user_lastLoginTime) SELECT c_user_code,c_user_name,c_user_image,c_user_phone, c_user_gender,c_user_password,c_user_createtime,c_user_lastLoginTime FROM t_user_base_temp where c_user_id='.$tempuid);
		$uid = $this -> db -> insert_id();
		$this->db->query('INSERT INTO t_user_image(c_image_url,c_user_id) SELECT c_user_image,c_user_id FROM t_user_base where c_user_id='.$uid);
		return $uid;
	}
	
	public function listContact($condition){
		$sql = "select left(c_user_fname,1) indexPY,u.* from t_user_base u order by c_user_Fname ";
		$result =  $this->db->query($sql)->result();
		return '{"result":'.json_encode($result).'}';
	}
	

}
