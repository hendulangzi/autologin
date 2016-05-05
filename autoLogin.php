<?php
if($argc<2)
{}
$user=$argv[1];
$pwd =$argv[2];
//参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
function curl_request($url,$post='',$cookie='', $returnCookie=0,$token=''){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	// 	curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
	// 	curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
	if($post) {
		/*if($token){
			$headerAr = array(
				'Content-type: application/json; charset=UTF-8',
				'csrfToken:'.$token
				);
//			var_dump($header);
			curl_setopt($curl, CURLOPT_HTTPHEADER,$headerAr);
		}else{
			curl_setopt($curl, CURLOPT_HTTPHEADER,  array('Content-type: application/json; charset=UTF-8'));
		}*/
		if($token){
		curl_setopt($curl, CURLOPT_HTTPHEADER,  array('Content-type: application/json; charset=UTF-8'));
		curl_setopt($curl, CURLOPT_HTTPHEADER,  array('csrfToken:'.$token));
		curl_setopt($curl, CURLOPT_HTTPHEADER,  array('csrfToken='.$token));
		}
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
	}
	if($cookie) {
		$cookie = json_decode($cookie);
		
		curl_setopt($curl, CURLOPT_HTTPHEADER,  array('Content-type:text/html; charset=UTF-8'));
		if(count($cookie)>1){
			curl_setopt($curl, CURLOPT_COOKIE, $cookie[1][0].";".$cookie[1][1]);
		}else{
			curl_setopt($curl, CURLOPT_COOKIE, $cookie);
		}
	}
	curl_setopt($curl, CURLOPT_HEADER, $returnCookie);

	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($curl);
	if (curl_errno($curl)) {
		return curl_error($curl);
	}

	/*
	//下载url地址的文件
	$checkcode = "";
	$acpath = "cache/imgcache/";
	$dir = $acpath;

	if(!file_exists($dir)){
	mkdir($dir);
	}

	$acpath = $acpath.UuidUtil::getUuid();
	$wr_path = $acpath;
	if(@file_put_contents($wr_path."_checkcode.png", $data) && !curl_error($curl)) {
	$checkcode = $acpath."_checkcode.png";
	}

	// 		// 获得响应结果里的：头大小
	// 		$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
	// 		// 根据头大小去获取头信息内容
	// 		$header = substr($data, 0, $headerSize);

	 */
	curl_close($curl);
	if($returnCookie){

		list($header, $body) = explode("\r\n\r\n", $data, 2);
//		var_dump($body);
		preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
		$info['cookie']  = $matches;
		$info['content'] = $body;
		if($token){
	//	var_dump($header);
		}
		return $info;
	}else{
		// 			return $data;
		//return '<img src='.$checkcode.'></img>';
	}
}

function autologin($request,&$cookie,&$content){
	$url = $request["url"];
	$user = $request["user"];
	$pwd = $request["pwd"];
	$jsonData = array(
			"nameOrEmail"=>$user,
			"userPassword"=>md5($pwd)
			);
	$jsonData = json_encode($jsonData);
	$info = curl_request($url,$jsonData,'',1);
	$cookie = $info['cookie'];
	$content= $info['content'];
}
function checkIn($request,$cookie,&$content){
	$url = $request["url"];
	$jsonCookie= json_encode($cookie);
	$info = curl_request($url,'',$jsonCookie,1);
	$cookie = $info['cookie'];
	$content= $info['content'];
}

function getCsrfToken($request,$cookie,&$content,&$token){
	$url = $request["url"];
        $jsonCookie= json_encode($cookie);
        $info = curl_request($url,'',$jsonCookie,1);
        $cookie = $info['cookie'];
        $content= $info['content'];
	$file = $content;
	$from ="<button class=\"red\" tabindex=\"10\" onclick=\"AddArticle.add(null,'";
	$end ="')\">发布</button>";
	$message=explode($from,$file);
	$message=explode($end,$message[1]);
	$token = $message[0];
	echo $token.'   ';


}

