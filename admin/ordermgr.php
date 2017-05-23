<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include "../php/database.php";

$result = false;
$res1 = false;

$con = connectToDB();
if (!$con)
{
	return false;
}
	
include "../php/constant.php";
$result = mysql_query("select * from Transaction  where Status='$OrderStatusBuy'");
// 	$result = mysql_query("select * from Transaction");
$res1 = mysql_query("select * from Transaction  where Status!='$OrderStatusBuy'");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>订单管理</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function onConfirm(btn)
			{
				var courier = document.getElementById("courierNum_" + btn.id).value;
				courier = $.trim(courier);
				if (courier.length == 0) {
					alert("请输入快递单号！");
					return;
				}
				document.getElementById(btn.id).disabled = true;
				$.post("../php/trade.php", {"func":"delivery","index":btn.id,"courier":courier}, function(data){
					
					if (data.error == "false") {
						alert("发货状态修改成功！");
						document.getElementById("status_" + data.index).innerHTML = "已发货";	
					}
					else {
						alert("发货状态修改失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function onDeny(btn)
			{
				alert(btn.id);
			}
			
			function getWaitForDelivery()
			{
				document.getElementById("tbl").style.display = "block";
				document.getElementById("tbl1").style.display = "none";
			}
			
			function getOthers()
			{	
				document.getElementById("tbl").style.display = "none";
				document.getElementById("tbl1").style.display = "block";
			}
			
			function exportToExcel()
			{
// 				alert(navigator.userAgent);
/*
				if (!(window.attachEvent && navigator.userAgent.indexOf('Opera') === -1)) {
					alert("您没有使用ie浏览器！");
					return;
				}
*/
				var isIE = !!window.ActiveXObject || "ActiveXObject" in window;
				if (!isIE) {
					alert("您没有使用ie浏览器！");
					return;					
				}

				var fso = new ActiveXObject("Scripting.FileSystemObject");
				if (!fso.FolderExists("D://导出订货单")) {
					fso.CreateFolder("D://导出订货单");
				}
				var date = new Date();
				var name = date.getFullYear()+"_"+date.getMonth()+"_"+date.getDate()+"_"+date.getHours()+"_"+date.getMinutes()+"_"+date.getSeconds();
				var folderPath = "D://导出订货单//" + name;
				fso.CreateFolder(folderPath);
				
				var XLObj = new ActiveXObject("Excel.Application");
				alert("1");
/*
				var xlBook = XLObj.Workbooks.Add;
				alert("2");
				var ExcelSheet = xlBook.Worksheets(1);
				alert("3");
				
				ExcelSheet.SaveAs(folderPath + "//普通EXCEL模板.xls");
				alert("4");
				
				xlBook.Close(true);
				alert("5");
*/
				var ExcelSheet = new ActiveXObject("Excel.Sheet");
				alert(2);
				ExcelSheet.Application.Visible = true;
				alert(3);
				ExcelSheet.ActiveSheet.Cells(1,1).Value = "订单编号";
				ExcelSheet.ActiveSheet.Cells(1,1).Value = "收件人";
				ExcelSheet.ActiveSheet.Cells(1,1).Value = "固话";
				ExcelSheet.ActiveSheet.Cells(1,1).Value = "手机";
				ExcelSheet.ActiveSheet.Cells(1,1).Value = "地址";
				ExcelSheet.ActiveSheet.Cells(1,1).Value = "发货信息";
				ExcelSheet.ActiveSheet.Cells(1,1).Value = "备注";
				ExcelSheet.ActiveSheet.Cells(1,1).Value = "代收金额";
				ExcelSheet.ActiveSheet.Cells(1,1).Value = "保价金额";
				ExcelSheet.ActiveSheet.Cells(1,1).Value = "业务类型";
				alert(4);
				var filePath = folderPath + "//test.XLS"; // "//普通EXCEL模板.XLS";
				alert(filePath);
				ExcelSheet.SaveAs(filePath);
				alert(5);
				ExcelSheet.Application.Quit();
				alert(6);
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
				<li><a href="configRwdRate.php">配置动态拨比</a></li>
				<li><a href="adminmgr.php">管理员账号维护</a></li>
			</ul>
		</div>
		<div style="display: inline; float: left; padding: 10px 0 0 10px;" >
			<div>
				<input type="button" value="查看待发货订单" onclick="getWaitForDelivery()"/>
				<input type="button" value="查看其他订单" onclick="getOthers()"/>
<!-- 				<input type="button" value="查看已完成订单" onclick=""/> -->
			</div>
	        <div>
		        <div id="tbl">
    				<input id="file_path" type="file" />
					<input type="button" value="导出到excel" onclick="exportToExcel()"  />
					<table id="tbl" border="1">
						<tr>
							<th>下单时间</th>
							<th>用户id</th>
							<th>数量</th>
							<th>收件人</th>
							<th>收货人手机</th>
							<th>收货地址</th>
							<th>状态</th>
							<th>快递单号</th>
							<th>确认发货</th>
						</tr>
						<?php
							include "../php/constant.php";
							date_default_timezone_set('PRC');
							while($row = mysql_fetch_array($result)) {
						?>
								<tr>
									<td><?php echo date("Y.m.d H:i:s" ,$row["OrderTime"]); ?></td>
									<td><?php echo $row["UserId"]; ?></td>
									<td><?php echo $row["Count"]; ?></td>
									<td><?php echo $row["Receiver"]; ?></td>
									<td><?php echo $row["PhoneNum"]; ?></td>
									<td><?php echo $row["Address"]; ?></td>
									<td id='status_<?php echo $row["OrderId"]; ?>'><?php if ($OrderStatusBuy == $row["Status"]) echo "等待发货"; else if ($OrderStatusDefault == $row["Status"]) echo "等待用户确认订单"; else if ($OrderStatusDelivery == $row["Status"]) echo "已收货"; else if ($OrderStatusAccept == $row["Status"]) echo "已收货"; ?></td>
									<td><input type="text" id='courierNum_<?php echo $row["OrderId"]; ?>' size='30' placeholder="请输入快递单号！" /></td>
									<td><input type="button" value="确认" id=<?php echo $row["OrderId"]; ?> onclick="onConfirm(this)" /></td>
								</tr>
						<?php
							}
						?>
					</table>
		        </div>
				<table id="tbl1" border="1" style="display: none;">
					<tr>
						<th>下单时间</th>
						<th>用户id</th>
						<th>数量</th>
						<th>收件人</th>
						<th>收货人手机</th>
						<th>收货地址</th>
						<th>状态</th>
						<th>快递单号</th>
						<th>确认发货</th>
					</tr>
					<?php
						include "../php/constant.php";
						date_default_timezone_set('PRC');
						while($row = mysql_fetch_array($res1)) {
					?>
							<tr>
								<td><?php echo date("Y.m.d H:i:s" ,$row["OrderTime"]); ?></td>
								<td><?php echo $row["UserId"]; ?></td>
								<td><?php echo $row["Count"]; ?></td>
								<td><?php echo $row["Receiver"]; ?></td>
								<td><?php echo $row["PhoneNum"]; ?></td>
								<td><?php echo $row["Address"]; ?></td>
								<td id='status_<?php echo $row["OrderId"]; ?>'><?php if ($OrderStatusBuy == $row["Status"]) echo "等待发货"; else if ($OrderStatusDefault == $row["Status"]) echo "等待用户确认订单"; else if ($OrderStatusDelivery == $row["Status"]) echo "已收货"; else if ($OrderStatusAccept == $row["Status"]) echo "已收货"; ?></td>
								<td><input type="text" id='courierNum_<?php echo $row["OrderId"]; ?>' size='30' placeholder="请输入快递单号！" /></td>
								<td><input type="button" value="确认" id=<?php echo $row["OrderId"]; ?> onclick="onConfirm(this)" /></td>
							</tr>
					<?php
						}
					?>
				</table>

	        </div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>