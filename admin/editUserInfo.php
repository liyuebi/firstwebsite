<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}
	
$userid = $_GET["idx"];

$res = false;
$res1 = false;

include "../php/database.php";
$con = connectToDB();
if ($con) {
	
	$res = mysqli_query($con, "select * from ClientTable where UserId='$userid'");
	$res1 = mysqli_query($con, "select * from Credit where UserId='$userid'");
}
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>用户信息更改</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">

			function changeNickname()
			{
				var val = document.getElementById("nickn").value;
				val = $.trim(val);
				
				$.post("../php/usrMgr.php", {"func":"cun","uid":<?php echo $userid; ?>,"val":val}, function(data){
						
					if (data.error == "false") {
		
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}

			function changePhone()
			{
				var val = document.getElementById("phone").value;
				val = $.trim(val);
				if (val.length <= 0) {
					alert("不能为空！");	
					return;
				}
				
				$.post("../php/usrMgr.php", {"func":"cuphone","uid":<?php echo $userid; ?>,"val":val}, function(data){
						
					if (data.error == "false") {
		
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}

			function changeCredits()
			{
				var val = document.getElementById("credit").value;
				val = $.trim(val);
				if (val.length <= 0) {
					alert("不能为空！");	
					return;
				}
				
				$.post("../php/usrMgr.php", {"func":"cuc","uid":<?php echo $userid; ?>,"val":val}, function(data){
						
					if (data.error == "false") {
		
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}

			function changePnts()
			{
				var val = document.getElementById("pnts").value;
				val = $.trim(val);
				if (val.length <= 0) {
					alert("不能为空！");	
					return;
				}
				
				$.post("../php/usrMgr.php", {"func":"cup","uid":<?php echo $userid; ?>,"val":val}, function(data){
						
					if (data.error == "false") {
		
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}

			function changeProfit()
			{
				var val = document.getElementById("profit").value;
				val = $.trim(val);
				if (val.length <= 0) {
					alert("不能为空！");	
					return;
				}
				
				$.post("../php/usrMgr.php", {"func":"cup1","uid":<?php echo $userid; ?>,"val":val}, function(data){
						
					if (data.error == "false") {
		
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}

/*
			function changeVault()
			{
				$.post("../php/usrMgr.php", {"func":"cuc","uid":<?php echo $userid; ?>,"val":val}, function(data){
						
					if (data.error == "false") {
		
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}
*/
									
			function resetLoginPwd(e)
			{
				if (!confirm("你确定要重置用户的登录密码吗？")) {

					return;
				}
				$.post("../php/usrMgr.php", {"func":"rlp","uid":<?php echo $userid; ?>}, function(data){

					if (data.error == "false") {
						alert("重置登录密码成功！");
					}
					else {
						alert("重置登录密码失败：" + data.error_msg);
					}
				}, "json");
			}
			
			function resetPayPwd(e)
			{
				if (!confirm("你确定要清空用户的支付密码吗？")) {

					return;
				}
				$.post("../php/usrMgr.php", {"func":"rpp","uid":<?php echo $userid; ?>}, function(data){

					if (data.error == "false") {
						alert("清空支付密码成功！");
					}
					else {
						alert("清空支付密码失败：" + data.error_msg);
					}
				}, "json");
			}

			function checkVault(e)
			{
				location.href = "creditbankrec.php?uid=<?php echo $userid; ?>";
			}
		</script>
	</head>
	<body>
		<div style="padding: 10px 0 0 10px;" >
	        <div>
		        <h3>用户信息更改</h3>
	        </div>

	        <hr>
			<div id="blk_chk" style="display: block;">
				<table id="user_tbl" border="1">
					<?php 
						if ($res && mysqli_num_rows($res) > 0
							&& $res1 && mysqli_num_rows($res1) > 0) {
							$row = mysqli_fetch_assoc($res);
							$row1 = mysqli_fetch_assoc($res1);
					?>
					<tr>
						<th>属性</th>
						<th>值</th>
						<th>操作</th>
					</tr>
					<tr>
						<td>用户id</td>
						<td><?php echo $row["UserId"]; ?></td>
					</tr>
					<tr>
						<td>昵称</td>
						<td><input type="text" id="nickn" value="<?php echo $row["NickName"]; ?>" /></td>
						<td><input type="button" value="更改" onclick="changeNickname()" /></td>
					</tr>
					<tr>
						<td>电话号码</td>
						<td><input type="text" id="phone" value="<?php echo $row["PhoneNum"]; ?>" /></td>
						<td><input type="button" value="更改" onclick="changePhone()" /></td>
					</tr>
					<tr>
						<td>线上云量</td>
						<td><input type="text" id="credit" value="<?php echo $row1["Credits"]; ?>" /></td>
						<td><input type="button" name="submit" value="更改" onclick="changeCredits()" /></td>
					</tr>
					<tr>
						<td>线下云量</td>
						<td><input type="text" id="pnts" value="<?php echo $row1["Pnts"]; ?>" /></td>
						<td><input type="button" name="submit" value="更改" onclick="changePnts()" /></td>
					</tr>
					<tr>
						<td>消费云量</td>
						<td><input type="text" id="profit" value="<?php echo $row1["ProfitPnt"]; ?>" /></td>
						<td><input type="button" name="submit" value="更改" onclick="changeProfit()" /></td>
					</tr>
					<tr>
						<td>财富云量</td>
						<td><?php echo $row1["Vault"]; ?></td>
<!-- 						<td><input type="text" id="vault" value="<?php echo $row1["Vault"]; ?>" /></td> -->
						<td><input type="button" name="submit" value="查看" onclick="checkVault(this)" /></td>
<!-- 						<td><input type="button" name="submit" value="更改" onclick="changeVault()" /></td> -->
					</tr>
					<tr>
						<td>登录密码</td>
						<td></td>
						<td><input type="button" name="submit" value="重置" onclick="resetLoginPwd(this)" /></td>
					</tr>
					<tr>
						<td>支付密码</td>
						<td></td>
						<td><input type="button" name="submit" value="重置" onclick="resetPayPwd(this)" /></td>
					</tr>
					<?php
						}
					?>
				</table>
			</div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>