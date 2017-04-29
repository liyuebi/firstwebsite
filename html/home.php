<?php

include "../php/database.php";
include "../php/constant.php";

session_start();
if (!$_SESSION['isLogin']) {	
	$home_url = '../index.html';
	header('Location: ' . $home_url);
	exit();
}
$userid = $_SESSION["userId"];
$new = 0;

$con = connectToDB();

// 如果是新用户，推他去修改个人信息
if ($_SESSION["password"] == '000000'
	&& $_SESSION["name"] == '') {
		
	$new = 1;
}

$feng = 0;
$row = false;
if ($con) {
	
	mysql_select_db("my_db", $con);
	
	$isDynamic = false;
	
	$res1 = mysql_query("select * from User where UserId='$userid'");
	if ($res1 && mysql_num_rows($res1) > 0) {
		$row1 = mysql_fetch_assoc($res1);
		$isDynamic = $row1["RecommendingCount"] > 0;
	}
	
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result || mysql_num_rows($result) <= 0) {
		
	}
	else {
		$row = mysql_fetch_assoc($result);
		
		$staticFeng = $row["Vault"];
		$dynamicFeng = $row["DynamicVault"];
		
		if ($dynamicFeng > 0) {
			
			if ($isDynamic) {
				$staticFeng += $dynamicFeng;
				$dynamicFeng = 0;
				mysql_query("update Credit set Vault='$staticFeng', DynamicVault='$dynamicFeng' where UserId='$userid'");
			}
		}
		$feng = $staticFeng;
	}
}

$monConsumption = getMonthConsumption($userid);
$dayObtained = getDayObtained($userid);
$feng = ceil($feng / $fengzhiValue);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>蜜蜂工坊主页</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="keywords" content="" />				<!-- ????? 便于搜索 -->
		<meta name="description" content="" />
		
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
		</script>
	</head>
	
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
<!--
		<div id="pic" class="example2" width="100%">
			<ul>
	            <li><img src="../img/wuyuan2.jpg" alt="1"/></li>
	            <li><img src="../img/wuyuan3.jpg" alt="2"/></li>
	            <li><img src="../img/wuyuan7.jpg" alt="3"/></li>
	            <li><img src="../img/wuyuan8.jpg" alt="4"/></li>
			</ul>
			<ol>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
			</ol>
		</div>
		<script>
			$(function(){
				$(".example2").luara({width:"500",height:"334",interval:4500,selected:"selected",deriction:"left"});
			});
		</script>
-->

		<div>
			<table class="t1" frame="border" border="1" align="center" style="margin-bottom: 0;"> <!-- rules="none" -->
				<tr>
					<td width="50%">总蜜券</td>
					<td width="50%">当日蜜券</td>
				</tr>
				<tr>
					<td id="totalexpense"><?php if ($row) echo $row["TotalConsumption"]; else echo '0'; ?></td>
					<td id="todayobtain"><?php if ($row) echo $dayObtained; else echo '0'; ?></td>
				</tr>
			</table>
			<table class="t1" border="1" align="center" style="margin-top: 0;">
				<tr>
					<td width="33.3%">蜜券</td>
					<td width="33.3%">当月蜜券</td>
					<td width="33.3%">蜂值</td>
				</tr>
				<tr>
					<td id="point"><?php if ($row) echo $row["Credits"]; else echo '0'; ?></td>
					<td id="todayexpense"><?php if ($row) echo $monConsumption; else echo '0'; ?></td>
					<td id="bonuspool"><?php echo $feng; ?></td>
				</tr>
			</table>
		</div>

<!--
		<div id="menu">
			<p class="navhref"><a href="products.html">购物</a></p>
			<p class="navhref"><a href="register.html">推荐用户</a></p>
			<p class="navhref"><a href="charge.php">充值申请</a></p>
			<p class="navhref"><a href="withdraw.php">提现申请</a></p>
			<p class="navhref"><a href="order.php">订单查询</a></p>
			<p class="navhref"><a href="">资金记录</a></p>
			<p class="navhref"><a href="contactus.html">客服信息</a></p>
		</div>
-->
		
		<div class="btn_box" width="auto">
			<ul>
				<li><a class="icon_btn1" href="recommend.php">推荐蜜粉</a></li>
<!-- 				<li><a class="icon_btn1" href="products.html">蜂值倍增</a></li> -->
				<li><a class="icon_btn1" href="productdetail.php?product_id=1">蜂值倍增</a></li>
				<li><a class="icon_btn2" href="recommended.html">蜜粉好友</a></li>
				<li><a class="icon_btn6" href="charge.php">购买蜜券</a></li>
				<li><a class="icon_btn5" href="withdraw.php">蜜券提现</a></li>
				<li><a class="icon_btn3" href="#">蜜券互转</a></li>
				<li><a class="icon_btn7" href="order.php">订单查询</a></li>
				<li><a class="icon_btn4" href="record.php">蜜券记录</a></li>
				<li><a class="icon_btn3" href="contactus.html">客服信息</a></li>
<!-- 				<li><a href="#"></a></li> -->
			</ul>
		</div>
		
		<div id="post">
			<h3>公告：</h3>
		</div>
	</body>
</html>