<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>用户管理</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			function showAddBlk()
			{
				document.getElementById("blk_add").style.display = "block"; 
				document.getElementById("blk_chk").style.display = "none";
				document.getElementById("blk_condQuery").style.display = "none";
			}
			
			function showQueryBlk()
			{
				document.getElementById("blk_chk").style.display = "block";
				document.getElementById("blk_condQuery").style.display = "none";
				document.getElementById("blk_add").style.display = "none";
			}
			
			function showCondQueryBlk()
			{
				document.getElementById("blk_chk").style.display = "none";
				document.getElementById("blk_condQuery").style.display = "block";
				document.getElementById("blk_add").style.display = "none";				
			}
			
			function addUser()
			{
				var phonenum = document.getElementById("phonenum").value;
				var name = document.getElementById("name").value;
				if (!isPhoneNumValid(phonenum)) {
					alert("无效的电话号码！");
					return;
				}
				if (name == "") {
					alert("没有输入姓名！");
					return;
				}
				
				$.post("../php/usrMgr.php", {"func":"addUser","phone":phonenum,"name":name}, function(data){
					
					if (data.error == "false") {
						alert("设置成功！");	
						document.getElementById("phonenum").value = "";
						document.getElementById("name").value = "";						
					}
					else {
						alert("设置失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function queryUser()
			{
				var uid = document.getElementById("id_input").value;

				if (uid.length <= 0) {
					return;
				}
				
				$.post("../php/usrMgr.php", {"func":"queryUser","uid":uid}, function(data){
					
					if (data.error == "false") {
					
						var container = document.getElementById("user_tbl");
						var trow = document.createElement("tr");
						container.appendChild(trow);
						
						var d1 = document.createElement("td");
						d1.innerHTML = data.id;
						trow.appendChild(d1);
						var d2 = document.createElement("td");
						d2.innerHTML = data.nickname;
						trow.appendChild(d2);
						var d3 = document.createElement("td");
						d3.innerHTML = data.phone;
						trow.appendChild(d3);
						var d4 = document.createElement("td");
						d4.innerHTML = data.credit;
						trow.appendChild(d4);
						var d5 = document.createElement("td");
						d5.innerHTML = data.pnt;
						trow.appendChild(d5);
						var d6 = document.createElement("td");
						d6.innerHTML = data.vault;
						trow.appendChild(d6);
						var d7 = document.createElement("td");	
						d7.innerHTML = data.RecoCnt;
						trow.appendChild(d7);
						var d8 = document.createElement("td");	
						d8.innerHTML = data.ChildCnt;
						trow.appendChild(d8);
						
						var d10 = document.createElement("td");	
						trow.appendChild(d10);
						var input1 = document.createElement("input");
						input1.type = "button";
						input1.value = "重置登录密码";
						input1.id = data.id;
						if (input1.addEventListener) {
							input1.addEventListener('click', resetLoginPwd, false);
						}
						else if (input1.attachEvent) {
							input1.attachEvent('onclick', resetLoginPwd);
						}
						d10.appendChild(input1);
						var input2 = document.createElement("input");
						input2.type = "button";
						input2.value = "清空支付密码";
						input2.id = data.id;
						if (input2.addEventListener) {
							input2.addEventListener('click', resetPayPwd, false);
						}
						else if (input2.attachEvent) {
							input2.attachEvent('onclick', resetPayPwd);
						}
						d10.appendChild(input2);
					}
					else {
						alert("查询失败: " + data.error_msg);
					}
				}, "json");
			}
						
			function resetLoginPwd(e)
			{
				if (!confirm("你确定要重置用户" + e.target.id + "的登录密码吗？")) {
					return;
				}
				$.post("../php/usrMgr.php", {"func":"rlp","uid":e.target.id}, function(data){
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
				if (!confirm("你确定要清空用户" + e.target.id + "的支付密码吗？")) {
					return;
				}
				$.post("../php/usrMgr.php", {"func":"rpp","uid":e.target.id}, function(data){
					if (data.error == "false") {
						alert("清空支付密码成功！");
					}
					else {
						alert("清空支付密码失败：" + data.error_msg);
					}
				}, "json");
			}
		</script>
	</head>
	<body>
		<div style="padding: 10px 0 0 10px;" >
	        <div>
		        <h3>用户管理</h3>
	        </div>
<!--
	        <div id="blk_add">
		        <input type="hidden" name='func' value="addNew" />
		        电话: <input type="text" name="phonenum" id="phonenum" placeholder="请填写用户手机号！" onkeypress="return onlyNumber(event)"/>
		        <br>
		        姓名: <input type="text" name="name" id="name" placeholder="请填写昵称" />
				<br>
				推荐人: <input type="texxt" name="referer" id="referer" placeholder="请填写推荐人" />
				<input type="button" name="submit" value="添加" onclick="addUser()" />
	        </div>
-->
	        <hr>
			<div id="blk_chk" style="display: block;">
				<input id="id_input" type="text" placeholder="请输入用户id" />
				<input type="button" value="查找" onclick="queryUser()" />
				
				<table id="user_tbl" border="1">
					<tr>
						<th>用户id</th>
						<th>昵称</th>
						<th>电话号码</th>
						<th>线上云量</th>
						<th>线下云量</th>
						<th>财富云量</th>
						<th>推荐人数</th>
						<th>队友人数</th>
<!-- 						<th>购物总数</th> -->
						<th>操作</th>
					</tr>
				</table>
			</div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>