<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include '../php/constant.php';

$uid = "";
if (isset($_GET["uid"])) {
	$uid = $_GET["uid"];
}

date_default_timezone_set('PRC');

?>

<!DOCTYPE html">
<html>
	<head>
		<meta charset="utf-8">
		<title>云量存储记录</title>
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

			function searchCreditBankRec()
			{
				var userid = document.getElementById("userid").value;

				var table = document.getElementById("tbl");
			    var rowNum = table.rows.length;
		    	for (i=1;i<rowNum;++i)
		    	{
		        	table.deleteRow(i);
		        	rowNum=rowNum-1;
		        	i=i-1;
		    	}

				$.post("../php/usrMgr.php", {"func":"gcb","uid":userid}, function(data){
					
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
							trow.id = "row_" + list[key].IdxId;
							table.appendChild(trow);
							
							var d1 = document.createElement("td");
							d1.innerHTML = formatDateTime(list[key].SaveTime);
							trow.appendChild(d1);
							var d2 = document.createElement("td");
							d2.innerHTML = list[key].Invest;
							trow.appendChild(d2);
							var d3 = document.createElement("td");
							d3.innerHTML = list[key].Quantity;
							trow.appendChild(d3);
							var d4 = document.createElement("td");
							d4.innerHTML = list[key].Divident;
							trow.appendChild(d4);
							var d5 = document.createElement("td");
							d5.innerHTML = list[key].Balance;
							d5.id = "bal_" + list[key].IdxId;
							trow.appendChild(d5);
							var d6 = document.createElement("td");
							if (list[key].EmptyTime != "0") {
								d6.innerHTML = formatDateTime(list[key].EmptyTime);
							}
							trow.appendChild(d6);
							var d7 = document.createElement("td");
							if (list[key].Type == "1") {
								d7.innerHTML = "线上";
								d7.style.color = "blue";
							}	
							else if (list[key].Type == "2") {
								d7.innerHTML = "线下";
								d7.style.color = "green";
							}
							trow.appendChild(d7);

							var d8 = document.createElement("td");
							trow.appendChild(d8);
							{
								var blk = document.createElement("div");
								blk.className = "btn-group";
								d8.appendChild(blk);

								var a = document.createElement("a");
								a.className = "btn dropdown-toggle";
								a.dataset.toggle = "dropdown";
								a.href = "#";
								a.innerHTML = "操作"
								blk.appendChild(a);

								var span = document.createElement("span");
								span.className = "caret";
								a.appendChild(span);

								var ul = document.createElement("ul");
								ul.className="dropdown-menu";
								blk.appendChild(ul);

								if ( parseInt(list[key].Balance) > 0) {
									var li1 = document.createElement("li");
									var a1 = document.createElement("a");
									a1.id = list[key].IdxId;
									a1.innerHTML = "清空余额";
									if (a1.addEventListener) {
										a1.addEventListener('click', clearBalance, false);
									}
									else if (a1.attachEvent) {
										a1.attachEvent('onclick', clearBalance);
									}
									li1.appendChild(a1);
									ul.appendChild(li1);
								}

								var li2 = document.createElement("li");
								var a2 = document.createElement("a");
								a2.id = list[key].IdxId;
								a2.innerHTML = "删除记录";
								if (a2.addEventListener) {
									a2.addEventListener('click', clearRecord, false);
								}
								else if (a2.attachEvent) {
									a2.attachEvent('onclick', clearRecord);
								}
								li2.appendChild(a2);
								ul.appendChild(li2);
							}
							trow.appendChild(d8);
						}				    	
					}
					else {
						alert("搜索记录失败：" + data.error_msg);
					}
				}, "json");
			}

			function clearBalance(e)
			{
				if (!confirm("确认要清空余额？操作不可逆！")) {
					return;
				}

				var idx = e.target.id;
				$.post("../php/usrMgr.php", {"func":"ccbb","idx":idx}, function(data){

					if (data.error == "false") {
						document.getElementById("bal_" + idx).style.color = "red";
						document.getElementById("bal_" + idx).innerHTML = '0';
					}
					else {
						alert("失败：" + data.error_msg);
					}
				}, "json");
			}

			function clearRecord(e)
			{
				if (!confirm("确认要删除记录？操作不可逆！")) {
					return;
				}

				var idx = e.target.id;
				$.post("../php/usrMgr.php", {"func":"ccbr","idx":idx}, function(data){

					if (data.error == "false") {
						document.getElementById("row_" + idx).style.textDecoration = "line-through";
					}
					else {
						alert("失败：" + data.error_msg);
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
					<input id="userid" type="text" class="span2" placeholder="用户id" value="<?php echo $uid; ?>">
					<button type="button" class="btn btn-default" onclick="searchCreditBankRec()">查询</button>
				</form>
			</div>
			<p>
				<span id="searchresult"><?php if ($res) echo "记录数为：" . mysqli_num_rows($res); ?></span>
			</p>
	        <div id="receive_blk">
				<table id="tbl" class="table table-striped" border="1" style="max-width: 1000px; text-align: center;">
					<tr>
						<th>存储时间</th>
						<th>存储额</th>
						<th>发放额</th>
						<th>日利率</th>
						<th>余额</th>
						<th>结束时间</th>
						<th>种类</th>
						<th>操作</th>
					</tr>
				</table>
	        </div>
		</div>	
    </body>
</html>