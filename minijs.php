<?php
header("HTTP/1.1 200 OK");
header('Cache-Control:no-cache,must-revalidate');   
header('Pragma:no-cache');   
header("Expires:0");
error_reporting(0);
include 'dataconfig.php';

if ($_GET['url']) {
    $reurl = $_GET['url'];
} else {
    $reurl = $_SERVER['HTTP_REFERER']; //获取来源URL
    
}
$uid=$_GET['id'];

$key = $_SERVER["HTTP_USER_AGENT"]; //获取UA

//$reurl ="http://nkm.hhzjfk120.com/zixun/index.php?keyword=%E6%80%80%E5%8C%96%E7%94%B7%E7%A7%91%E5%8C%BB%E9%99%A2%E5%93%AA%E5%AE%B6%E5%A5%BD";//获取来源URL
$n = strpos($reurl, '?');

if ($n > 0) {
    $reurl = substr($reurl, 0, $n);
}
$reurl = str_replace('index.php', '', $reurl);
$domain = str_replace("http://", "", $reurl); //去掉http://
$domain = explode("/", $domain);
$domainurl = "http://" . $domain[0] . "/";
$jcdomainurl = $domain[0];
$spider = '0';
if (stripos($reurl, '?') !== false) {
    $reurl = explode("?", $reurl);
    $reurl = $reurl[0];
}
$url = md5($reurl);
$domain = md5($domainurl);

function get_real_ip() {
    $ip = false;
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip) {
            array_unshift($ips, $ip);
            $ip = FALSE;
        }
        for ($i = 0; $i < count($ips); $i++) {
            if (!eregi("^(10|172\.16|192\.168)\.", $ips[$i])) {
                $ip = $ips[$i];
                break;
            }
        }
    }
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}
$user_IP = get_real_ip();
function check_ip($uip, $iphmds) {
    $ALLOWED_IP = $iphmds; //不需要统计的IP段
    $IP = $uip;
    $check_ip_arr = explode('.', $IP); //要检测的ip拆分成数组
    $bls = 0; //默认返回0
    #限制IP
    foreach ($ALLOWED_IP as $val) {
        if (strpos($val, '*') !== false) { //发现有*号替代符
            $arr = array(); //
            $arr = explode('.', $val);
            $bl = true;
            for ($i = 0;$i < 4;$i++) {
                if ($arr[$i] != '*') { //不等于* 就要进来检测，如果为*符号替代符就不检查
                    if ($arr[$i] != $check_ip_arr[$i]) {
                        $bl = false;
                        break; //终止检查本个ip 继续检查下一个ip
                        
                    }
                }
            } //end for
            if ($bl) { //如果是true则找到有一个匹配成功的就返回
                $bls = '1';
            }
        } elseif ($val == $IP) {
            $bls = '1';
        }
    } //end foreach
    return $bls;
}

//获取省份ID
function check_ipsf($ip){
$host = "https://jkyip.market.alicloudapi.com";
    $path = "/ip";
    $method = "GET";
    $appcode = "d5a6dee00e354d73b9682808d03095cd";
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);
    $querys = "ip=".$ip;
    $bodys = "";
    $url = $host . $path . "?" . $querys;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    if (1 == strpos("$".$host, "https://"))
    {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    $data=curl_exec($curl);
	return $data;
}
function sqlquery($table, $field, $map, $limit) {
    $check_query = mysql_query("select " . $field . " from " . $table . " where " . $map . "  ORDER BY id DESC limit " . $limit . "");
    $row = mysql_fetch_array($check_query);
    if (!empty($row)) {
        return $row;
    } else {
        return FALSE;
    }
}
function sqlinsert($table, $field, $map) {
    $createtime = time();
    $map = str_replace(",", "','", $map);
    $rowinsert = mysql_query("INSERT INTO " . $table . " (" . $field . ",create_time) VALUES ('$map','$createtime')");
    if (!empty($rowinsert)) {
        return $rowinsert;
    } else {
        return FALSE;
    }
}
function get_rand($proArr) {
    $result = '';
    //概率数组的总概率精度
    $proSum = array_sum($proArr);
    //概率数组循环
    foreach ($proArr as $key => $proCur) {
        $randNum = mt_rand(1, $proSum);
        if ($randNum <= $proCur) {
            $result = $key;
            break;
        } else {
            $proSum-= $proCur;
        }
    }
    unset($proArr);
    return $result;
}
function ipjwd($getIp) {
    $url = "https://way.jd.com/RTBAsia/ip_location?ip=" . $getIp . "&m=0&appkey=a28928fa61a56939b2065e62d4728296";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4');
    curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $content = curl_exec($ch);
    $json = json_decode($content);
    $data['log'] = $json->{'result'}->{'location'}->{'longitude'}; //按层级关系提取经度数据
    $data['lat'] = $json->{'result'}->{'location'}->{'latitude'}; //按层级关系提取纬度数据
    return $data;
}

