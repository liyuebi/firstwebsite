<?php

include "../php/admin_func.php";
include "../php/constant.php";

if (!checkLoginOrJump()) {
	return;
}

include '../php/constant.php';

$sid = "";
if (isset($_GET["uid"])) {
	$sid = $_GET["uid"];
}

date_default_timezone_set('PRC');

?>

<!DOCTYPE html">
<html>
	<head>
		<meta charset="utf-8">
		<title>用户云量记录</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script>
		<script type="text/javascript">
			
			function formatDateTime(inputTime)
			{    
		        var date = new Date(inputTime * 1000);  
		        var y = date.getFullYear();    
		        var m = date.getMonth() + 1;    
		        m = m < 10 ? ('0' + m) : m;    
		        var d = date.getDate();    
		        d = d < 10 ? ('0' + d) : d;    
		        var h = date.getHours();  
		        h = h < 10 ? ('0' + h) : h;  
		        var minute = date.getMinutes();  
		        var second = date.getSeconds();  
		        minute = minute < 10 ? ('0' + minute) : minute;    
		        // second = second < 10 ? ('0' + second) : second;   
		        return y + '-' + m + '-' + d+' '+h+':'+minute; //+':'+second;    
		    };    

			function searchOLSRecord()
			{
				var userId = document.getElementById("userid").value;
				var recordType = document.getElementById("recordType").value;

				var table = document.getElementById("tbl");

				if (!table) {
					return;
				}

			    var rowNum = table.rows.length;
		    	for (i=1;i<rowNum;++i)
		    	{
		        	table.deleteRow(i);
		        	rowNum=rowNum-1;
		        	i=i-1;
		    	}

				$.post("../php/usrMgr.php", {"func":"sRec","sid":userId,"type":recordType}, function(data){
					
					if (data.error == "false") {

						var result = document.getElementById("searchresult");
						if (result) {

							if (data.num > 0) {

								result.innerHTML = "记录数为：" + data.num;
								result.className = "text-success";
							}
							else {
								result.innerHTML = "没有记录！";
								result.className = "text-warning";
							}
						}

						var list = data.list;
						for (var key in list) {

							var trow = document.createElement("tr");
							table.appendChild(trow);

							{
								var d1 = document.createElement("td");
								d1.innerHTML = formatDateTime(list[key].ApplyTime);
								trow.appendChild(d1);
								var d2 = document.createElement("td");
								d2.innerHTML = list[key].Amount;
								trow.appendChild(d2);
								var d3 = document.createElement("td");
								d3.innerHTML = list[key].CurrAmount;
								trow.appendChild(d3);
								var d4 = document.createElement("td");
								d4.innerHTML = list[key].HandleFee;
								trow.appendChild(d4);
								var d5 = document.createElement("td");
								if (list[key].WithUserId != '0') {
									d5.innerHTML = list[key].WithUserId;
								}
								trow.appendChild(d5);
								var d6 = document.createElement("td");
								trow.appendChild(d6);

								if (1 == recordType) {

									switch (list[key].Type) {
										case "<?php echo $codeDivident; ?>":
											d6.innerHTML = "线上云量每日分红";
											break;
										case "<?php echo $codeReferer; ?>":
											d6.innerHTML = "推荐新用户";
											break;
										case "<?php echo $codeReferBonus; ?>":
											d6.innerHTML = "直推奖励";
											break;
										case "<?php echo $codeColliBonusNew ?>":
											d6.innerHTML = "推荐碰撞奖励";
											break;
										case "<?php echo $codeColliBonusRe; ?>":
											d6.innerHTML = "复投碰撞奖励";
										 	break;
										case "<?php echo $codeSave; ?>":
											d6.innerHTML = "存储";
											break;
										case "<?php echo $codeCreTradeInit; ?>":
											d6.innerHTML = "创建云量交易";
											break;
										case "<?php echo $codeCreTradeSucc; ?>":
											d6.innerHTML = "交易成功，退回未购部分";
										 	break;
										case "<?php echo $codeCreTradeCancel; ?>":
											d6.innerHTML = "交易取消，退回";
										 	break;
 										case "<?php echo $codeCreTradeRec; ?>":
											d6.innerHTML = "交易成功，买家收款";
											break;
										case "<?php echo $codeTryChargePhone; ?>":
											d6.innerHTML = "手机充值申请";
										 	break;
										case "<?php echo $codeStopChargePhone; ?>":
											d6.innerHTML = "手机充值申请取消";
										 	break;
										case "<?php echo $codeTryChargeOil; ?>":
											d6.innerHTML = "油卡充值申请";
										 	break;
										case "<?php echo $codeStopChargeOil; ?>":
											d6.innerHTML = "油卡充值取消";
										 	break;
										case "<?php echo $codeRegiOlShop; ?>":
											d6.innerHTML = "注册线下商店";
										 	break;
										case "<?php echo $codeFromProfit; ?>":
											d6.innerHTML = "消费云量转入";
										 	break;
									}
								}
								else if (2 == recordType) {

									switch (list[key].Type) {
										case "<?php echo $code2Save; ?>":
											d6.innerHTML = "云量存储即时返还";
											break;
										case "<?php echo $code2OlShopPay; ?>":
											d6.innerHTML = "线下支付";
											break;
										case "<?php echo $code2OlShopReceive; ?>":
											d6.innerHTML = "线下商家收款";
											break;
										case "<?php echo $code2OlShopBonus; ?>":
											d6.innerHTML = "线下商家分红";
											break;
										case "<?php echo $code2OlShopWdApply; ?>":
											d6.innerHTML = "提现申请";
										 	break;
										case "<?php echo $code2OlShopWdCancel; ?>":
											d6.innerHTML = "提现申请撤销";
											break;
										case "<?php echo $code2OlShopWdAccept; ?>":
											d6.innerHTML = "提现申请通过";
											break;
										case "<?php echo $code2OlSHopWdDecline; ?>":
											d6.innerHTML = "提现申请被拒";
										 	break;
										case "<?php echo $code2Divident; ?>":
											d6.innerHTML = "线下积分每日分红";
										 	break;
 										case "<?php echo $code2TryCP; ?>":
											d6.innerHTML = "手机充值申请";
											break;
										case "<?php echo $code2CancelCP; ?>":
											d6.innerHTML = "手机充值申请取消";
										 	break;
										case "<?php echo $code2StopCP; ?>":
											d6.innerHTML = "手机充值申请被拒";
										 	break;
										case "<?php echo $code2FromProfit; ?>":
											d6.innerHTML = "消费云量转入";
										 	break;
									}
								}
								else if (3 == recordType) {

									switch (list[key].Type) {
										case "<?php echo $code3OlShopReceive; ?>":
											d6.innerHTML = "线下商家收款";
											break;
										case "<?php echo $code3OlShopBonus; ?>":
											d6.innerHTML = "线下商家分红";
											break;
										case "<?php echo $code3OlShopWdApply; ?>":
											d6.innerHTML = "提现申请";
											break;
										case "<?php echo $code3OlShopWdCancel; ?>":
											d6.innerHTML = "提现申请撤销";
											break;
										case "<?php echo $code3OlShopWdAccept; ?>":
											d6.innerHTML = "提现申请通过";
										 	break;
										case "<?php echo $code3OlSHopWdDecline; ?>":
											d6.innerHTML = "提现申请被拒";
											break;
										case "<?php echo $code3ToCredit; ?>":
											d6.innerHTML = "转到线上云量";
											break;
										case "<?php echo $code3ToPnts; ?>":
											d6.innerHTML = "转到线下云量";
										 	break;
										case "<?php echo $code3ToShareCredit; ?>":
											d6.innerHTML = "转到分享云量";
										 	break;
									}
								}
								else if (4 == recordType) {

									switch (list[key].Type) {
										case "<?php echo $code4CreTradeRec; ?>":
											d6.innerHTML = "交易收款";
											break;
										case "<?php echo $code4Referer; ?>":
											d6.innerHTML = "推荐用户";
											break;
										case "<?php echo $code4Save; ?>":
											d6.innerHTML = "云量存储";
											break;
										case "<?php echo $code4FromProfit; ?>":
											d6.innerHTML = "消费云量转入";
											break;
										case "<?php echo $code4RegiOlShop; ?>":
											d6.innerHTML = "注册线下商店";
										 	break;
									}
								}
							}
						}				    	
					}
					else {
						alert("搜索记录失败：" + data.error_msg);
					}
				}, "json");
			}
						
			$(document).ready(function(){
			});
		</script>
	</head>
	<body>
		<div style="padding: 10px 0 0 10px;" >
			<div class="navbar navbar-default">
				<form class="navbar-form">
					<input id="userid" type="text" class="span2" placeholder="用户id" value="<?php echo $sid; ?>">
					<select id="recordType">
						<option value="1">线上云量记录</option>
						<option value="2">线下云量记录</option>
						<option value="3">消费云量记录</option>
						<option value="4">分享云量记录</option>
					</select>

					<button type="button" class="btn btn-default" onclick="searchOLSRecord()">查询</button>
				</form>
			</div>
			<p>
				<span id="searchresult"><?php if ($res) echo "记录数为：" . mysqli_num_rows($res); ?></span>
			</p>
	        <div id="tbl_blk">
				<table class="table table-striped" id="tbl" border="1" style="max-width: 800px; text-align: center;">
					<tr>
						<th>时间</th>
						<th>变化云量</th>
						<th>剩余云量</th>
						<th>手续费</th>
						<th>相关用户</th>
						<th>类型</th>
					</tr>
				</table>
	        </div>
		</div>	
    </body>
</html>