<?php  

$userCnt = 0;
$offlineShopCnt = 0;
$vaildOfflineShopCnt = 0;

include '../php/constant.php';
include "../php/database.php";
$con = connectToDB();
if ($con) {

	$res = mysqli_query($con, "select * from ClientTable");
	$userCnt = mysqli_num_rows($res);

	$res = mysqli_query($con, "select * from OfflineShop");
	$offlineShopCnt = mysqli_num_rows($res);

	$res = mysqli_query($con, "select * from OfflineShop where Status='$olshopAccepted'");
	$vaildOfflineShopCnt = mysqli_num_rows($res);
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>索引</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" href="../css/adminlte/AdminLTE.min.css">
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			$(document).ready(function() {

				updateNums();

				// window.setInterval(updateNums, 5000);
			});

			function updateNums()
			{
				var cnt = 0; 

				// 更新新购产品包数
				cnt = getCookie('c_n_u_p');
				if (cnt != '') {
					document.getElementById('num_pack').innerHTML = cnt;
				}

				// 更新新用户订单数
				cnt = getCookie('c_n_u_o');
				if (cnt != '') {
					document.getElementById('num_new').innerHTML = cnt;
				}

				// 更新充话费订单数
				cnt = getCookie('c_p_c_o');
				if (cnt != '') {
					document.getElementById('num_p').innerHTML = cnt;
				}

				// 更新使用线下积分充话费订单数
				cnt = getCookie('c_p_c_f_n');
				if (cnt != '') {
					document.getElementById('num_p_new').innerHTML = cnt;
				}

				// 更新充油费订单数
				cnt = getCookie('c_o_c_o');
				if (cnt != '') {
					document.getElementById('num_o').innerHTML = cnt;
				}

				// 更新线下商家申请审核数
				cnt = getCookie('c_ol_r');
				if (cnt != '') {
					document.getElementById('num_ol_r').innerHTML = cnt;
				}
				
				// 更新线下商家申请提现数
				cnt = getCookie('c_ol_wd_a');
				if (cnt != '') {
					document.getElementById('num_ol_w').innerHTML = cnt;
				}
			}
		</script>
	</head>
	<body>
		<div style="padding: 10px 0 0 10px;" >
	      <div class="row">
	        <div class="col-md-3">
	          <div class="box box-primary box-solid">
	            <div class="box-header with-border">
	              <h3 class="box-title">信息</h3>

<!-- 	              <div class="box-tools pull-right">
	                <span data-toggle="tooltip" title="3 New Messages" class="badge bg-light-blue">3</span>
	                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
	                </button>
	                <button type="button" class="btn btn-box-tool" data-toggle="tooltip" title="Contacts" data-widget="chat-pane-toggle">
	                  <i class="fa fa-comments"></i></button>
	                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
	              </div>
 -->	           
 				</div>
	            <!-- /.box-header -->
	            <div class="box-body">
	            	<ul class="nav nav-stacked">
	            		<li><a href="#">用户 <span class="pull-right text-info"><?php echo $userCnt; ?></span></a></li>
	            		<li><a href="#">线下商家 <span class="pull-right text-primary"><?php echo $vaildOfflineShopCnt . '/' . $offlineShopCnt; ?></span></a></li>
	            	</ul>
	            </div>
	          </div>
	        </div>
	        <div class="col-md-3">
	          <div class="box box-primary box-solid">
	            <div class="box-header with-border">
	              <h3 class="box-title">订单</h3>

<!-- 	              <div class="box-tools pull-right">
	                <span data-toggle="tooltip" title="3 New Messages" class="badge bg-light-blue">3</span>
	                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
	                </button>
	                <button type="button" class="btn btn-box-tool" data-toggle="tooltip" title="Contacts" data-widget="chat-pane-toggle">
	                  <i class="fa fa-comments"></i></button>
	                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
	              </div>
 -->	           
 				</div>
	            <!-- /.box-header -->
	            <div class="box-body">
	            	<ul class="nav nav-stacked">
	            		<li><a href="ordermgr5.php">产品包订单 <span id='num_pack' class="pull-right badge bg-blue">0</span></a></li>
	            		<li><a href="ordermgr1.php">话费订单 <span id='num_p' class="pull-right badge bg-blue">0</span></a></li>
	            		<li><a href="ordermgr3.php">话费订单（新用户） <span id='num_p_new' class="pull-right badge bg-blue">0</span></a></li>
	            		<li><a href="ordermgr2.php">油费订单 <span id='num_o' class="pull-right badge bg-blue">0</span></a></li>
	            		<li><a href="ordermgr.php">新用户订单 <span id='num_new' class="pull-right badge bg-blue">0</span></a></li>
	            	</ul>
	            </div>
	          </div>
	        </div>
	        <div class="col-md-3">
	          <div class="box box-primary box-solid">
	            <div class="box-header with-border">
	              <h3 class="box-title">线下商家</h3>
 				</div>
	            <!-- /.box-header -->
	            <div class="box-body">
	            	<ul class="nav nav-stacked">
	            		<li><a href="olsreviewmgr.php">待审核商家 <span id='num_ol_r' class="pull-right badge bg-blue">0</span></a></li>
	            		<li><a href="withdrawmgr.php">提现申请 <span id='num_ol_w' class="pull-right badge bg-blue">0</span></a></li>
	            	</ul>
	            </div>
	          </div>
	        </div>
	      </div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>