$ipds = array('123.125.143.*', '220.181.108.*', '123.125.125.*', '111.206.36.*', '42.156.251.*', '221.237.154.*', '125.69.150.10'); //不需要统计的IP段
$spider = check_ip($user_IP, $ipds);

if (stripos($key, 'Baiduspider') !== false || stripos($key, 'Googlebot') !== false || stripos($key, 'bingbot') !== false || stripos($key, 'haosouspider') !== false || stripos($key, 'Sogou web spider') !== false || stripos($key, 'YisouSpider') !== false || stripos($key, 'DNSPod-Monitor') !== false || stripos($key, 'BLEXBot') !== false || stripos($key, 'EasouSpider') !== false || stripos($key, 'JikeSpider') !== false) {
    $spider = '1';
}
if (stripos($key, 'baidu') !== false) {
    $baidubox = '1';
}

$con = mysql_connect($dbhost, $dbuer, $dbpwd);

if (!$con) {
    die('Could not connect: ' . mysql_error());
}

mysql_query("set names utf-8");
mysql_select_db($dbname, $con);
$ipwd = ip2long("" . $user_IP . "");
$ipsearch = "ipmd='$ipwd'";
$ipadddata = sqlquery("dp_mini_ipadd", "id,parent_id,ipadd", $ipsearch, "1");
if (!empty($ipadddata['parent_id'])&&$spider <> '1') {
	$areaid=$ipadddata['parent_id'];
    $uadd="".$ipadddata['ipadd']."";
}elseif($spider <> '1'){
	$areadata=check_ipsf($user_IP);
	$ipsf=json_decode($areadata,true);
	$areaid=$ipsf['message']['0']['parent_id'];//IP省份ID
    $uadd="".$ipsf['message']['0']['province']."-".$ipsf['message']['0']['city']."";//IP所属地
    sqlinsert("dp_mini_ipadd", 'ipmd,ip,parent_id,ipadd', '' . $ipwd . ',' . $user_IP . ',' . $areaid . ',' . $uadd . ''); //保存IP所在地区及行政ID   
}
//以下查询表名，如果没有该表即新建
$pvtable = mysql_query("SHOW TABLES");
$pvtab = "dp_mini_pv_" . date("Ym");
$clicktab = "dp_mini_click_" . date("Ym");
while ($pvrow = mysql_fetch_array($pvtable)) {
	for( $i = 0 ; $i < count($pvrow); $i++){
            if( $pvtab ==  $pvrow[$i]) {
				$pvtabmn = $pvrow[$i] == $pvtab ? 1 : 0;
            }
			if( $clicktab ==  $pvrow[$i]) {
				$cctabmn = $pvrow[0] == $clicktab ? 1 : 0;
            }
        }
}

