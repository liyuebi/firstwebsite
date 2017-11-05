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
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		
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
			
			function changeSCreditL()
			{
				var val = document.getElementById("sCreditL").value;
				$.post("../php/changeConfig.php", {"func":"changeSCL","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function changeSCreditM()
			{
				var val = document.getElementById("sCreditM").value;
				$.post("../php/changeConfig.php", {"func":"changeSCM","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function changeEL()
			{
				var val = document.getElementById("exL").value;
				$.post("../php/changeConfig.php", {"func":"changeEL","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");	
			}
			
			function changeEM()
			{
				var val = document.getElementById("exM").value;
				$.post("../php/changeConfig.php", {"func":"changeEM","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function changePChargeL()
			{
				var val = document.getElementById("pChargeL").value;
				$.post("../php/changeConfig.php", {"func":"changePCL","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");				
			}
			
			function changePChargeM()
			{
				var val = document.getElementById("pChargeM").value;
				$.post("../php/changeConfig.php", {"func":"changePCM","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");		
			}
			
			function changeOChargeL()
			{
				var val = document.getElementById("oChargeL").value;
				$.post("../php/changeConfig.php", {"func":"changeOCL","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");				
			}
			
			function changeOChargeM()
			{
				var val = document.getElementById("oChargeM").value;
				$.post("../php/changeConfig.php", {"func":"changeOCM","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");		
			}

			function changeRBonusR()
			{
				var val = document.getElementById("referR").value;
				$.post("../php/changeConfig.php", {"func":"changeRBR","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");		
			}			
			
			function changeCBonusRRefer()
			{
				var val = document.getElementById("cRRefer").value;
				$.post("../php/changeConfig.php", {"func":"changeCRR1","val":val}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");		
			}			
		
			function changeCBonusReinv()
			{
				var val = document.getElementById("cRReinv").value;
				$.post("../php/changeConfig.php", {"func":"changeCRR2","val":val}, function(data){
					
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
				<table class="table table-striped table-bordered table-condensed" style="max-width: 500px">
					<tr>
						<th align="center">参数名</th><th>值</th><th>操作</th>
					</tr>
					<tr>
						<td>推荐奖比例</td>
						<td><input type="text" id="referR" value="<?php echo $referBonusRate;?>"</td>
						<td><input type="button" class="btn btn-default" value="修改" onclick="changeRBonusR()" /></td>
					</tr>
					<tr>
						<td>直推对碰奖比例</td>
						<td><input type="text" id="cRRefer" value="<?php echo $colliBonusRateRefer;?>"</td>
						<td><input type="button" class="btn btn-default" value="修改" onclick="changeCBonusRRefer()" /></td>
					</tr>
					<tr>
						<td>复投对碰奖比例</td>
						<td><input type="text" id="cRReinv" value="<?php echo $colliBonusRateReinv;?>"</td>
						<td><input type="button" class="btn btn-default" value="修改" onclick="changeCBonusReinv()" /></td>
					</tr>
					<tr>
						<td>存储云量日利率</td>
						<td><input type="text" id="dBonusR" value="<?php echo $dayBonusRate;?>"</td>
						<td><input type="button" class="btn btn-default" value="修改" onclick="changeDBonusR()" /></td>
					</tr>
					<tr>
						<td>推荐用户消耗云量下限</td>
						<td><input type="text" id="regiCreditLeast" value="<?php echo $regiCreditLeast;  ?>" /></td>
						<td><input type="button" class="btn btn-default btn-samll" value="修改" onclick="changeRegiCreditL()" /></td>
					</tr>
					<tr>
						<td>推荐用户消耗云量上限</td>
						<td><input type="text" id="regiCreditMost" value="<?php echo $regiCreditMost;  ?>" /></td>
						<td><input type="button" class="btn btn-default" value="修改" onclick="changeRegiCreditM()" /></td>
					</tr>
					<tr>
						<td>存储云量下限</td>
						<td><input type="text" id="sCreditL" value="<?php echo $saveCreditLeast;  ?>" /></td>
						<td><input type="button" class="btn btn-default" value="修改" onclick="changeSCreditL()" /></td>
					</tr>
					<tr>
						<td>存储云量上限</td>
						<td><input type="text" id="sCreditM" value="<?php echo $saveCreditMost; ?>" /></td>
						<td><input type="button" class="btn btn-default" value="修改" onclick="changeSCreditM()" /></td>
					</tr>
					<tr>
						<td>挂单最小额度</td>
						<td><input type="text" id="exL" value="<?php echo $exchangeLeast;?>" /></td>
						<td><input type="button" class="btn btn-default" value="修改" onclick="changeEL()" /></td>
					</tr>
					<tr>
						<td>挂单最大额度</td>
						<td><input type="text" id="exM" value="<?php echo $exchangeMost;?>" /></td>
						<td><input type="button" class="btn btn-default" value="修改" onclick="changeEM()" /></td>
					</tr>
					<tr>
						<td>话费充值下限</td>
						<td><input type="text" id="pChargeL" value="<?php echo $phoneChargeLeast;?>" /></td>
						<td><input type="button" class="btn btn-default" value="修改" onclick="changePChargeL()" /></td>
					</tr>
					<tr>
						<td>话费充值上限</td>
						<td><input type="text" id="pChargeM" value="<?php echo $phoneChargeMost;?>"</td>
						<td><input type="button" class="btn btn-default" value="修改" onclick="changePChargeM()" /></td>
					</tr>
					<tr>
						<td>油费充值下限</td>
						<td><input type="text" id="oChargeL" value="<?php echo $oilChargeLeast;?>" /></td>
						<td><input type="button" class="btn btn-default" value="修改" onclick="changeOChargeL()" /></td>
					</tr>
					<tr>
						<td>油费充值上限</td>
						<td><input type="text" id="oChargeM" value="<?php echo $oilChargeMost;?>"</td>
						<td><input type="button" class="btn btn-default" value="修改" onclick="changeOChargeM()" /></td>
					</tr>
				</table>
	        </div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>