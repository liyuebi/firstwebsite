<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include '../php/constant.php';

?>

<!DOCTYPE html">
<html>
	<head>
		<meta charset="utf-8">
		<title>线下商家</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script>
		<script type="text/javascript">
			
			function searchOLShop()
			{
				var shopId = document.getElementById("olsid").value;
				var shopname = document.getElementById("olsname").value;
				var ownerid = document.getElementById("ownerid").value;
				
				$.post("../php/offlineTrade.php", {"func":"ssInA","sid":shopId,"sname":shopname,"oid":ownerid}, function(data){
					
					if (data.error == "false") {

						document.getElementById("olsid").value = "";
						document.getElementById("olsname").value = "";	
						document.getElementById("ownerid").value = "";	

						var result = document.getElementById("searchresult");
						if (result) {

							if (data.num > 0) {

								result.innerHTML = "符合条件的线下商家： " + data.num;
								result.className = "text-success";
							}
							else {
								result.innerHTML = "没有符合条件的线下商家！";
								result.className = "text-warning";
							}
						}

						var table = document.getElementById("tbl");
					    var rowNum = table.rows.length;
				    	for (i=1;i<rowNum;++i)
				    	{
				        	table.deleteRow(i);
				        	rowNum=rowNum-1;
				        	i=i-1;
				    	}

						var list = data.list;
						for (var key in list) {
							var trow = document.createElement("tr");
							table.appendChild(trow);
							
							var d1 = document.createElement("td");
							d1.innerHTML = key;
							trow.appendChild(d1);
							var d2 = document.createElement("td");
							d2.innerHTML = list[key].UserId;
							trow.appendChild(d2);
							var d3 = document.createElement("td");
							d3.innerHTML = list[key].ShopName;
							trow.appendChild(d3);
							var d4 = document.createElement("td");
							d4.innerHTML = list[key].Contacter;
							trow.appendChild(d4);
							var d5 = document.createElement("td");
							d5.innerHTML = list[key].PhoneNum;
							trow.appendChild(d5);
							var d6 = document.createElement("td");
							d6.innerHTML = list[key].Address;
							trow.appendChild(d6);
							var d7;
							if ("" == list[key].LicencePic) {
								d7 = document.createElement("td");
								d7.innerHTML = "未上传";
								d7.className = "text-warning";
							}
							else {
								d7 = document.createElement("input");
								d7.type = "button";
								d7.value = "查看营业执照";
								d7.dataset.toggle = "modal";
								d7.dataset.target = "#licenceModal";
								d7.dataset.who = key;
								d7.dataset.whatever = list[key].LicencePic;
							}
							trow.appendChild(d7);
							var d8 = document.createElement("td");
							var status = parseInt(list[key].Status);
							switch (status) {
								case <?php echo $olshopRegistered; ?>:
									d8.innerHTML = "新注册";
									break;
								case <?php echo $olshopApplied; ?>:
									d8.innerHTML = "提交审核";
									break;
								case <?php echo $olshopDeclined; ?>:
									d8.innerHTML = "审核失败";
									break;
								case <?php echo $olshopAccepted; ?>:
									d8.innerHTML = "审核通过";
									break;
								case <?php echo $olshopClosed; ?>:
									d8.innerHTML = "商家关闭";
									break;
								default:
									d8.innerHTML = status;
							}
							trow.appendChild(d8);
							var d9 = document.createElement("td");
							d9.innerHTML = list[key].TradeAmount;
							d9.className = "text-info";
							trow.appendChild(d9);
							var d10 = document.createElement("td");
							d10.innerHTML = list[key].WithdrawAmount;
							d9.className = "text-info";
							trow.appendChild(d10);
							d11 = document.createElement("input");
							d11.type = "button";
							d11.value = "查看收入记录";
							d11.id = key;
							if (d11.addEventListener) {
								d11.addEventListener('click', checkIncomeRecord, false);
							}
							else if (d11.attachEvent) {
								d11.attachEvent('onclick', checkIncomeRecord);
							}
							trow.appendChild(d11);
						}				    	
					}
					else {
						alert("搜索线下商家失败：" + data.error_msg);
					}
				}, "json");
			}

			function checkIncomeRecord(e)
			{
				location.href = "olspntrecord.php?sid=" + e.target.id;
			}
						
			$(document).ready(function(){
				
				$('#licenceModal').on('show.bs.modal', function (event) {
					
					var button = $(event.relatedTarget);
					var who = button.data('who');
					var src = button.data('whatever');
					
					var modal = $(this);
					modal.find('.modal-title').text(who + "的营业执照");
					document.getElementById("licencePic").src = "../olLicensePic/" + src;
				})
			});
		</script>
	</head>
	<body>
		<div style="padding: 10px 0 0 10px;" >
			<div class="navbar navbar-default">
				<form class="navbar-form">
					<input id="olsid" type="text" class="span2" placeholder="商家id">
					<input id="olsname" type="text" class="span2" placeholder="商家名称">
					<input id="ownerid" type="text" class="span2" placeholder="用户id">
					<button type="button" class="btn btn-default" onclick="searchOLShop()">搜索</button>
				</form>
			</div>
	        <div>
	        	<span id="searchresult"></span>
				<table id="tbl" border="1" style="text-align: center;">
					<tr>
						<th>商家id</th>
						<th>用户id</th>
						<th>店名</th>
						<th>联系人</th>
						<th>联系电话</th>
						<th>商家地址</th>
						<th>营业执照</th>
						<th>商家状态</th>
						<th>收款总额</th>
						<th>取现总额</th>
						<th>操作</th>
					</tr>
				</table>
	        </div>
		</div>	
		
		<div class="modal fade" id="licenceModal" tabindex="-1" role="dialog" aria-labelledby="licenceModalLabel">
			<div class="modal-dialog" role="document">
		    	<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="licenceModalLabel">营业执照</h4>
			    	</div>
					<div class="modal-body" style="text-align: center;">
						<img id="licencePic" src="" style="width: 80%; margin: 0 auto"></img>
			    	</div>
			    </div>
			</div>
		</div>
    </body>
</html>