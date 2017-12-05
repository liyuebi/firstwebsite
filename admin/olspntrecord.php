<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include '../php/constant.php';

$sid = "";
$res = false;
if (isset($_GET["sid"])) {
	$sid = $_GET["sid"];

	include "../php/database.php";
	$con = connectToDB();
	if ($con)
	{
		$res = mysql_query("select * from PntsRecord where Type='$code2OlShopReceive' and WithStoreId='$sid' order by ApplyTime desc");
	}
}

?>

<!DOCTYPE html">
<html>
	<head>
		<meta charset="utf-8">
		<title>线下商家账户流水</title>
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
				var shopId = document.getElementById("olsid").value;
				var recordType = document.getElementById("recordType").value;

				var table;
				document.getElementById("searchresult").innerHTML = "";
				if (1 == recordType) {
					document.getElementById("receive_blk").style.display = "block";
					document.getElementById("withdraw_blk").style.display = "none";
					table = document.getElementById("tbl");
				}
				else {
					document.getElementById("withdraw_blk").style.display = "block";
					document.getElementById("receive_blk").style.display = "none";
					table = document.getElementById("tbl1");
				}

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

				$.post("../php/offlineTrade.php", {"func":"sOLSRecord","sid":shopId,"type":recordType}, function(data){
					
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

							if ('1' == recordType) {
							
								var d1 = document.createElement("td");
								d1.innerHTML = formatDateTime(list[key].ApplyTime);
								trow.appendChild(d1);
								var d2 = document.createElement("td");
								d2.innerHTML = list[key].WithUserId;
								trow.appendChild(d2);
								var d3 = document.createElement("td");
								d3.innerHTML = list[key].UserId;
								trow.appendChild(d3);
								var d4 = document.createElement("td");
								d4.innerHTML = list[key].RelatedAmount;
								trow.appendChild(d4);
							}
							else if ("2" == recordType) {

								var d1 = document.createElement("td");
								d1.innerHTML = formatDateTime(list[key].ApplyTime);
								trow.appendChild(d1);
								var d2 = document.createElement("td");
								d2.innerHTML = list[key].ApplyAmount;
								trow.appendChild(d2);
								var d3 = document.createElement("td");
								switch (parseInt(list[key].Status))
								{
									case <?php echo $olShopWdApplied ?>:
										d3.innerHTML = "等待处理";
										break;
									case <?php echo $olShopWdCancelled ?>:
										d3.innerHTML = "玩家取消";
										break;
									case <?php echo $olShopWdAccepted ?>:
										d3.innerHTML = "已完成";
										break;
									case <?php echo $olShopWdDeclined ?>:
										d3.innerHTML = "管理员拒绝";
										break;
								}
								trow.appendChild(d3);
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
					<input id="olsid" type="text" class="span2" placeholder="商家id" value="<?php echo $sid; ?>">
					<select id="recordType">
						<option value="1">收款记录</option>
						<option value="2">提现记录</option>
					</select>

					<button type="button" class="btn btn-default" onclick="searchOLSRecord()">查询</button>
				</form>
			</div>
			<p>
				<span id="searchresult"><?php if ($res) echo "记录数为：" . mysql_num_rows($res); ?></span>
			</p>
	        <div id="receive_blk">
				<table id="tbl" border="1" style="text-align: center;">
					<tr>
						<th>交易时间</th>
						<th>买家id</th>
						<th>卖家id</th>
						<th>线下云量数</th>
					</tr>
					<?php
					if ($res) {
						date_default_timezone_set('PRC');
						while($row = mysql_fetch_array($res)) {
					?>
					<tr>
						<td><?php echo $row["ApplyTime"]; ?></td>
						<td><?php echo $row["WithUserId"]; ?></td>
						<td><?php echo $row["UserId"]; ?></td>
						<td><?php echo $row["RelatedAmount"]; ?></td>
					</tr>
					<?php
						}
					}
					?>
				</table>
	        </div>
	        <div id="withdraw_blk" style="display: none;">
				<table id="tbl1" border="1" style="text-align: center;">
					<tr>
						<th>申请时间</th>
						<th>提现金额</th>
						<th>状态</th>
					</tr>
				</table>
	        </div>
		</div>	
    </body>
</html>