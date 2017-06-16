<?php

include "../php/constant.php";
include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	exit();
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>配置管理</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function changeNUFeng()
			{
				var val = document.getElementById("newUserFeng").value;
				$.post("../php/changeConfig.php", {"func":"changeNUF","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function changeNewAntFeng()
			{
				var val = document.getElementById("newAccntFeng").value;
				$.post("../php/changeConfig.php", {"func":"changeNAF","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function changeFloor()
			{
				var val = document.getElementById("floor").value;
				$.post("../php/changeConfig.php", {"func":"changeFloor","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function changeCeil()
			{
				var val = document.getElementById("ceil").value;
				$.post("../php/changeConfig.php", {"func":"changeCeil","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function changeTransferFloor()
			{
				var val = document.getElementById("transferFloor").value;
				$.post("../php/changeConfig.php", {"func":"changeTransferFloor","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");	
			}
			
			function changeRewardRate()
			{
				var val = document.getElementById("rwdRate").value;
				$.post("../php/changeConfig.php", {"func":"changeRwdRate","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function changeWithdrawHandleRate()
			{
				var val = document.getElementById("withdrawRate").value;
				$.post("../php/changeConfig.php", {"func":"changeWHR","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");				
			}
			
			function changeTransferHandleRate()
			{
				var val = document.getElementById("transferRate").value;
				$.post("../php/changeConfig.php", {"func":"changeTHR","val":val}, function(data){
					
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
		<div style="padding: 10px 0 0 10px;" >
	        <div>
		        <table border="1" style="text-align: center;">
			        <tr>
				        <th>等级</th><th>称号</th><th>一组人数</th><th>二组人数</th><th>三组人数</th><th>分红总额</th><th>每日分红</th><th>升级奖励</th><th>每日采蜜券比例</th><th>可复投次数</th>
			        </tr>
<!--
			        <tr>
				        <td>1</td><td>蜂粉</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
			        </tr>
-->
					<?php 
						$cnt = count($levelBonus);
						$idx = 0;
						while ($idx < $cnt) {
					?>
					<tr>
						<td><?php echo ($idx + 1); ?></td>
						<td><?php echo $levelName[$idx]; ?></td>
						<td><?php echo $team1Cnt[$idx]; ?></td>
						<td><?php echo $team2Cnt[$idx]; ?></td>
						<td><?php echo $team3Cnt[$idx]; ?></td>
						<td><?php echo $levelBonus[$idx]; ?></td>
						<td><?php echo $levelDayBonus[$idx]; ?></td>
						<td><?php echo $levelUpBonus[$idx]; ?></td>
						<td><?php echo $levelPntsRate[$idx]; ?></td>
						<td><?php echo $levelReinvestTime[$idx]; ?></td>
					</tr>
					<?php
							++$idx;
						}
					?>
		        </table>
	        </div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>