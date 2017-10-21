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
setUserCookie($_SESSION['name'], $_SESSION["userId"], 'true');

$userid = $_SESSION["userId"];
$new = 0;

// 如果是新用户，推他去修改个人信息
if ($_SESSION['pwdModiT'] == 0
	&& $_SESSION["name"] == '') {
		
	$new = 1;
}

$vault = 0;
$feng = 0;
$dfeng = 0;
$row = false;
$bonus = 0;
$dBonus = 0;
$lastCBTime = 0;

$res = false;

$con = connectToDB();
if ($con) {
		
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result || mysql_num_rows($result) <= 0) {
		
	}
	else {
		$row = mysql_fetch_assoc($result);
		
		$vault = $row["Vault"];
		$bonus = $row["CurrBonus"];
		$lastCBTime = $row["LastCBTime"];
		
		$now = time();
		// 每日固定分红只能收获一次
		if (isInTheSameDay($lastCBTime, $now)) {
			$bonus = 0;	
		}
	}
	
	$res = mysql_query("select * from PostTable where Status='$postStatusOnline' order by OnlineTime desc");
}

// $monConsumption = getMonthConsumption($userid);
$dayObtained = getDayObtained($userid);
$feng = ceil($vault / $fengzhiValue);

$hasBonus = ($bonus + $dBonus) > 0;
$bonusPnts = floor($bonus * $levelPntsRate[$_SESSION['lvl'] - 1] * 100) / 100;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>连物网主页</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="keywords" content="" />				<!-- ????? 便于搜索 -->
		<meta name="description" content="" />
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
<!-- 		<link rel="stylesheet" type="text/css" href="../css/luara.left.css"/> -->
		
		<script src="../js/jquery-1.8.3.min.js"></script>
		<script src="../js/jquery.luara.0.0.1.min.js"></script>
		<script src="../js/scripts.js"></script>
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
			
			function acceptBonus()
			{
				var bonus = <?php echo $bonus; ?>;
				if (bonus <= 0) {
					alert("出错了！");
					location.href = 'home.php';
				}
				
// 				var feng = <?php echo $vault; ?>;
// 				if (bonus > feng) {
// 					if (!confirm("固定蜂值余额不足了，如果继续，只能领取" + feng + "线上云量，是否继续？")) {
// 						return;
// 					}
// 					bonus = feng;
// 				}
				
				$.post("../php/credit.php", {"func":"acceptBonus"}, function(data){
					
					if (data.error == "false") {
						
						if (data.not_enough == "true") {
							alert(data.error_msg);
						}
						else {
						}
						document.getElementById("accept_btn").style.display = "none";
						document.getElementById("accept_logo").style.display = "block";
						document.getElementById("todayobtain").innerHTML = data.DayObtained;
						document.getElementById("bonuspool").innerHTML = data.vault;
						document.getElementById("point").innerHTML = data.credit;
						document.getElementById("pnts").innerHTML=data.pnts;
					}
					else {
						alert("领取失败：" + data.error_msg);
					}
				}, "json");
			}
			
			function acceptDBonus()
			{
				var dBonus = <?php echo $dBonus; ?>;
				if (dBonus <= 0) {
					alert("出错了！");
					location.href = 'home.php';
				}
				
				$.post("../php/credit.php", {"func":"acceptDBonus"}, function(data){
					
					if (data.error == "false") {
						
						if (data.not_enough == "true") {
							alert(data.error_msg);
						}
						else {
						}
						document.getElementById("accept_btn1").style.display = "none";
						document.getElementById("accept_logo1").style.display = "block";
						document.getElementById("todayobtain").innerHTML = data.DayObtained;
						document.getElementById("point").innerHTML = data.credit;
						document.getElementById("pnts").innerHTML=data.pnts;
					}
					else {
						alert("领取失败：" + data.error_msg);
					}
				}, "json");
			}
			
			function goToRecommend()
			{
/*
				var regiToken = document.getElementById("regiToken").innerHTML;
				regiToken = $.trim(regiToken);
				regiToken = parseFloat(regiToken);
				
				if (regiToken < <?php echo $refererConsumePoint; ?>) {
					if (confirm("您的注册券不足，需要先充值才能进入注册页面，是否前去？")) {
						location.href = "charge.php";
					}
				}
				else {
					location.href = "recommend.php";
				}
*/
				location.href = "recommend.php";
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
        <div width="100%">
            <img src="../img/lian-post.jpg" width="100%" />
        </div>
	        
		<div>
<!--
			<table class="t1" border="1" align="center" style="margin-bottom: 0;" rules="none">
				<tr>
					<td width="49" align="left"><?php echo $_SESSION['nickname'] . "(" . $_SESSION["userId"] . ")"; ?></td>
					<td width="25%">总线上云量</td>
					<td width="25%">当日线上云量</td>
				</tr>
				<tr>
					<td align="left"><?php echo $levelName[$_SESSION['lvl'] - 1]; ?></td>
					<td id="totalexpense"><?php if ($row) echo $row["TotalConsumption"]; else echo '0'; ?></td>
					<td id="todayobtain"><?php if ($row) echo $dayObtained; else echo '0'; ?></td>
				</tr>
			</table>
-->
			<table class="t1" border="1" align="center" style="margin-top: 0; " rules="none">
				<tr>
					<td width="33%">线上云量</td>
					<td width="33%">线下云量</td>
					<td width="33%">财富云量</td>
				</tr>
				<tr>
					<td id="point" style="color: red;"><?php if ($row) echo $row["Credits"]; else echo '0'; ?></td>
					<td id=""><?php if ($row) echo $row["Pnts"]; else echo '0'; ?></td>
					<td id="pnts"><?php if ($row) echo $row["Vault"]; else echo '0'; ?></td>
<!-- 					<td id="bonuspool"><?php echo $vault; ?></td> -->
<!-- 					<td id="dbonuspool"><?php echo $dfeng; ?></td> -->
				</tr>
			</table>
		</div>

		<div style="display: <?php if ($hasBonus > 0) echo "block"; else echo "none"; ?>;">
			<table width="100%">
				<?php if ($bonus > 0) { ?>
				<tr>
					<?php if ($bonusPnts > 0) { ?>
					<td style="width: 60%;"><p>分润 <b><?php echo ($bonus - $bonusPnts); ?></b> 线上云量，<b><?php echo $bonusPnts; ?></b>采线上云量！</p></td>
					<?php } else { ?>
					<td style="width: 60%;"><p>分润 <b><?php echo $bonus; ?></b> 线上云量！</p></td>
					<?php } ?>
					<td style="width: 36%;">
						<input id="accept_btn" type="button" value="领取" style="width: 100%;" onclick="acceptBonus()" />
						<p id="accept_logo" style="color: red; display: none;">已领取</p>
					</td>		
				</tr>
				<?php } ?>
<!--
				<?php if ($dBonus > 0) { ?>
					<td style="width: 60%;"><p>动态分润 <b><?php echo $dBonus; ?></b> 采线上云量！</p></td>
					<td style="width: 36%;">
						<input id="accept_btn1" type="button" value="领取" style="width: 100%;" onclick="acceptDBonus()" />
						<p id="accept_logo1" style="color: red; display: none;">已领取</p>
				<?php } ?>
-->
			</table>
		</div>
		
		<div class="btn_box" width="auto">
			<ul>
				<li><a class="icon_btn1" href="#" onclick="goToRecommend()">分享粉丝</a></li>
<!-- 				<li><a class="icon_btn1" href="products.html">蜂值倍增</a></li> -->
				<li><a class="icon_btn2" href="#">自由集市</a></li>
<!-- 				<li><a class="icon_btn13" href="products.php">商城</a></li> -->
				<li><a class="icon_btn3" href="virtuelife.php">虚拟生活</a></li>
				<li><a class="icon_btn6" href="exchange.php">交易所</a></li>
				<li><a class="icon_btn5" href="charity.php">会员慈善</a></li>
<!-- 				<li><a class="icon_btn11" href="transfer.php">线上云量互转</a></li> -->
				<li><a class="icon_btn7" href="#">会员创业</a></li>
				<li><a class="icon_btn4" href="#">直播购</a></li>
				<li><a class="icon_btn8" href="#">粉丝传媒</a></li>
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