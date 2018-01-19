<?php
namespace Junec\Huyisms;
class Huyisms {
	
	// 发送短信接口地址
	private $send_url = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
	
	// 余额查询接口地址
	private $query_url = 'http://106.ihuyi.com/webservice/sms.php?method=GetNum';
	
	// 短信平台APIID
	private $appid = '';
	
	// 短息平台APIKEY
	private $apikey = '';
	
	public function __construct($appid = '', $apikey = '') {
		parent::__construct();
		$this->appid = $appid;
		$this->apikey = $apikey;
	}
	
	/**
	 * 发送短息验证码
	 */
	public function send($phone, $content) {
		if ($this->utf8Strlen($phone) !== 11 || !this->isPhone($phone)) {
			return array('status' => 0, 'msg' => '手机号不正确');
		}
		if (!$content) {
			return array('status' => 0, 'msg' => '短信内容为空');
		}
		$data = "account={$this->appid}&password={$this->apikey}&mobile={$phone}&content=" . rawurlencode("{$content}");
		$result = $this->xmlToArray($this->post($data, $this->send_url));
		if ($result['SubmitResult']['code'] == 2) {
			return array('status' => 1, 'msg' => '发送成功');
		} else {
			return array('status' => 0, 'msg' => $result['SubmitResult']['msg']);
		}
	}
	
	/**
	 * 查询余额
	 */
	public function query()
	{
		$data = "account={$this->appid}&password={$this->apikey}";
		$result = $this->xmlToArray($this->post($data, $this->query_url));
		if ($result['SubmitResult']['code'] == 2) {
			return array('status' => 1, 'msg' => '发送成功', 'data' => 'num' => $result['SubmitResult']['num']);
		} else {
			return array('status' => 0, 'msg' => $result['SubmitResult']['msg']);
		}
	}
	
	/**
	 * 设置Appid
	 */
	public function setAppid($appid) {
		$this->appid = $appid;
	}
	
	/**
	 * 设置Appkey
	 */
	public function setApikey($apikey) {
		$this->apikey = $apikey;
	}
	
	/**
	 * 请求数据到短信接口，检查环境是否 开启 curl init。
	 */
	private function post($curlPost, $url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		$return_str = curl_exec($curl);
		curl_close($curl);
		return $return_str;
	}
	
	/**
	 * 将xml数据转换为数组格式。
	 */
	private function xmlToArray($xml){
		$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
		if (preg_match_all($reg, $xml, $matches)) {
			$count = count($matches[0]);
			for ($i = 0; $i < $count; $i++) {
				$subxml = $matches[2][$i];
				$key = $matches[1][$i];
				if (preg_match($reg, $subxml)) {
					$arr[$key] = $this->xmlToArray($subxml);
				} else {
					$arr[$key] = $subxml;
				}
			}
		}
		return $arr;
	}
	
	/**
	 * 产生随机数
	 */
	private function random($length = 6 , $numeric = 0) {
		PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
		if ($numeric) {
			$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
		} else {
			$hash = '';
			$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
			$max = strlen($chars) - 1;
			for ($i = 0; $i < $length; $i++) {
				$hash .= $chars[mt_rand(0, $max)];
			}
		}
		return $hash;
	}
	
	/**
	 * 计算字符串长度
	 */
	private function utf8Strlen($string = null) {
		// 将字符串分解为单元
		preg_match_all("/./us", $string, $match);
		// 返回单元个数
		return count($match[0]);
	}
	
	/**
	 * 判断是否是手机号码
	 */
	private function isPhone($phone) {
		return preg_match("/1[34578]{1}\d{9}$/",$phone);
	}
	
}