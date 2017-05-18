<?php

include "../php/database.php";
$con = connectToDB();
if (!$con) {
	return;
}

$result = mysql_query("select * from Product");
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>产品管理</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			function addProduct()
			{
				document.getElementById("addform").style.display = "inline";
				document.getElementById("blk_list").style.display = "none";   
			}
			
			function queryProduct()
			{
				document.getElementById("addform").style.display = "none";
				document.getElementById("blk_list").style.display = "inline";				
			}
			
			function enableInput(idx, enabled)
			{
				document.getElementsByName("edit_" + idx)[0].disabled = enabled;
				document.getElementsByName("commit_" + idx)[0].disabled = !enabled;
				document.getElementsByName("cancel_" + idx)[0].disabled = !enabled;
				
				document.getElementById("name_" + idx).disabled = !enabled;
				document.getElementById("price_" + idx).disabled = !enabled;
				document.getElementById("desc_" + idx).disabled = !enabled;
				document.getElementById("limit_" + idx).disabled = !enabled;
			}
			
			function edit(btn)
			{
				enableInput(btn.id, true);
			}
			
			function commit(btn)
			{
				enableInput(btn.id, false);
				var name = document.getElementById("name_" + btn.id).value;
				var price = document.getElementById("price_" + btn.id).value;
				var desc = document.getElementById("desc_" + btn.id).value;				
				var limit = document.getElementById("limit_" + btn.id).value;
				
				$.post("../php/product.php", {"func":"edit","id":btn.id,"name":name,"price":price,"desc":desc,"limit":limit}, function(data){
					
					if (data.error == "false") {
						alert("修改成功！");	
					}
					else {
						alert("修改失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function cancel(btn)
			{
				enableInput(btn.id, false);
			}
		</script>
	</head>
	<body>
		<div style="padding: 0 10px 0 5px; height: 100%; display:inline; float: left; border-right: 1px solid black;">
			<ul style="list-style: none; padding: 0">
<!-- 				<li><a href="companymgr.html">企业管理</a></li> -->
				<li><a href="productmgr.php">产品管理</a></li>
				<li><a href="usermgr.html">用户管理</a></li>
				<li><a href="ordermgr.php">订单管理</a></li>
				<li><a href="rechargemgr.php">充值管理</a></li>
				<li><a href="withdrawmgr.php">取现管理</a></li>
				<li><a href="configmgr.php">配置管理</a></li>
				<li><a href="statistics.php">统计数据</a></li>
				<li><a href="configRwdRate.php">配置动态拨比</a></li>
			</ul>
		</div>
		<div style="display: inline; float: left; padding: 10px 0 0 10px;" >
	        <div>
				<h3>产品管理</h3>
	        </div>
	        <input id="btnQuery" type="button" value="查看产品" onclick="queryProduct()" />
	        <input id="btnAdd" type="button" value="添加产品" onclick="addProduct()" />
	        <div>
		        <div id="blk_list">
			        <table border="1">
						<tr>
<!-- 							<th>产品Id</th> -->
							<th>产品名</th><th>产品价格</th><th>产品描述</th><th>操作</th>
						</tr>
						<?php
						if ($result) {
							while ($row = mysql_fetch_assoc($result)) {
						?>
						<tr> 
<!-- 							<td><?php echo $row["ProductId"]; ?></td> -->
							<td><input type="text" id="name_<?php echo $row["ProductId"]; ?>" value="<?php echo $row["ProductName"]; ?>" disabled="true" /></td>
							<td><input type="text" id="price_<?php echo $row["ProductId"]; ?>" value="<?php echo $row["Price"]; ?>" disabled="true" /></td>
							<td><input type="text" id="desc_<?php echo $row["ProductId"]; ?>" value="<?php echo $row["ProductDesc"]; ?>" disabled="true" size="60" /></td>
							<td><input type="text" id="limit_<?php echo $row["ProductId"]; ?>" value="<?php echo $row["LimitOneDay"]; ?>" disabled="true" /></td>
							<td>
								<input type="button" name="edit_<?php echo $row["ProductId"]; ?>" id=<?php echo $row["ProductId"]; ?> value="编辑" onclick="edit(this)" />
								<input type="button" name="commit_<?php echo $row["ProductId"]; ?>" id=<?php echo $row["ProductId"]; ?> value="提交" onclick="commit(this)" disabled="true" />
								<input type="button" name="cancel_<?php echo $row["ProductId"]; ?>" id=<?php echo $row["ProductId"]; ?> value="取消" onclick="cancel(this)" disabled="true" />
							</td>
						</tr>
						<?php
							}							
						}
						?>
			        </table>
		        </div>
		        <form id="addform" style="display: none" method="post" action="../php/product.php">
			        <input type="hidden" name='func' value="addNew" />
			        名称: <input type="text" name="productname" placeholder="请填写产品名称" />
			        <br>
			        描述: <input type="text" name="productdesc" placeholder="请填写产品描述" />
			        <br>
			        价格: <input type="text" name="productprice" placeholder="请填写产品价格" onkeypress="return onlyNumber(event)"/>
					<br>
					每日购买上限: <input type="text" name="daylimit" placeholder="请填写产品购买上限，默认为没有上上限（0）" onkeypress="return onlyNumber(event)"/>
					<br>
					<input type="submit" name="submit" value="添加" />
		        </form>
	        </div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>