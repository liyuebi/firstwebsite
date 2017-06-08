<?php

include "../php/admin_func.php";

$res = false;

if (!checkLoginOrJump()) {
	return;
}

include "../php/database.php";
$con = connectToDB();
if (!$con)
{
	return false;
}
	
$res = mysql_query("select * from AdminTable where Name!='admin'");
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>管理员管理</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/md5.js" ></script>
		<script type="text/javascript">
			
			function switchToAdmin()
			{
				document.getElementById("blk_currAdmin").style.display = "block";
				document.getElementById("blk_changepwd").style.display = "none";
				document.getElementById("blk_add").style.display = "none";	
			}
			
			function switchToChange()
			{
				document.getElementById("blk_currAdmin").style.display = "none";
				document.getElementById("blk_changepwd").style.display = "block";
				document.getElementById("blk_add").style.display = "none";
			}
			
			function switchToAdd()
			{
				document.getElementById("blk_currAdmin").style.display = "none";
				document.getElementById("blk_changepwd").style.display = "none";
				document.getElementById("blk_add").style.display = "block";	
			}
			
			function resetPwd()
			{
				var oldPwd = document.getElementById("pwd1").value;
				var newpwd1 = document.getElementById("pwd2").value;
				var newpwd2 = document.getElementById("pwd3").value;
				
				oldPwd = $.trim(oldPwd);
				newpwd1 = $.trim(newpwd1);
				newpwd2 = $.trim(newpwd2);
				
				if (newpwd1 != newpwd2) {
					alert("两次输入的密码不一致！");
					return;
				}
				
				if (newpwd1 == "") {
					alert("新密码不能为空！");
					return;
				}
				
				if (oldPwd == newpwd1) {
					alert("新密码和旧密码一样，请重新输入！");
					return;
				}
				
				
				oldPwd = md5(oldPwd);
				newpwd1 = md5(newpwd1);
				$.post("../php/login.php", {"func":"admChP","opd":oldPwd,"npd":newpwd1}, function(data){
					if (data.error == "false") {
						alert("修改密码成功！");
						document.getElementById("pwd1").value = "";
						document.getElementById("pwd2").value = "";
						document.getElementById("pwd3").value = "";
						
						switchToAdmin();
					}
					else {
						alert("修改密码失败：" + data.error_msg);
					}
				}, "json");
			}
			
			function cancel()
			{
				document.getElementById("pwd1").value = "";
				document.getElementById("pwd2").value = "";
				document.getElementById("pwd3").value = "";
			}
			
			function addAccount()
			{
				var account = document.getElementById("account").value;
				var pwd1 = document.getElementById("pwd4").value;
				var pwd2 = document.getElementById("pwd5").value;	
				
				account = $.trim(account);
				pwd1 = $.trim(pwd1);
				pwd2 = $.trim(pwd2);
				
				if (pwd1 != pwd2) {
					alert("两次输入的密码不一致！");
					return;
				}
				
				if (pwd1 == "") {
					alert("密码不能为空！");
				}
				
				pwd1 = md5(pwd1);
				$.post("../php/login.php", {"func":"admAddA","acc":account,"pd":pwd1}, function(data){
					if (data.error == "false") {
						alert("添加新账号成功！");
						document.getElementById("account").value = "";
						document.getElementById("pwd4").value = "";
						document.getElementById("pwd5").value = "";
						
						switchToAdmin();
					}
					else {
						alert("添加新账号失败：" + data.error_msg);
					}
				}, "json");
			}
			
			function cancel1()
			{
				document.getElementById("account").value = "";
				document.getElementById("pwd4").value = "";
				document.getElementById("pwd5").value = "";
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
<!-- 				<li><a href="configRwdRate.php">配置动态拨比</a></li> -->
				<li><a href="postmgr.php">公告管理</a></li>
				<li><a href="adminmgr.php">管理员账号维护</a></li>
			</ul>
		</div>
		<div style="display: inline; float: left; padding: 10px 0 0 10px;" >
			<div>
				<input type="button" value="修改密码" onclick="switchToChange()" />
				<input type="button" value="添加管理员" onclick="switchToAdd()" />
			</div>
			<div id='blk_currAdmin'>
				<table border="1">
					<tr>
						<th>账户名</th>
					</tr>
					<?php
						if ($res) {
							while ($row = mysql_fetch_array($res)) {
					?>
								<td><?php echo $row["Name"]; ?></td>
					<?php
							}
						}
					?>
				</table>
			</div>
			<div id='blk_changepwd' style="display: none;">
				<input type="password" id="pwd1" placeholder="请输入原密码" />
				<br>
				<input type="password" id="pwd2" placeholder="请输入新密码" />
				<br>
				<input type="password" id="pwd3" placeholder="请再次输入新密码" />
				<br>
				<input type="button" value="确认修改" onclick="resetPwd()" />
				<input type="button" value="取消" onclick="cancel()" />
			</div>
			<div id='blk_add' style="display: none;">
				<input type="text" id="account" placeholder="请输入账户名称" />
				<br>
				<input type="password" id="pwd4" placeholder="请输入密码" />
				<br>
				<input type="password" id="pwd5" placeholder="请再次输入密码" />
				<br>
				<input type="button" value="确认添加" onclick="addAccount()" />
				<input type="button" value="放弃添加" onclick="cancel1()" />
			</div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>
