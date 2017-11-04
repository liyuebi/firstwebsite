<?php

include "../php/database.php";
include "../php/constant.php";

session_start();
// check if logined. check cookie to limit login time
// check session first to avoid if user close browser and reopen, cookie is still valid but can't find session
if ((isset($_SESSION['isLogin']) && $_SESSION['isLogin'])
	&& (isset($_COOKIE['isLogin']) && $_COOKIE['isLogin'])) {
	// no code here, just continue;		
} 
else {
	$home_url = '../index.php';
	header('Location: ' . $home_url);
	exit();
}

include "../php/func.php";
setUserCookie($_SESSION['nickname'], $_SESSION["userId"], 'true');

$userid = $_SESSION["userId"];
$new = 0;

// 如果是新用户，推他去修改个人信息
if ($_SESSION['accInited'] <= 0) {
	$home_url = 'initAcc.php';
	header('Location: ' . $home_url);
	exit();	
}

$vault = 0;
$row = false;
$hasBonus = false;
$bonus = 0;
$lastCBTime = 0;

$con = connectToDB();
if ($con) {
		
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result || mysql_num_rows($result) <= 0) {
		
	}
	else {
		$row = mysql_fetch_assoc($result);
		
		$vault = $row["Vault"];
		$lastCBTime = $row["LastCBTime"];
		
		$now = time();
		// 利息每日只能收获一次
		if (!isInTheSameDay($lastCBTime, $now)) {
			
			date_default_timezone_set('PRC');
			$hour = intval(date("H", $now));
			// 6点以后才可以领取利息
			if ($hour >= 6) {
			
				include "../php/bonus.php";
				$bonus = getDayBonus($userid);
				if ($bonus > 0) {
					$hasBonus = true;
				}
			}
		}
	}
	
// 	$res = mysql_query("select * from PostTable where Status='$postStatusOnline' order by OnlineTime desc");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>连物网主页</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="keywords" content="" />				<!-- ????? 便于搜索 -->
		<meta name="description" content="" />
		
		<link rel="apple-touch-icon" href="/apple-touch-icon.png">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
<!-- 		<link rel="stylesheet" type="text/css" href="../css/luara.left.css"/> -->
		
		<script src="../js/jquery-3.2.1.min.js"></script>
		<script src="../js/jquery.luara.0.0.1.min.js"></script>
		<script src="../js/scripts.js"></script>
		<script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script>
		<script type="text/javascript">
			
			$(document).ready(function(){
				
// 				if (isNotLoginAndJump()) {
// 					return;
// 				}
				if (<?php echo $new; ?> > 0) {
					setTimeout("countDown()", 1000);
				}
			});
			
			function countDown()
			{
				alert("请先去完善您的个人资料，修改登录密码，及设置初始密码！");
				location.href = 'me.php';
			}
			
			function acceptBonus(btn)
			{
								
				$.post("../php/credit.php", {"func":"acceptBonus"}, function(data){
					
					if (data.error == "false") {
						
						if (data.not_enough == "true") {
							alert(data.error_msg);
						}
						else {
						}
						document.getElementById("accept_btn").style.display = "none";
						document.getElementById("accept_logo").style.display = "block";
						document.getElementById("point").innerHTML = data.credit;
						document.getElementById("bonuspool").innerHTML = data.vault;
					}
					else {
						alert("领取失败：" + data.error_msg);
					}
				}, "json");
			}
						
			function goToRecommend()
			{
				var credit = document.getElementById("point").innerHTML;
				credit = $.trim(credit);
				credit = parseFloat(credit);
				
				if (credit <= 0) {
					if (confirm("您的线上云量为0，是否前去云量交易获取？")) {
						location.href = "exchange.php";
					}
				}
				else {
					location.href = "recommend.php";
				}
			}
		</script>
	</head>
	
	<body>
<!--
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
-->
<!--
        <div width="100%">
            <img src="../img/lian-post.jpg" width="100%" />
        </div>
