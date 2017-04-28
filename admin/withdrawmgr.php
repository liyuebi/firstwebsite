<?php

include "../php/database.php";

function getWithdrawApplication()
{
	$con = connectToDB();
	if (!$con)
	{
		return false;
	}
	
	mysql_select_db("my_db", $con);
	$result = mysql_query("select * from WithdrawApplication");
	return $result;
}

$result = getWithdrawApplication();		
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>取现管理</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function onConfirm(btn)
			{
// 				alert(btn.id);	
				document.getElementById(btn.id).disabled = true;
				$.post("../php/credit.php", {"func":"allowWithdraw","index":btn.id}, function(data){
					
					if (data.error == "false") {
						alert("通过申请！" + data.index);	
						location.href = "pwd.php";
					}
					else {
						alert("申请未通过: " + data.error_msg + " " + data.index);
					}
				}, "json");
			}
			
			function onDeny(btn)
			{
				alert(btn.id);
			}
		</script>
	</head>
	<body>
        <div>
			<table border="1">
				<tr>
					<th>用户id</th>
					<th>用户手机号</th>
					<th>用户姓名</th>
					<th>申请金额</th>
					<th>手续费</th>
					<th>申请时间</th>
					<th>确认</th>
					<th>拒绝</th>
				</tr>
				<?php
					while($row = mysql_fetch_array($result)) {
				?>
						<tr>
							<th><?php echo $row["UserId"]; ?></th>
							<th></th>
							<th></th>
							<th><?php echo $row["ApplyAmount"]; ?></th>
							<th><?php $fee = $row["ApplyAmount"] - $row["ActualAmount"]; echo $fee; ?></th>
							<th><?php echo $row["ApplyTime"] ?></th>
							<th><input type="button" value="确认" id=<?php echo $row["IndexId"]; ?> onclick="onConfirm(this)" /></th>
							<th><input type="button" value="拒绝" id=<?php echo $row["IndexId"]; ?> onclick="onDeny(this)" /></th>
						</tr>
				<?php
					}
				?>
			</table>
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>