<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include "../php/database.php";
include "../php/constant.php";

$result = false;
$row1 = false;
$gross = 0;

$con = connectToDB();
if (!$con)
{
	return false;
}

$res1 = mysql_query("select * from ShortStatis");
if ($res1) {
	$row1 = mysql_fetch_assoc($res1);
	$gross = $row1["OrderGross"];
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>配置分配比例</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function calc()
			{
				var feng = document.getElementById("feng").innerHTML;
				feng = parseInt(feng);
				
				if (0 == feng) {
					document.getElementById("bonus").innerHTML = "0";
					return;
				}
				
				var rate = document.getElementById("rwdRate").value;
				rate = parseFloat(rate);
				var bonus = Math.floor(<?php echo $gross; ?> * rate / feng * 100) / 100;
				document.getElementById("bonus").innerHTML = bonus;
			}
			
			function getTotalDFeng()
			{
				$.post("../php/usrMgr.php", {"func":"getDFeng"}, function(data){
					
					if (data.error == "false") {
						alert("获取成功！");
						document.getElementById("feng").innerHTML = data.dfeng;
						calc();
					}
					else {
						alert("获取失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function modifyRate()
			{
				var val = document.getElementById("rwdRate").value;
				val = parseFloat(val);
				if (val < 0) {
					alert("拨比小于零，最小值为零！");
					return;
				}
				if (val > 1) {
					if (!confirm("拨比值大于1，是否确认修改?")) {
						return;
					}
				}
				$.post("../php/changeConfig.php", {"func":"changeRwdRate","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function modifyVal()
			{
				var val = document.getElementById("rwdVal").value;
				val = parseFloat(val);
				if (val < 0) {
					val = 0;
				}
				$.post("../php/changeConfig.php", {"func":"changeRwdVal","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}
		</script>
	</head>
	<body>
		<div style="padding: 10px 10px 0 5px; height: 100%; display:inline; float: left; border-right: 1px solid black;">
			<ul style="list-style: none; padding: 0">
<!-- 				<li><a href="companymgr.html">企业管理</a></li> -->
				<li><a href="productmgr.php">产品管理</a></li>
				<li><a href="usermgr.php">用户管理</a></li>
				<li><a href="ordermgr.php">订单管理</a></li>
				<li><a href="rechargemgr.php">充值管理</a></li>
				<li><a href="withdrawmgr.php">取现管理</a></li>
				<li><a href="configmgr.php">配置管理</a></li>
				<li><a href="statistics.php">统计数据</a></li>
				<li><a href="configRwdRate.php">配置动态拨比</a></li>
				<li><a href="postmgr.php">公告管理</a></li>
				<li><a href="adminmgr.php">管理员账号维护</a></li>
			</ul>
		</div>
		<div style="display: inline; float: left; padding: 10px 0 0 10px;" >
			<div>
				<b><?php echo $gross; ?> </b>
				<b> * </b>
				<input id="rwdRate" type="text" value="<?php echo $rewardRate; ?>" onblur="calc()"/>
				<b> / </b>
				<b id="feng"> 0 </b>
				<b> = </b>
				<b id="bonus"> 0 </b>
			</div>
			<div>
				<input type="button" value="获得动态蜂值总数" onclick="getTotalDFeng()" />
				<input type="button" value="计算每蜂值分润" onclick="calc()" />
				<input type="button" value="修改配比" onclick="modifyRate()" />
			</div>
			<hr>
			<div>
				<p>设置动态分红值，如果大于0，则每蜂值分红使用设置的值，而不会使用计算得到的分红值。</p>
				<input id="rwdVal" type="text" value="<?php echo $rewardVal; ?>" />
				<input type="button" value="修改分红值" onclick="modifyVal()" />
			</div>
		</div>
    </body>
</html>