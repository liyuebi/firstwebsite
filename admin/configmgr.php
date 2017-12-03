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
			
			function changeVal(btn)
			{
				var inputId = btn.dataset.whatever; 
				var func = btn.dataset.func;

				var val = document.getElementById(inputId).value;
				$.post("../php/changeConfig.php", {"func":func,"val":val}, function(data){
					
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
	        	<label class="text-info">推荐奖励相关</label>
				<table class="table table-striped table-bordered table-condensed" style="max-width: 500px">
					<tr>
						<th>推荐用户消耗云量下限</th>
						<th>推荐用户消耗云量上限</th>
						<th>推荐奖比例</th>
						<th>直推对碰奖比例</th>
						<th>复投对碰奖比例</th>
					</tr>
					<tr>
						<td><input type="text" id="regiCreditLeast" value="<?php echo $regiCreditLeast;  ?>" /></td>
						<td><input type="text" id="regiCreditMost" value="<?php echo $regiCreditMost;  ?>" /></td>
						<td><input type="text" id="referR" value="<?php echo $referBonusRate;?>"</td>
						<td><input type="text" id="cRRefer" value="<?php echo $colliBonusRateRefer;?>"</td>
						<td><input type="text" id="cRReinv" value="<?php echo $colliBonusRateReinv;?>"</td>
					</tr>
					<tr>
						<td><input type="button" class="btn btn-default btn-samll" value="修改" data-whatever="regiCreditLeast" data-func='changeRCL' onclick="changeVal(this)" /></td>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="regiCreditMost" data-func='changeRCM' onclick="changeVal(this)" /></td>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="referR" data-func='changeRBR' onclick="changeVal(this)" /></td>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="cRRefer" data-func='changeCRR1' onclick="changeVal(this)" /></td>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="cRReinv" data-func='changeCRR2' onclick="changeVal(this)" /></td>
					</tr>
				</table>
				<label class="text-info">存储相关</label>
				<table class="table table-striped table-bordered table-condensed" style="max-width: 500px">
					<tr>
						<th>存储云量下限</th>
						<th>存储云量上限</th>
						<th>存储云量日利率</th>
					</tr>
					<tr>
						<td><input type="text" id="sCreditL" value="<?php echo $saveCreditLeast;  ?>" /></td>
						<td><input type="text" id="sCreditM" value="<?php echo $saveCreditMost; ?>" /></td>
						<td><input type="text" id="dBonusR" value="<?php echo $dayBonusRate;?>"</td>
					</tr>
					<tr>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="sCreditL" data-func='changeSCL' onclick="changeVal(this)" /></td>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="sCreditM" data-func='changeSCM' onclick="changeVal(this)" /></td>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="dBonusR" data-func='changeDBR' onclick="changeVal(this)" /></td>
					</tr>
				</table>
				<label class="text-info">交易所相关</label>
				<table class="table table-striped table-bordered table-condensed" style="max-width: 500px">
					<tr>
						<th>挂单最小额度</th>
						<th>挂单最大额度</th>
					</tr>
					<tr>
						<td><input type="text" id="exL" value="<?php echo $exchangeLeast;?>" /></td>
						<td><input type="text" id="exM" value="<?php echo $exchangeMost;?>" /></td>
					</tr>
					<tr>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="exL" data-func='changeEL' onclick="changeVal(this)" /></td>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="exM" data-func='changeEM' onclick="changeVal(this)" /></td>
					</tr>
				</table>
				<label class="text-info">话费油费相关</label>
				<table class="table table-striped table-bordered table-condensed" style="max-width: 500px">
					<tr>
						<th>话费充值下限</th>
						<th>话费充值上限</th>
					</tr>
					<tr>
						<td><input type="text" id="pChargeL" value="<?php echo $phoneChargeLeast;?>" /></td>
						<td><input type="text" id="pChargeM" value="<?php echo $phoneChargeMost;?>" /></td>
					</tr>
					<tr>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="pChargeL" data-func='changePCL' onclick="changeVal(this)" /></td>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="pChargeM" data-func='changePCM' onclick="changeVal(this)" /></td>
					</tr>
<!-- 					<tr>
						<td>油费充值下限</td>
						<td><input type="text" id="oChargeL" value="<?php echo $oilChargeLeast;?>" /></td>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="oChargeL" data-func='changeOCL' onclick="changeVal(this)" /></td>
					</tr>
					<tr>
						<td>油费充值上限</td>
						<td><input type="text" id="oChargeM" value="<?php echo $oilChargeMost;?>"</td>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="oChargeM" data-func='changeOCM' onclick="changeVal(this)" /></td>
					</tr>
 -->				
				</table>
				<label class="text-info">线下交易相关</label>
				<table class="table table-striped table-bordered table-condensed" style="max-width: 500px">
					<tr>
						<th>线下商家注册费</th>
						<th>提现金额下线</th>
						<th>提现金额每日上限</th>
					</tr>
					<tr>
						<td><input type="text" id="ofsRF" value="<?php echo $offlineShopRegisterFee;  ?>" /></td>
						<td><input type="text" id="wfa" value="<?php echo $withdrawFloorAmount; ?>" /></td>
						<td><input type="text" id="wca" value="<?php echo $withdrawCeilAmountOneDay;?>"</td>
					</tr>
					<tr>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="ofsRF" data-func='changeOFLRF' onclick="changeVal(this)" /></td>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="wfa" data-func='changeWFA' onclick="changeVal(this)" /></td>
						<td><input type="button" class="btn btn-default" value="修改" data-whatever="wca" data-func='changeWCA' onclick="changeVal(this)" /></td>
					</tr>
				</table>
	        </div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>