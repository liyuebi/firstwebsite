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
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		
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
						
						var list = data.list;
						for (var key in list) {
							
							var trow = document.createElement("tr");
							container.appendChild(trow);
							
							var d1 = document.createElement("td");
							d1.innerHTML = key;
							trow.appendChild(d1);
							var d2 = document.createElement("td");
							d2.innerHTML = list[key].nickname;
							trow.appendChild(d2);
							var d3 = document.createElement("td");
							d3.innerHTML = list[key].phone;
							trow.appendChild(d3);
							var d4 = document.createElement("td");
							d4.id = "credit_" + key;
							d4.innerHTML = list[key].credit;
							trow.appendChild(d4);
							var d16 = document.createElement("td");
							d16.innerHTML = list[key].shareCredit;
							trow.appendChild(d16);
							var d5 = document.createElement("td");
							d5.innerHTML = list[key].pnt;
							trow.appendChild(d5);
							var d15 = document.createElement("td");
							d15.innerHTML = list[key].profit;
							trow.appendChild(d15);
							var d6 = document.createElement("td");
							d6.innerHTML = list[key].vault;
							trow.appendChild(d6);
							var d7 = document.createElement("td");	
							d7.innerHTML = list[key].RecoCnt;
							trow.appendChild(d7);
							var d8 = document.createElement("td");	
							d8.innerHTML = list[key].ChildCnt;
							trow.appendChild(d8);
							
							var d10 = document.createElement("td");	
							trow.appendChild(d10);
							var input1 = document.createElement("input");
							input1.type = "button";
							input1.value = "更改用户信息";
							input1.className = "btn btn-info btn-sm";
							input1.id = key;
							if (input1.addEventListener) {
								input1.addEventListener('click', changeUserInfo, false);
							}
							else if (input1.attachEvent) {
								input1.attachEvent('onclick', changeUserInfo);
							}
							d10.appendChild(input1);

							var d11 = document.createElement("td");	
							trow.appendChild(d11);
							var input2 = document.createElement("input");
							input2.type = "text";
							input2.placeholder = "变动数量";
							input2.id = "amt_" + key;
							d11.appendChild(input2);

							var d12 = document.createElement("td");	
							trow.appendChild(d12);
							var input3 = document.createElement("input");
							input3.type = "button";
							input3.value = "增加";
							input3.className = "btn btn-info btn-sm";
							input3.id = key;
							if (input3.addEventListener) {
								input3.addEventListener('click', addCredit, false);
							}
							else if (input3.attachEvent) {
								input3.attachEvent('onclick', addCredit);
							}
							d12.appendChild(input3);
							var input4 = document.createElement("input");
							input4.type = "button";
							input4.value = "减少";
							input4.className = "btn btn-info btn-sm";
							input4.id = key;
							if (input4.addEventListener) {
								input4.addEventListener('click', decreaseCredit, false);
							}
							else if (input4.attachEvent) {
								input4.attachEvent('onclick', decreaseCredit);
							}
							d12.appendChild(input4);
						}
					}
					else {
						alert("查询失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function changeUserInfo(e)
			{
				location.href = "editUserInfo.php?idx=" + e.target.id;
			}
			
			function addCredit(e)
			{
				var key = "amt_" + e.target.id;
				var amount = document.getElementById(key).value;

				$.post("../php/usrMgr.php", {"func":"auc","uid":e.target.id,"val":amount}, function(data){
					if (data.error == "false") {
						alert("增加线上云量成功！");
						document.getElementById(key).value = "";
						document.getElementById("credit_" + e.target.id).innerHTML = data.credit;
						document.getElementById("credit_" + e.target.id).style.color = "red";
					}
					else {
						alert("增加线上云量失败：" + data.error_msg);
					}
				}, "json");
			}
			
			function decreaseCredit(e)
			{
				var key = "amt_" + e.target.id;
				var amount = document.getElementById(key).value;
				$.post("../php/usrMgr.php", {"func":"duc","uid":e.target.id,"val":amount}, function(data){
					if (data.error == "false") {
						alert("减少线上云量成功！");
						document.getElementById(key).value = "";
						document.getElementById("credit_" + e.target.id).innerHTML = data.credit;
						document.getElementById("credit_" + e.target.id).style.color = "red";
					}
					else {
						alert("减少线上云量失败：" + data.error_msg);
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
			<div id="blk_chk" style="display: block;">
				<div class="input-group" style="width: 360px; margin-bottom: 10px;">
					<input type="text" id="id_input" class="form-control" placeholder="请输入用户 ID/手机号／昵称" />
					<div class="input-group-btn">
						<input type="button" class="btn btn-default" value="查找" onclick="queryUser()" />
					</div>
				</div>
				
				<table id="user_tbl" border="1" style="text-align: center">
					<tr>
						<th>用户id</th>
						<th>昵称</th>
						<th>电话号码</th>
						<th>线上云量</th>
						<th>分享云量</th>
						<th>线下云量</th>
						<th>消费云量</th>
						<th>财富云量</th>
						<th>推荐人数</th>
						<th>队友人数</th>
<!-- 						<th>购物总数</th> -->
						<th>操作</th>
						<th>线上云量变动</th>
						<th>变动操作</th>
					</tr>
				</table>
			</div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>