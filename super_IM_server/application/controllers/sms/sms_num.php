<?php
/* 手机验证码生成函数*/
function get_mobile_code() {
	$forbidden_num = "1989:10086:12590:1259:10010:10001:10000:";
	do {
		$mobile_code = substr ( microtime (), 2, 6 );
	} while ( preg_match ( $mobile_code . ':', $forbidden_num ) );
	return $mobile_code;
}

function randStr($len=6,$format='ALL') {
	switch($format) {
		case 'ALL':
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~'; break;
		case 'CHAR':
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-@#~'; break;
		case 'NUMBER':
			$chars='0123456789'; break;
		default :
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
			break;
	}
	mt_srand((double)microtime()*1000000*getmypid());
	$password="";
	while(strlen($password)<$len)
		$password.=substr($chars,(mt_rand()%strlen($chars)),1);
	return $password;
}