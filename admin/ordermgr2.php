<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include "../php/database.php";

$result = false;
$res1 = false;
$productList = array();

$con = connectToDB();
if (!$con)
{
	return false;
}

$res = mysql_query("select * from Product");
if ($res) {
	while($row = mysql_fetch_array($res)) {
		$productList[$row['ProductId']] = $row['ProductName'];
	}	
}

include "../php/constant.php";
$result = mysql_query("select * from Transaction  where Status='$OrderStatusBuy'");
// 	$result = mysql_query("select * from Transaction");
$res1 = mysql_query("select * from Transaction  where Status='$OrderStatusDefault'");

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
				document.getElementById("blk_tbl2").style.display = "none";
			}
			
			function getOthers()
			{	
				document.getElementById("tbl").style.display = "none";
				document.getElementById("tbl1").style.display = "block";
				document.getElementById("blk_tbl2").style.display = "none";
			}
			
			function goQueryOrder()
			{
				document.getElementById("tbl").style.display = "none";
				document.getElementById("tbl1").style.display = "none";
				document.getElementById("blk_tbl2").style.display = "block";				
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
				var folder = fso.CreateFolder(folderPath);
				var filePath1 = folderPath + "//普通EXCEL模板.XLS";
				var file = fso.CreateTextFile(filePath1, true);
				
				var XLObj = new ActiveXObject("Excel.Application");
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
				ExcelSheet.ActiveSheet.Cells(1,2).Value = "收件人";
				ExcelSheet.ActiveSheet.Cells(1,3).Value = "固话";
				ExcelSheet.ActiveSheet.Cells(1,4).Value = "手机";
				ExcelSheet.ActiveSheet.Cells(1,5).Value = "地址";
				ExcelSheet.ActiveSheet.Cells(1,6).Value = "发货信息";
				ExcelSheet.ActiveSheet.Cells(1,7).Value = "备注";
				ExcelSheet.ActiveSheet.Cells(1,8).Value = "代收金额";
				ExcelSheet.ActiveSheet.Cells(1,9).Value = "保价金额";
				ExcelSheet.ActiveSheet.Cells(1,10).Value = "业务类型";
				alert(4);
// 				var filePath = folderPath + "//test.XLS"; // "//普通EXCEL模板.XLS";
				var filePath = "D://普通EXCEL模板.XLS";
				alert(filePath);
				alert(folder.attributes);
				try {
				ExcelSheet.SaveAs(filePath);
				}
				catch (err) {
					alert(err.description);
				}
				alert(5);
				ExcelSheet.Application.Quit();
				alert(6);
			}
			
			function queryUserOrders()
			{
				var userid = document.getElementById("input_userid").value;
				$.post("../php/trade.php", {"func":"queryUserOrder","uid":userid}, function(data){
					
					if (data.error == 'false') {
						document.getElementById("quert_result").innerHTML = "查询用户 " + data.uid + " 交易记录成功。共有" + data.num + "条交易记录！";
						
						var container = document.getElementById("tbl2");
						for (var key in data.order_list) {
							
							var trow = document.createElement("tr");
							container.appendChild(trow);
							
							var d1 = document.createElement("td");
							d1.innerHTML = data.order_list[key]['OrderTime'];
							trow.appendChild(d1);
							var d2 = document.createElement("td");
							d2.innerHTML = data.order_list[key]['UserId'];
							trow.appendChild(d2);
							var d3 = document.createElement("td");
							d3.innerHTML = data.order_list[key]['ProductName'];
							trow.appendChild(d3);
							var d4 = document.createElement("td");
							d4.innerHTML = data.order_list[key]['Count'];
							trow.appendChild(d4);
							var d5 = document.createElement("td");
							d5.innerHTML = data.order_list[key]['Receiver'];
							trow.appendChild(d5);
							var d6 = document.createElement("td");
							d6.innerHTML = data.order_list[key]['PhoneNum'];
							trow.appendChild(d6);
							var d7 = document.createElement("td");
							d7.innerHTML = data.order_list[key]['Address'];
							trow.appendChild(d7);
							var d8 = document.createElement("td");
							d8.id = "status_" + key;
							trow.appendChild(d8);
							var d9 = document.createElement("td");	
							trow.appendChild(d9);
							var d10 = document.createElement("td");	
							trow.appendChild(d10);
							
							var status = '';
							if (data.order_list[key]['Status'] == <?php echo $OrderStatusBuy; ?>) {
								status = '等待发货';

								var input = document.createElement("input");
								input.type = "text";
								input.placeholder = "请输入快递单号";
								input.id = "courierNum_" + key;
								d9.appendChild(input);
								
								var input1 = document.createElement("input");
								input1.type = "button";
								input1.value = "确认发货";
								input1.id = key;
								if (input1.addEventListener) {
									input1.addEventListener('click', function(){onConfirm(input1);}, false);
								}
								else if (input1.attachEvent) {
									input1.attachEvent('onclick', function() {onConfirm(input1);});
								}
								d10.appendChild(input1);
							}
							else if (data.order_list[key]['Status'] == <?php echo $OrderStatusDefault; ?>) {
								status = '等待用户确认订单';
							}
							else if (data.order_list[key]['Status'] == <?php echo $OrderStatusDelivery; ?>) {
								status = '已发货';
								
								d9.innerHTML = data.order_list[key]['CourierNum'];
							}
							else if (data.order_list[key]['Status'] == <?php echo $OrderStatusAccept; ?>) {
								status = '已收货';
								
								d9.innerHTML = data.order_list[key]['CourierNum'];
							}
							d8.innerHTML = status;
						}
					}
					else {
						document.getElementById("quert_result").innerHTML = "查询用户 " + data.uid + " 交易记录失败：" + data.error_msg;
					}
				}, "json");
			}
		</script>
	</head>
	<body>
		<div style="padding: 10px 0 0 10px;" >
				<div id="blk_tbl2">
					<table id="tbl2" border="1">
						<input id="input_userid" type="text" placeholder="请输入用户Id" />
						<input type="button" value="查询用户订单" onclick="queryUserOrders()" />
						<p id="quert_result"></p>
						<tr>
							<th>下单时间</th>
							<th>用户id</th>
							<th>产品信息</th>
							<th>数量</th>
							<th>收件人</th>
							<th>收货人手机</th>
							<th>收货地址</th>
							<th>状态</th>
							<th>快递单号</th>
							<th>操作</th>
						</tr>
					</table>
				</div>
	        </div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>