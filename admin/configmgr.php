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
			
			function changeRegiCreditL()
			{
				var val = document.getElementById("regiCreditLeast").value;
				$.post("../php/changeConfig.php", {"func":"changeRCL","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function changeRegiCreditM()
			{
				var val = document.getElementById("regiCreditMost").value;
				$.post("../php/changeConfig.php", {"func":"changeRCM","val":val}, function(data){
					
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
			
			function changeDBonusR()
			{
				var val = document.getElementById("dBonusR").value;
				$.post("../php/changeConfig.php", {"func":"changeDBR","val":val}, function(data){
					
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
				<table border="1">
					<tr>
						<th align="center">参数名</th><th>值</th><th>操作</th>
					</tr>
					<tr>
						<td>推荐用户消耗云量下限</td>
						<td><input type="text" id="regiCreditLeast" value="<?php echo $regiCreditLeast;  ?>" /></td>
						<td><input type="button" value="修改" onclick="changeRegiCreditL()" /></td>
					</tr>
					<tr>
						<td>推荐用户消耗云量上限</td>
						<td><input type="text" id="regiCreditMost" value="<?php echo $regiCreditMost;  ?>" /></td>
						<td><input type="button" value="修改" onclick="changeRegiCreditM()" /></td>
					</tr>
					<tr>
						<td>存储云量下限</td>
						<td><input type="text" id="sCreditL" value="<?php echo $saveCreditLeast;  ?>" /></td>
						<td><!-- <input type="button" value="修改" onclick="changeRegiCreditM()" /> --></td>
					</tr>
					<tr>
						<td>存储云量上限</td>
						<td><input type="text" id="sCreditM" value="<?php echo $saveCreditMost; ?>" /></td>
						<td><!-- <input type="button" value="修改" onclick="changeNUFeng()" /> --></td>
					</tr>
					<tr>
						<td>挂单最小额度</td>
						<td><input type="text" id="exL" value="<?php echo $exchangeLeast;?>" /></td>
						<td><!-- <input type="button" value="修改" onclick="changeFloor()" /> --></td>
					</tr>
					<tr>
						<td>挂单最大额度</td>
						<td><input type="text" id="exM" value="<?php echo $exchangeMost;?>" /></td>
						<td><!-- <input type="button" value="修改" onclick="changeCeil()" /> --></td>
					</tr>
					<tr>
						<td>话费充值下限</td>
						<td><input type="text" id="$pChargeL" value="<?php echo $phoneChargeLeast;?>" /></td>
						<td><!-- <input type="button" value="修改" onclick="changeTransferFloor()" /> --></td>
					</tr>
					<tr>
						<td>话费充值上限</td>
						<td><input type="text" id="pChargeM" value="<?php echo $phoneChargeMost;?>"</td>
						<td><!-- <input type="button" value="修改" onclick="changeWithdrawHandleRate()" /> --></td>
					</tr>
					<tr>
						<td>油费充值下限</td>
						<td><input type="text" id="$oChargeL" value="<?php echo $oilChargeLeast;?>" /></td>
						<td><!-- <input type="button" value="修改" onclick="changeTransferFloor()" /> --></td>
					</tr>
					<tr>
						<td>油费充值上限</td>
						<td><input type="text" id="oChargeM" value="<?php echo $oilChargeMost;?>"</td>
						<td><!-- <input type="button" value="修改" onclick="changeWithdrawHandleRate()" /> --></td>
					</tr>
					<tr>
						<td>存储云量日利率</td>
						<td><input type="text" id="dBonusR" value="<?php echo $dayBonusRate;?>"</td>
						<td><input type="button" value="修改" onclick="changeDBonusR()" /></td>
					</tr>
				</table>
	        </div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>