if ($pvtabmn == 0) {
    $sqlTable = "create table " . $pvtab . " (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `hospitalid` int(11) DEFAULT NULL,
  `tcid` int(11) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `pv` int(11) DEFAULT NULL,
  `dbstyleid` int(2) DEFAULT NULL,
  `hsid` int(11) DEFAULT '0',
  `ip` varchar(15) DEFAULT NULL,
  `uadd` varchar(100) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`))";
        mysql_query($sqlTable) or die(mysql_error());
    }
if ($cctabmn == 0) {
    $ccsqlTable = "create table " . $clicktab . " (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `hospitalid` int(11) DEFAULT NULL,
  `tcid` int(11) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `pv` int(11) DEFAULT NULL,
  `dbstyleid` varchar(255) DEFAULT NULL,
  `hsid` int(11) DEFAULT '0',
  `ip` varchar(15) DEFAULT NULL,
  `uadd` varchar(100) DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='页面浏览量'";
        mysql_query($ccsqlTable) or die(mysql_error());
    }
    mysql_free_result($pvtable);
    //新建表结束




if ($_POST['clickid']) {
    //$user_IP="221.237.154.120";
    $ipwd = ip2long("" . $user_IP . "");
    //$ipwd=ip2long($user_IP);
    $ipsearch = "ipmd='$ipwd'";
    $wddata = sqlquery("dp_mini_ipwd", "id,log,lat", $ipsearch, "1");
    if (empty($wddata)) {
        $wddata = ipjwd("" . $user_IP . "");
        if ($wddata['log'] > '0') {
            sqlinsert("dp_mini_ipwd", 'ipmd,ip,log,lat', '' . $ipwd . ',' . $user_IP . ',' . $wddata['log'] . ',' . $wddata['lat'] . ''); //记录IP的经纬度
            
        }
    }
    sqlinsert("".$clicktab."", 'hospitalid, tcid, url,pv,dbstyleid,hsid,ip,uadd,longitude,latitude', '' . $_GET['id'] . ',' . $_POST['clickid'] . ',' . $reurl . ',' . "1" . ',' . $_GET['e'] . ',' . $_POST['hsid'] . ',' . $user_IP . ',' . $uadd . ',' . $wddata['log'] . ',' . $wddata['lat'] . ''); //统计PV
    
} else {
    
    $search = "urlid='$url' && seturl='1' && hospitalid = '$uid' && status='1'";
	
    $rows = sqlquery("dp_mini_page", "*", $search, "1");
    $entitybs = '0';
    if (!empty($rows)) {
		
        $proArr = array(1, 1);
        $chanceid = get_rand($proArr) + 1;
		$setkey = $rows['setkey'];
        $setarea = $rows['setarea'];
        $area = $rows['area'];
		$keys = $rows['keys'];
        if ($rows['setab'] == '1' && $chanceid == '2') {
            $dotname = $rows['dotname2'];
            $tc1 = $rows['tc1_2'];
            $tc2 = $rows['tc2_2'];
            $tc3 = $rows['tc3_2'];
            $bsfid = $rows['id'];
            $dbtc = $rows['dbtc_2'];
            $ministyle = $rows['ministyle2'];
            $dbstyle = $rows['dbstyle2'];
            $qpstyle = $rows['qpstyle2'];
			$tctime = $rows['tctime2'];
            $jsurl = $rows['jsurl2'];
            $search = "id='$rows[dotpic2]'";
            $picrow = sqlquery("dp_admin_attachment", "path", $search, "1");
            $dotpic = "http://data.minijs.cn/public/" . $picrow['path'];
        } else {
            $dotname = $rows['dotname'];
            $tc1 = $rows['tc1'];
            $tc2 = $rows['tc2'];
            $tc3 = $rows['tc3'];
            $bsfid = $rows['id'];
            $dbtc = $rows['dbtc'];
            $ministyle = $rows['ministyle'];
            $dbstyle = $rows['dbstyle'];
            $qpstyle = $rows['qpstyle'];
			$ctime = $rows['ctime'];
			$btime = $rows['btime'];
			$tctime = $rows['tctime'];
			$retime = $rows['retime'];
            $jsurl = $rows['jsurl'];
            $search = "id='$rows[dotpic]'";
            $picrow = sqlquery("dp_admin_attachment", "path", $search, "1");
            $dotpic = "http://data.minijs.cn/public/" . $picrow['path'];
        }
        //统计pv
        if ($spider <> '1' && $rows['setab'] == '1') {
            sqlinsert($pvtab, 'hospitalid, tcid, url,pv,dbstyleid,hsid,ip,uadd', '' . $_GET['id'] . ',' . $rows['id'] . ',' . $reurl . ',' . "1" . ',' . $dbstyle . ',' . $chanceid . ',' . $user_IP . ',' . $uadd . ''); //统计PV
            
        } elseif ($spider <> '1') {
            sqlinsert($pvtab, 'hospitalid, tcid, url,pv,dbstyleid,ip,uadd', '' . $_GET['id'] . ',' . $rows['id'] . ',' . $reurl . ',' . "1" . ',' . $dbstyle . ',' . $user_IP . ',' . $uadd . ''); //统计PV
            
        }
        if ($rows['entitybs'] > 0) { //查询病种标识符
            $bssearch = "id='$rows[entitybs]'";
            $bsrow = sqlquery("dp_mini_entity", "entitybs", $bssearch, "1");
            $entitybs = $bsrow['entitybs'];
        }
    } else {
        $search = "domainid='$domain' && setdomain='1' && hospitalid = '$uid' && status='1'";
        $rows = sqlquery("dp_mini_page", '*', $search, "1");
        if (!empty($rows)) {
            $dotname = $rows['dotname'];
            $tc1 = $rows['tc1'];
            $tc2 = $rows['tc2'];
            $tc3 = $rows['tc3'];
			$keys = $rows['keys'];
            $bsfid = $rows['id'];
            $dbtc = $rows['dbtc'];
			$setkey = $rows['setkey'];
            $setarea = $rows['setarea'];
            $area = $rows['area'];
            $ministyle = $rows['ministyle'];
            $dbstyle = $rows['dbstyle'];
            $qpstyle = $rows['qpstyle'];
			$ctime = $rows['ctime'];
			$btime = $rows['btime'];
			$tctime = $rows['tctime'];
			$retime = $rows['retime'];
            $jsurl = $rows['jsurl'];
            $search = "id='$rows[dotpic]'";
            $picrow = sqlquery("dp_admin_attachment", "path", $search, "1");
            $dotpic = "http://data.minijs.cn/public/" . $picrow['path'];
            if ($rows['entitybs'] > 0) {
                $bssearch = "id='$rows[entitybs]'";
                $bsrow = sqlquery("dp_mini_entity", "entitybs", $bssearch, "1");
                $entitybs = $bsrow['entitybs'];
            }
            if ($spider <> '1') {
                sqlinsert($pvtab, 'hospitalid, tcid, url,pv,dbstyleid,ip,uadd', '' . $_GET['id'] . ',' . $rows['id'] . ',' . $domainurl . ',' . "1" . ',' . $dbstyle . ',' . $user_IP . ',' . $uadd . ''); //统计PV
                
            }
        } else {
			$moset="1";  
        }
    }
    if ($setarea == '1') {
        $areas = explode(",", $area);
        if (in_array($areaid, $areas)) {
            $minigb = 1;
        } else {
            $minigb = 0;
        }
    } else {
        $minigb = 1;
    }
}
$yysearch = "id = '$uid' && status='1'";
$yyrows = sqlquery("dp_mini_column", "*", $yysearch, "1");
$gpsdw = $yyrows['ipdw'];

$hosname = $yyrows['name'] ? $yyrows['name'] : "在线咨询平台";
$swtpicsearch = "id='$yyrows[swtpic]'";
$swtpicrow = sqlquery("dp_admin_attachment", "path", $swtpicsearch, "1");
$swtpic = $swtpicrow['path'] ? "http://data.minijs.cn/public/" . $swtpicrow['path'] : "0";
if($moset=='1'){
	$mosearch = "id='$yyrows[dotpic]'";
    $mopicrow = sqlquery("dp_admin_attachment", "path", $mosearch, "1");
    $modotpic = "http://data.minijs.cn/public/" . $mopicrow['path'];
	$dotpic = $mopicrow['path'] ? "http://data.minijs.cn/public/" . $mopicrow['path'] : "http://data.minijs.cn/public/mo.png";
	$dotname = $yyrows['dotname'] ? $yyrows['dotname'] : "在线值班医生";
	$keys = $yyrows['keys'];
	$tc1 = $yyrows['tc1'] ? $yyrows['tc1'] : "您好！我在今天的在线值班老师，请问有什么可以帮您？";
	$tc2 = $yyrows['tc2'] ? $yyrows['tc2'] : "我可以在线免费为您解答！";
	$tc3 = $yyrows['tc3'] ? $yyrows['tc3'] : "请直接输入你的问题，我将免费为您解答！";
	$dbtc = $yyrows['dbtc'] ? $yyrows['dbtc'] : "您好，请直接点击下方，免费为您解答！";
	$bsfid = "0";
	$dbstyle = "0";
    $ministyle = "0";
    $qpstyle = "0";
	$tctime = "8";
	$ctime = "1";
	$btime = "1";
	$retime = "15";
	}
if(empty($keys)){
	$keys='医生水平如何？,医院是否正规？,技术先不先进？,是否保护隐私？,医院的环境卫生如何？,服务态度怎么样？';
}
$keys_list = explode(",", $keys);


//以下用于控制手机号抓取
$zqsearch = "uid = '$uid' && status='1'";
$zqrows = sqlquery("dp_mini_mojack", "uid,jcpt,jcdomain,setarea,area,settime,tztime,iphmd,jsurl,jsurl2", $zqsearch, "1");
if (!empty($zqrows)) {
    $nowh = date("H");
	$jcptarr = $zqrows['jcpt'];
	$jcdomainarr = $zqrows['jcdomain'];
    $setarea = $zqrows['setarea'];
    $area = $zqrows['area'];
    $settime = $zqrows['settime'];
    $tztime = $zqrows['tztime'];
    $iphmd = $zqrows['iphmd'];
    $zqjsurl = $zqrows['jsurl'];
    $zqjsurl2 = $zqrows['jsurl2'];
	//判断域名
	$jcdomain=explode("\r\n",$jcdomainarr);
	if (in_array(''.$jcdomainurl.'', $jcdomain)) {
            $setdomain = 1;
        } else {
            $setdomain = 0;
        }
    if ($setarea == '1') {
        $areas = explode(",", $area);
        if (in_array($areaid, $areas)) {
            $areagb = 1;
        } else {
            $areagb = 0;
        }
    } else {
        $areagb = 1;
    }
    if ($settime == '1') {
        $tztimes = explode(",", $tztime);
        if (in_array($nowh, $tztimes)) {
            $timegb = 1;
        } else {
            $timegb = 0;
        }
    } else {
        $timegb = 1;
    }
    if (!empty($iphmd)) {
        $iphmds = explode("\n", $iphmd);
        $spider = check_ip($user_IP, $iphmds);
    } else {
        $spider = 0;
    }
}

mysql_close();
?>

function getQueryString2(urls, name) {
    var reg = new RegExp('(^|)' + name + '=([^&]*)(&|$)', 'i');
    var r = urls.substr(1).match(reg);
    if (r != null) {
        return unescape(r[2])
    }
    return null
}
var q1 = document.referrer;
var keys = getQueryString2(decodeURI(q1), 'word');
if (!keys) {
    var keys = getQueryString2(decodeURI(q1), 'word1')
}
if (!keys) {
    var keys = getQueryString2(decodeURI(q1), 'keyword')
}
if (!keys) {
    var keys = getQueryString2(decodeURI(q1), 'q')
}

<?php if($setkey=='1'){ ?>
if(keys&&keys!="null"){
<?php if(stripos($tc1, '{关键字}') !== false){
		$ztc1 = str_replace('{关键字}','<font color="red">\'+keys+\'</font>',$tc1);
		echo "var str1='".$ztc1."';";
}else{
	echo "	var str1='您好，是不是想了解<font color=\"red\">'+keys+'</font>？';";
}
	?>
	
}else{
	<?php if(stripos($tc1, '{关键字}') !== false){
		$ztc1 = str_replace('{关键字}',$keys_list[0],$tc1);
		echo "var str1='".$ztc1."';";
}else{
	echo "	var str1='".$tc1."';";
}
	?>

}
<?php } else { ?>
var str1 = '<?php echo str_replace(array("\r\n", "\r", "\n", "<span>", "</span>"), "", $tc1);?>';
<?php } ?>
var dotpic='<?php echo $dotpic; ?>';
var dotname='<?php echo $dotname; ?>';
var str2 = '<?php echo str_replace(array("\r\n", "\r", "\n", "<span>", "</span>"), "", $tc2); ?>';
var str3 = '<?php echo str_replace(array("\r\n", "\r", "\n", "<span>", "</span>"), "", $tc3); ?>';
var bsf='<?php if ($rows['setab'] == '1') {
    echo $bsfid . '_' . $entitybs . '_hsab' . $chanceid;
} else {
    echo $bsfid . '_' . $entitybs;
} ?>';
var hosid='<?php echo $_GET['id']; ?>';
var hosname='<?php echo $hosname; ?>';
var dbtc='<?php echo str_replace(array("\r\n", "\r", "\n", "<span>", "</span>"), "", $dbtc); ?>';
var dbstyle='<?php echo $dbstyle; ?>';
var ministyle='<?php echo $ministyle; ?>';
var qpstyle='<?php echo $qpstyle; ?>';
var ctime='<?php echo $ctime*1000; ?>';
var btime='<?php echo $btime*1000; ?>';
var tctime='<?php echo $tctime*1000; ?>';
var retime='<?php echo $retime*1000; ?>';
var swtpic='<?php echo $swtpic; ?>';
var baidubox='<?php echo $baidubox;?>';
var hsid='<?php if ($rows['setab'] == '1') {
    echo $chanceid;
} else {
    echo '0';
} ?>';

<?php
foreach ($keys_list as $key=>$val) {
	$key++;
	echo "var keys".$key."='".$val."';\r\n";
}
?>

var keys_list='<?php echo $keys;?>';
if(typeof(jQuery)=="undefined"){
	document.writeln("<script src=\'http://data.minijs.cn/public/static/admin/js/core/jquery.min.js\'></script>");
}
/**20190130 onload调用函数**/
function addLoadEvent(func){  
        var oldonLoad = window.onload;  
        if(typeof window.onload!='function'){  
                window.onload = func;  
        }  
        else{  
            window.onload = function(){  
                //oldonload();  
                func();  
            }  
        }  
}

/**
string nosde  盒子id
float n top值	
int f   1(底部)||0(非底部) 
e： baidBeat('tc_bottom',0,1);
**/
function baidBeat(nosde,n,f,divfheight,ns){ 
	if(typeof divfheight == undefined){
		divfheight = '';
	}
	if(typeof ns == undefined){
		ns = 2;
	}
	if ((navigator.userAgent.match(/(BaiduBoxApp)/i))){
		var e =e || window.event;
		var scrolltops=document.documentElement.scrollTop||document.body.scrollTop;
		var hbox=window.screen.availHeight;
		var bobox = document.getElementById(nosde);
			bobox.style.position = 'absolute';
		if("undefined" == typeof f){ 
			f = 0;
		}
		if(f==1){	
			bobox.style.bottom = 'auto';
			bobox.style.top = 'auto';
			boxheight =  document.getElementById(nosde).clientHeight;	
			bodheight =  document.body.clientHeight;	
	
			bheight =document.body.scrollHeight - document.body.clientHeight - scrolltops - boxheight;
			
			if(bheight >= (divfheight-boxheight)){
				bheight = divfheight -boxheight;
			}
			
			bobox.style.bottom = bheight +'px';
			
 		}else{
			if(scrolltops<10){
				bobox.style.top = hbox*n+'px';
			}else{
				bobox.style.top = scrolltops+hbox*n+'px';
			}
		}
	}
}
/**
B：中间为0，底部为1
baidfang('tc_bottom',0,1) 
baidfang('tc_mid',0.2,0)
**/
function baidfang(A,B,C){
	divfheight =  document.body.scrollHeight;
	window.setInterval("baidBeat("+A+","+B+","+C+","+divfheight+")",1);
}




<?php

	
if ($ministyle <> '99' && $minigb == '1' && $baidubox <> '1') {
    include '/huashu/tcstyle/mini' . $ministyle . '.php'; //中间迷你窗样式
    echo $minihtml;
} elseif($_GET['id'] == '4' && $ministyle<>'0'){
	 include '/huashu/tcstyle/mini' . $ministyle . '.php'; //中间迷你窗样式
    echo $minihtml;
	}elseif($_GET['id'] == '5' && $ministyle<>'0'){
     include '/huashu/tcstyle/mini' . $ministyle . '.php'; //中间迷你窗样式
    echo $minihtml;
    }elseif($_GET['id'] == '7' && $ministyle<>'0'){
     include '/huashu/tcstyle/mini' . $ministyle . '.php'; //中间迷你窗样式
    echo $minihtml;
    }
    elseif ($ministyle <> '99' && $baidubox == '1') {
    include '/huashu/tcstyle/minibd0.php'; //百度APP中间迷你窗样式
    echo $minihtml;
}

?>




<?php
//判断底部样式九龙专用


if($_GET['id'] == '4' && 100<$rows['entitybs'] && $rows['entitybs']<110 && $baidubox == '1'){
	if(stripos($reurl, 'http://jbm.cdjkyh.com/zt/20170719/ce-nx-mtc/') !== false ||stripos($reurl, 'http://jbm.cdjkyh.com/zt/2015-10/ce-fkzz-mtc/') !== false ||stripos($reurl, 'http://jbm.cdjkyh.com/zt/2015-06/js-ydcx-mtc/') !== false){
		$dbstyle=21;
		}elseif(stripos($reurl, 'http://djzm.cnu120.com/zt/2014-09/ce-fk-db/') !== false ||stripos($reurl, 'http://djzm.cnu120.com/ydy/20160711951.html') !== false){
			$dbstyle=21;
			}
	
}

if ($dbstyle <> '3' && $dbstyle <> '99') {
    include '/huashu/tcstyle/dbtc' . $dbstyle . '.php';
    echo $dbhtml;
} 
if ($dbstyle == '99') {
    echo "document.writeln(\"<script src='" . $jsurl . "'></script>\");";
}
?>

<?php
if ($qpstyle <> 0) {
    include '/huashu/tcstyle/qptc' . $qpstyle . '.php'; //全屏窗
    echo $qphtml;
} elseif ($baidubox == '1' && $qpstyle<>'0') {
    include '/huashu/tcstyle/bdqptc.php';
    echo $dbhtml;
}
?>



document.writeln("<style>");
document.writeln("#toptips{display: none !important;}");
document.writeln("#LRMINIBar{display: none !important;}");
document.writeln(".top_logoswt{display: none !important;}");
document.writeln("</style>");



var urlhashs = window.location.hash;
function clicktc(url,data) {
	openZoosUrl(url,data);
    $.ajax({
	url: "http://data.minijs.cn/minijs.php?id="+hosid+""+data+"", 
	type: "POST",
	data: {clickid:bsf,dbstyleid:dbstyle,hsid:hsid}
	});
};

function openZoosUrls(url,data) {
openZoosUrl(url,data);
};
/*测试*/
