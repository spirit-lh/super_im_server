<?php
header("Content-type: text/html; charset=utf-8");
header('Access-Control-Allow-Origin:*');
header('Content-type: text/json');
include_once(APPPATH."controllers/sms/CCPRestSmsSDK.php");
class SendTemplateSMS extends CI_Controller{
		public function __construct(){
			parent::__construct();
		    $this->load->model('index/Index_model');
		}
		
		//主帐号,对应开官网发者主账号下的 ACCOUNT SID
		private $accountSid= 'aaf98f8950f4a62c0150fa7497b11699';
		
		//主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
		private $accountToken= '964ddcd385e94443856e6da5838839ae';
		
		//应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
		//在开发调试的时候，可以使用官网自动为您分配的测试Demo的APP ID
// 		private $appId='8a48b55150f4a7260150fa74fb1c190c';
		private $appId='aaf98f8951d8d1c10151e72b27cb3087';
		
		//请求地址
		//沙盒环境（用于应用开发调试）：sandboxapp.cloopen.com
		//生产环境（用户应用上线使用）：app.cloopen.com
		private $serverIP='app.cloopen.com';
		
		
		//请求端口，生产环境和沙盒环境一致
		private $serverPort='8883';
		
		//REST版本号，在官网文档REST介绍中获得。
		private $softVersion='2013-12-26';
		
		
		/**
		 * 发送模板短信
		 * @param to 手机号码集合,用英文逗号分开
		 * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
		 * @param $tempId 模板Id,测试应用和未上线应用使用测试模板请填写1，正式应用上线后填写已申请审核通过的模板ID
		 */
		function sendTemplateSMS($to,$datas,$tempId)
		{
			// 初始化REST SDK
			$rest = new REST($this->serverIP,$this->serverPort,$this->softVersion);
			$rest->setAccount($this->accountSid,$this->accountToken);
			$rest->setAppId($this->appId);
		
			// 发送模板短信
			echo "Sending TemplateSMS to $to <br/>";
			$result = $rest->sendTemplateSMS($to,$datas,$tempId);
			if($result == NULL ) {
				echo "result error!";
				break;
			}
			if($result->statusCode!=0) {
				echo "error code :" . $result->statusCode . "<br>";
				echo "error msg :" . $result->statusMsg . "<br>";
				//TODO 添加错误处理逻辑
			}else{
				echo "Sendind TemplateSMS success!<br/>";
				// 获取返回信息
				$smsmessage = $result->TemplateSMS;
				echo "dateCreated:".$smsmessage->dateCreated."<br/>";
				echo "smsMessageSid:".$smsmessage->smsMessageSid."<br/>";
				//TODO 添加成功处理逻辑
			}
		}
		
		/**
		 * random code
		 * @param number $len
		 * @param string $format
		 */
		function randStr($len = 6, $format = 'ALL') {
			switch ($format) {
				case 'ALL' :
					$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
					break;
				case 'CHAR' :
					$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-@#~';
					break;
				case 'NUMBER' :
					$chars = '0123456789';
					break;
				default :
					$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
					break;
			}
			mt_srand ( ( double ) microtime () * 1000000 * getmypid () );
			$password = "";
			while ( strlen ( $password ) < $len )
				$password .= substr ( $chars, (mt_rand () % strlen ( $chars )), 1 );
			return $password;
		}
		
		function send_message(){
			$username = isset($_POST['username']) ? $_POST['username'] : NULL;
			$phone = self::randStr ( 6, 'NUMBER' );
			$this->Index_model->save_sms(array('phone' => $username,'code'=>$phone, 'time' => time()));
			self::sendTemplateSMS($username ,array($phone,'5'),"58608");
		}
}