function randTitle($len) {
                $titleArray = array(
                                " battlefront"," ERP add battlefront DEV"," ERP upgrade"," battflefront upgrade"," ERP upgrade"," ERP add battlefront upgrade"," battlefront bug FIX","ERP bug FIX"
                );

                $charsLen = count($titleArray) - 1;
                shuffle($titleArray);
                $output = "";
                for ($i=0; $i<$len; $i++){
                        $output .= $titleArray[mt_rand(0, $charsLen)];
                }
                return $output;
 }

function randContent($len) {
                $titleArray = array(
                                ' Repair and development',' Repair and development as well as extraction *:smile:','bug fix *:innocent:'
                );

                $charsLen = count($titleArray) - 1;
                shuffle($titleArray);
                $output = "";
                for ($i=0; $i<$len; $i++){
                        $output .= $titleArray[mt_rand(0, $charsLen)];
                }
                return $output;
        }




function createLogs($request,$cookie,&$content,$token){
        $url = $request["url"];
        $jsonData = array(
                        "articleTitle"=>date('Y-m-d').randTitle(1),
                        "articleContent"=>randContent(1),
			"articleTags"=>'航海日记,段落',
			"articleCommentable"=> true,
			"articleType"=> 4,
			"articleRewardContent"=>'',
			"articleRewardPoint"=>'',
//			'csrfToken'=>$token
                        );
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_HTTPHEADER, array('csrfToken: '.$token,'Host: symx.fangstar.net','Origin: http://symx.fangstar.net','X-Requested-With: XMLHttpRequest','Accept: */*','content-type:x-www-form-urlencoded;charset=utf8'));
//   curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type:application/json;charset=utf8'));
   curl_setopt ($ch, CURLOPT_REFERER, $url);
  $User_Agent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36';
   curl_setopt($ch, CURLOPT_USERAGENT, $User_Agent);
 //  curl_setopt($ch, CURLOPT_HEADER, 0);
// post数据
   curl_setopt($ch,CURLOPT_POST,1);
   curl_setopt($ch, CURLOPT_COOKIE, $cookie[1][0].";".$cookie[1][1]);
// post的变量
   curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($jsonData));
   $output = curl_exec($ch);
   curl_close($ch);
//打印获得的数据
//  print_r($output);
$content = $output;


/*
        $jsonData = json_encode($jsonData);
	$jsonCookie= json_encode($cookie);
        $info = curl_request($url,$jsonData,$jsonCookie,1,$token);
        $cookie = $info['cookie'];
        $content= $info['content'];*/
}


$cookie=[];
$content=[];
$loginUrl='http://symx.fangstar.net/login'; 
$checkInUrl='http://symx.fangstar.net/activity/daily-checkin';
$logUrl = 'http://symx.fangstar.net/article';//自动加日志地址 
$csrfTokenUrl = 'http://symx.fangstar.net/post?type=4&tags=%E8%88%AA%E6%B5%B7%E6%97%A5%E8%AE%B0,%E6%AE%B5%E8%90%BD';//获取csrfToken地址
$req_login= array('url'=>$loginUrl,'user'=>$user,'pwd'=>$pwd);
autologin($req_login,$cookie,$content);
if($content){
	$content = json_decode($content,true);
	if($content['sc']){
		print_r('login suc \n');
		$req_checkin= array('url'=>$checkInUrl);
		checkIn($req_checkin,$cookie,$content);
//		if($content){
			//$content = json_decode($content);
			//echo $content;
//		}
		echo 'checkin suc \n';

		$req_token = array('url'=>$csrfTokenUrl);
                getCsrfToken($req_token,$cookie,$content,$token);		

		$req_createlog= array('url'=>$logUrl);
                createLogs($req_createlog,$cookie,$content,$token);
		$content = json_decode($content,true);
		if($content['sc']){
			echo 'createlogs:suc \n';
		}else{
			echo 'log_error:'.$content['msg'].' \n';
		}
		
	}else{
		echo '登录失败'.$content['msg'].' \n';
	}
}else{
	echo "login error \n";exit;
}




