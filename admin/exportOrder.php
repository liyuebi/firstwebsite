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

$res = mysqli_query($con, "select * from Product");
if ($res) {
	while($row = mysqli_fetch_assoc($res)) {
		$productList[$row['ProductId']] = $row['ProductName'];
	}	
}

include "../php/constant.php";
$result = mysqli_query($con, "select * from Transaction where Status='$OrderStatusBuy' and Exported=0");
// 	$result = mysqli_query($con, "select * from Transaction");
// $res1 = mysqli_query($con, "select * from Transaction  where Status='$OrderStatusDefault'");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>订单管理</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
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
				
				var container = document.getElementById("tbl");
				if (container == null) {
					alert("查找表格失败！");
					return;				
				}

				try {
					var fso = new ActiveXObject("Scripting.FileSystemObject");
					if (!fso.FolderExists("D://导出订货单")) {
						fso.CreateFolder("D://导出订货单");
					}
					var date = new Date();
					var name = date.getFullYear()+"_"+date.getMonth()+"_"+date.getDate()+"_"+date.getHours()+"_"+date.getMinutes()+"_"+date.getSeconds();
					var folderPath = "D://导出订货单//" + name;
					var folder = fso.CreateFolder(folderPath);
					var filePath1 = folderPath + "//普通EXCEL模板.XLS";
	// 				var file = fso.CreateTextFile(filePath1, true);
					
					var XLObj = new ActiveXObject("Excel.Application");
				}
				catch (err) {
					alert(err.description);
					return;
				}
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
				ExcelSheet.Application.Visible = true;
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
				
			    var rowNum = container.rows.length;
			    for (i=1;i<rowNum;++i) {
				    
					ExcelSheet.ActiveSheet.Cells(i + 1,1).Value = "";
					ExcelSheet.ActiveSheet.Cells(i + 1,2).Value = container.rows[i].cells[1].innerHTML;
					ExcelSheet.ActiveSheet.Cells(i + 1,3).Value = "";
					ExcelSheet.ActiveSheet.Cells(i + 1,4).Value = container.rows[i].cells[3].innerHTML;
					ExcelSheet.ActiveSheet.Cells(i + 1,5).Value = container.rows[i].cells[4].innerHTML;
					ExcelSheet.ActiveSheet.Cells(i + 1,6).Value = container.rows[i].cells[5].innerHTML; 
			    }

				
// 				var filePath = folderPath + "//test.XLS"; // "//普通EXCEL模板.XLS";
// 				var filePath = "D://普通EXCEL模板.XLS";
				alert("文件将保存到" + filePath1);
// 				alert(folder.attributes);
				try {
					ExcelSheet.SaveAs(filePath1);
				}
				catch (err) {
					alert(err.description);
					return;
				}
				ExcelSheet.Application.Quit();
				
				exportToExcel();
			}
			
			function tryMarkExported()
			{
				if (!confirm("是否确认标记为已导出订单")) {
					return;
				}
				
				markExported();
			}
			
			function markExported()
			{
				var container = document.getElementById("tbl");
				if (null == container) {
					alert("查找表格出错！");
					return;
				}
			    var rowNum = container.rows.length;
			    var ids = "";
			    for (i=1;i<rowNum;++i) {
				    
				    if (i > 1) {
					    ids += ",";
				    }
				    ids += container.rows[i].cells[0].id;
			    }
				
				$.post("../php/trade.php", {"func":"markExported","ids":ids}, function(data){

					if (data.error == "false") {
						alert("成功标记为已导出状态！");
					}
					else {
						alert("标记失败: " + data.error_msg);
					}					
				}, "json");
			}
		</script>
	</head>
	<body>
		<div style="padding: 10px 0 0 10px;" >
	        <div>
		        <div>
 					<input type="button" value="导出到excel" onclick="exportToExcel()" />
 					<input type="button" value="标记为已导出" onclick="tryMarkExported()" />
 					<hr>
					<table id="tbl" border="1">
						<tr>
							<th>订单编号</th>
							<th>收件人</th>
							<th>固话</th>
							<th>手机</th>
							<th>地址</th>
							<th>发货信息</th>
							<th>备注</th>
							<th>代收金额</th>
							<th>保价金额</th>
							<th>业务类型</th>
						</tr>
						<?php
							include "../php/constant.php";
							date_default_timezone_set('PRC');
							while($row = mysqli_fetch_assoc($result)) {
						?>
								<tr>
									<td id="<?php echo $row["OrderId"]; ?>"></td>
									<td><?php echo $row["Receiver"]; ?></td>
									<td></td>
									<td><?php echo $row["PhoneNum"]; ?></td>
									<td><?php echo $row["Address"]; ?></td>
									<td><?php echo $productList[$row['ProductId']] . ' x' . $row["Count"] . '件'; ?></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
								</tr>
						<?php
							}
						?>
					</table>
		        </div>
	        </div>
		</div>
    </body>
</html>