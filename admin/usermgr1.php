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
			
			function queryUserByCond()
			{
// 				var lvl = document.getElementById("input_lvl").value;
				var recoLow = document.getElementById("input_recoLow").value;
				var recoHigh = document.getElementById("input_recoHigh").value;
				
// 				lvl = $.trim(lvl);
				recoLow = $.trim(recoLow);
				recoHigh = $.trim(recoHigh);
				
				if (recoLow == "" && recoHigh == "") {
					alert("条件不能都为空！");
					return;
				}
				
				$.post("../php/usrMgr.php", {"func":"qubd","rlow":recoLow,"rhigh":recoHigh}, function(data){
					
					var container = document.getElementById("user_tbl1");
				    var rowNum = container.rows.length;
				     for (i=1;i<rowNum;++i)
				     {
				         container.deleteRow(i);
				         rowNum=rowNum-1;
				         i=i-1;
				     }
					if (data.error == "false") {
						document.getElementById("span_res").innerHTML = "查询到符合条件的用户共" + data.cnt + "人！";
						
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
							d4.innerHTML = list[key].credit;
							trow.appendChild(d4);
							var d5 = document.createElement("td");
							d5.innerHTML = list[key].pnt;
							trow.appendChild(d5);
							var d6 = document.createElement("td");
							d6.innerHTML = list[key].vault;
							trow.appendChild(d6);
							var d7 = document.createElement("td");
							d7.innerHTML = list[key].RecoCnt;
							trow.appendChild(d7);
							var d8 = document.createElement("td");
							d8.innerHTML = list[key].ChildCnt;
							trow.appendChild(d8);
						}
					}
					else {
						document.getElementById("span_res").innerHTML = "查询失败！";
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
	        <input type="button" value="添加新用户" onclick="showAddBlk()" />
 	        <input type="button" value="账户查询" onclick="showQueryBlk()" />
 	        <input type="button" value="条件查询" onclick="showCondQueryBlk()" />
	        <div id="blk_add" style="display: none">
		        <input type="hidden" name='func' value="addNew" />
		        电话: <input type="text" name="phonenum" id="phonenum" placeholder="请填写用户手机号！" onkeypress="return onlyNumber(event)"/>
		        <br>
		        姓名: <input type="text" name="name" id="name" placeholder="请填写用户姓名" />
				<br>
				<input type="button" name="submit" value="添加" onclick="addUser()" />
	        </div>
-->
			<div id="blk_condQuery">
<!-- 				等级<input type="text" id="input_lvl" placeholder="请输入等级" /> -->
				推荐人数<input type="text" id="input_recoLow" /> ~ <input type="text" id="input_recoHigh" />
				<input type="button" value="查询" onclick="queryUserByCond()" />
				<br>
				<span id="span_res"></span>
				<table id="user_tbl1" border="1">
					<tr>
						<th>用户id</th>
						<th>昵称</th>
						<th>电话号码</th>
						<th>线上云量</th>
						<th>线下云量</th>
						<th>财富云量</th>
						<th>推荐人数</th>
						<th>队友人数</th>
					</tr>
				</table>

			</div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>