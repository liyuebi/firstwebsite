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
				<table border="1">
					<tr>
						<th align="center">参数名</th><th>值</th><th>操作</th>
					</tr>
<!--
					<tr>
						<td>一蜂值</td>
						<td><?php echo $fengzhiValue; ?></td>
					</tr>
					<tr>
						<td>新用户初始动态蜂值</td>
						<td><input type="text" id="newUserFeng" value="" /></td>
						<td><input type="button" value="修改" onclick="changeNUFeng()" /></td>
					</tr>
-->
					<tr>
						<td>复投一单增加固定蜂值</td>
						<td><input type="text" id="newAccntFeng" value="<?php echo $dyNewAccountVault;  ?>" /></td>
						<td><input type="button" value="修改" onclick="changeNewAntFeng()" /></td>
					</tr>
					<tr>
						<td>推荐用户消耗蜜券</td><td><?php echo $refererConsumePoint;?></td>
					</tr>
					<tr>
						<td>每次提现下限</td>
						<td><input type="text" id="floor" value="<?php echo $withdrawFloorAmount;?>" /></td>
						<td><input type="button" value="修改" onclick="changeFloor()" /></td>
					</tr>
					<tr>
						<td>每日提现上限</td>
						<td><input type="text" id="ceil" value="<?php echo $withdrawCeilAmountOneDay;?>" /></td>
						<td><input type="button" value="修改" onclick="changeCeil()" /></td>
					</tr>
					<tr>
						<td>单笔转账下限</td>
						<td><input type="text" id="transferFloor" value="<?php echo $transferFloorAmount;?>" /></td>
						<td><input type="button" value="修改" onclick="changeTransferFloor()" /></td>
					</tr>
					<tr>
						<td>提现手续费</td>
						<td><input type="text" id="withdrawRate" value="<?php echo $withdrawHandleRate;?>"</td>
						<td><input type="button" value="修改" onclick="changeWithdrawHandleRate()" /></td>
					</tr>
					<tr>
						<td>转账手续费</td>
						<td><input type="text" id="transferRate" value="<?php echo $transferHandleRate;?>"</td>
						<td><input type="button" value="修改" onclick="changeTransferHandleRate()" /></td>
					</tr>
<!--
					<tr>
						<td>动态分红比例</td>
						<td><input type="text" id="rwdRate" value="<?php echo $rewardRate;?>" /></td>
						<td><input type="button" value="修改" onclick="changeRewardRate()" /></td>
					</tr>					
-->
				</table>
	        </div>
	        <div>
		        <table border="1" align="center" style="margin: auto; text-align: center;">
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