-->
        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
			<!-- Indicators -->
		    <ol class="carousel-indicators">
		    	<li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
				<li data-target="#carousel-example-generic" data-slide-to="1"></li>
				<li data-target="#carousel-example-generic" data-slide-to="2"></li>
				<li data-target="#carousel-example-generic" data-slide-to="3"></li>
				<li data-target="#carousel-example-generic" data-slide-to="4"></li>
				<li data-target="#carousel-example-generic" data-slide-to="5"></li>
		    </ol>
		
		    <!-- Wrapper for slides -->
		    <div class="carousel-inner" role="listbox">
		    	<div class="item active">
			        <img src="../img/intro1.jpg" alt="...">
			    </div>
			    <div class="item">
			        <img src="../img/intro2.jpg" alt="...">
			    </div>
			    <div class="item">
			        <img src="../img/intro3.jpg" alt="...">
			    </div>
			    <div class="item">
			        <img src="../img/intro4.jpg" alt="...">
			    </div>
			    <div class="item">
			        <img src="../img/intro5.jpg" alt="...">
			    </div>
			    <div class="item">
			        <img src="../img/intro6.jpg" alt="...">
			    </div>
		    </div>
	    </div>
	     
		<div>
			<table class="t1" border="1" align="center" style="margin-top: 5px; border: 1px solid #e7e7e7;" rules="none">
				<tr>
					<td width="33%">线上云量</td>
					<td width="33%">线下云量</td>
					<td width="33%">财富云量</td>
				</tr>
				<tr>
					<td id="point" style="color: red;"><?php if ($row) echo $row["Credits"]; else echo '0'; ?></td>
					<td id=""><?php if ($row) echo $row["Pnts"]; else echo '0'; ?></td>
					<td id="bonuspool"><?php if ($row) echo $row["Vault"]; else echo '0'; ?></td>
				</tr>
			</table>
		</div>

		<div style="display: <?php if ($hasBonus > 0) echo "block"; else echo "none"; ?>; margin: 5px 0; border: 1px solid #e7e7e7;">
			<table width="100%">
				<tr>
					<td style="width: 60%;"><p>今日领取 <b><?php echo $bonus; ?></b> 线上云量！</p></td>
					<td style="width: 36%;">
						<input id="accept_btn" type="button" value="领取" style="width: 100%;" onclick="acceptBonus(this)" />
						<p id="accept_logo" style="color: red; display: none;">已领取</p>
					</td>		
				</tr>
			</table>
		</div>
		
		<div class="btn_box" width="auto">
			<ul>
				<li><a class="icon_btn1" onclick="goToRecommend()">分享云粉</a></li>
				<li><a class="icon_btn6" href="exchange.php">云量交易</a></li>
				<li><a class="icon_btn5" href="posters.php">公告</a></li>
				<li><a class="icon_btn3" href="virtuelife.php">虚拟生活</a></li>
				<li><a class="icon_btn2" href="#">自由集市</a></li>
<!-- 				<li><a class="icon_btn5" href="charity.php">会员慈善</a></li> -->
				<li><a class="icon_btn7" href="#">线下商家</a></li>
				<li><a class="icon_btn4" href="#">直播购</a></li>
				<li><a class="icon_btn8" href="#">云粉传媒</a></li>
				<li><a class="icon_btn12" href="#">云量商城</a></li>
			</ul>
		</div>
		
<!--
		<div id="post" style="background: #dddbdb; margin-bottom: 30px;">
			<h3>公告：</h3>
			<?php 
				if ($res) {
					while ($row = mysql_fetch_array($res)) {
			?>
					<a href="poster.php?idx=<?php echo $row["IndexId"]; ?>"><?php echo $row["Title"]; ?></a>
					<br>		
			<?php
					}
				}
			?>
		</div>
-->
<!-- 		<div class="btn_box" width="auto"> -->
		<div class="footer"> 
			<div>
				<ul class="nav nav-pills">
					<li class="active" style="display:table-cell; width:1%; float: none"><a href="#" style="text-align: center;">首页</a></li>
					<li style="display:table-cell; width:1%; float: none"><a href="recommended.php" style="text-align: center">朋友</a></li>
					<li style="display:table-cell; width:1%; float: none"><a href="me.php" style="text-align: center">个人中心</a></li>
				</ul>
			</div>
		</div>
	</body>
</html>