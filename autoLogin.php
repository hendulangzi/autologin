<?php
if($argc<2){
	
}
$user=$argv[1];
$pwd =$argv[2];
//参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
function curl_request($url,$post='',$cookie='', $returnCookie=0,$token=''){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	if($post) {
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

	curl_close($curl);
	if($returnCookie){

		list($header, $body) = explode("\r\n\r\n", $data, 2);
		preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
		$info['cookie']  = $matches;
		$info['content'] = $body;
		if($token){
		}
		return $info;
	}else{
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
                                "title1","title2"
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
                                'this is remark','this is remark 2'
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
			"articleRewardPoint"=>''
                        );
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_HTTPHEADER, array('csrfToken: '.$token,'Host: xxxx.net','Origin: http://www.xxx.net','X-Requested-With: XMLHttpRequest','Accept: */*','content-type:x-www-form-urlencoded;charset=utf8'));

   curl_setopt ($ch, CURLOPT_REFERER, $url);
  $User_Agent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36';
   curl_setopt($ch, CURLOPT_USERAGENT, $User_Agent);
// post data
   curl_setopt($ch,CURLOPT_POST,1);
   curl_setopt($ch, CURLOPT_COOKIE, $cookie[1][0].";".$cookie[1][1]);
// post params
   curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($jsonData));
   $output = curl_exec($ch);
   curl_close($ch);
//out put get's data
$content = $output;
}


$cookie=[];
$content=[];
$loginUrl='http login url'; 
$checkInUrl='daily checkin http url';
$logUrl = 'logs url';
$csrfTokenUrl = 'get csrfToken url'; 
$req_login= array('url'=>$loginUrl,'user'=>$user,'pwd'=>$pwd);
autologin($req_login,$cookie,$content);
if($content){
	$content = json_decode($content,true);
	if($content['sc']){
		print_r('login suc \n');
		$req_checkin= array('url'=>$checkInUrl);
		checkIn($req_checkin,$cookie,$content);
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
		echo 'login fail'.$content['msg'].' \n';
	}
}else{
	echo "login error \n";exit;
}




