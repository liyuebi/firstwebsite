<?php
	include "../php/admin_func.php";
	
	if (!checkLoginOrJump()) {
		exit();
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		
		<title>连物网管理系统</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/adminlte/AdminLTE.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/adminlte/skins/_all-skins.min.css" />
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script>
<!-- 		<script src="../js/slimScroll/jquery.slimscroll.min.js"></script> -->
<!-- 		<script src="../js/fastclick/fastclick.min.js"></script> -->
		<script src="../js/adminlte/adminlte.min.js"></script>
		<script type="text/javascript">
			
			$(document).ready(function() {
				$('#riframe').height($(window).height());
				$('#rightContent').height($(window).height());
				$('.main-sidebar').height($(window).height());
				
				$(window).resize(function() {
					$('#riframe').height($(window).height());
					$('#rightContent').height($(window).height());
					$('.main-sidebar').height($(window).height());
				});
			});
			
			var tmpmenu = 'index_Index';
			function makecss(obj){
				$('li[data-id="'+tmpmenu+'"]').removeClass('active');
				$(obj).addClass('active');
				tmpmenu = $(obj).attr('data-id');
			}
		</script>
	</head>
	<body class="hold-transition skin-blue">
		<div style="padding: 10px 10px 0 5px; height: 100%; float: left; border-right: 1px solid black; display: none;">
			<ul style="list-style: none; padding: 0">
<!-- 				<li><a href="companymgr.html">企业管理</a></li> -->
<!-- 				<li><a href="productmgr.php">产品管理</a></li> -->
				<li><a href="usermgr.php">用户管理</a></li>
				<li><a href="ordermgr.php">订单管理</a></li>
<!-- 				<li><a href="rechargemgr.php">充值管理</a></li> -->
<!-- 				<li><a href="withdrawmgr.php">取现管理</a></li> -->
				<li><a href="configmgr.php">配置管理</a></li>
				<li><a href="statistics.php">统计数据</a></li>
<!-- 				<li><a href="configRwdRate.php">配置动态拨比</a></li> -->
				<li><a href="postmgr.php">公告管理</a></li>
				<li><a href="adminmgr.php">管理员账号维护</a></li>
			</ul>
		</div>
		<div style="display: inline; float: left; padding: 10px 0 0 10px;" >
		</div>
		
		<div class="wrapper">
		<aside class="main-sidebar" style="overflow-y:auto;">
			<section class="sidebar">
				<ul class="sidebar-menu" data-widget="tree">
					<li class="treeview">
		        	    <a href="javascript:void(0)">
				            <i class="fa fa-home"></i><span>用户管理</span><i class="fa fa-angle-left pull-right"></i>
			            </a>
			            <ul class="treeview-menu">
		            		<li onclick="makecss(this)" data-id="index_User">
		            			<a href='usermgr.php' target='rightContent'><i class="fa fa-circle-o"></i>ID 查询</a>
							</li>	            
		            		<li onclick="makecss(this)" data-id="index_User1">
		            			<a href='usermgr1.php' target='rightContent'><i class="fa fa-circle-o"></i>条件查询</a>
							</li>	            
		            	</ul>
					</li>
					<li class="treeview">
		        	    <a href="javascript:void(0)">
				            <i class="fa fa-home"></i><span>订单管理</span><i class="fa fa-angle-left pull-right"></i>
			            </a>
			            <ul class="treeview-menu">
		            		<li onclick="makecss(this)" data-id="index_Order">
		            			<a href='ordermgr.php' target='rightContent'><i class="fa fa-circle-o"></i>待发货订单</a>
							</li>
		            		<li onclick="makecss(this)" data-id="index_Order3">
		            			<a href='orderExported.php' target='rightContent'><i class="fa fa-circle-o"></i>已导出订单</a>
							</li>
		            		<li onclick="makecss(this)" data-id="index_Order1">
		            			<a href='ordermgr1.php' target='rightContent'><i class="fa fa-circle-o"></i>待确认订单</a>
							</li>
		            		<li onclick="makecss(this)" data-id="index_Order2">
		            			<a href='ordermgr2.php' target='rightContent'><i class="fa fa-circle-o"></i>订单查询</a>
							</li>	            
		            	</ul>
					</li>
					<li class="treeview">
		        	    <a href="javascript:void(0)">
				            <i class="fa fa-home"></i><span>配置管理</span><i class="fa fa-angle-left pull-right"></i>
			            </a>
			            <ul class="treeview-menu">
		            		<li onclick="makecss(this)" data-id="index_Config">
		            			<a href='configmgr.php' target='rightContent'><i class="fa fa-circle-o"></i>全局配置</a>
							</li>	            
		            		<li onclick="makecss(this)" data-id="index_Config">
		            			<a href='lvl_configmgr.php' target='rightContent'><i class="fa fa-circle-o"></i>等级配置</a>
							</li>	            
		            	</ul>
					</li>
					<li class="treeview">
		        	    <a href="javascript:void(0)">
				            <i class="fa fa-home"></i><span>统计数据</span><i class="fa fa-angle-left pull-right"></i>
			            </a>
			            <ul class="treeview-menu">
		            		<li onclick="makecss(this)" data-id="index_Statis">
		            			<a href='statistics.php' target='rightContent'><i class="fa fa-circle-o"></i>总统计</a>
							</li>	            
		            		<li onclick="makecss(this)" data-id="index_dayStatis">
		            			<a href='statistics_day.php' target='rightContent'><i class="fa fa-circle-o"></i>按日统计</a>
							</li>	            
		            	</ul>
					</li>
					<li>
		        	    <a href="postmgr.php" target='rightContent'>
				            <i class="fa fa-home"></i><span>公告管理</span><i class="fa fa-angle-left pull-right"></i>
			            </a>
<!--
			            <ul class="treeview-menu">
		            		<li onclick="makecss(this)" data-id="index_Poster">
		            			<a href='postmgr.php' target='rightContent'><i class="fa fa-circle-o"></i>订单查询</a>
							</li>	            
		            	</ul>
-->
					</li>
					<li> <!-- class="treeview"> -->
		        	    <a href="adminmgr.php" target="rightContent">
				            <i class="fa fa-home"></i><span>管理员账号维护</span><i class="fa fa-angle-left pull-right"></i>
			            </a>
<!--
			            <ul class="treeview-menu">
		            		<li onclick="makecss(this)" data-id="index_Account">
		            			<a href='adminmgr.php' target='rightContent'><i class="fa fa-circle-o"></i>订单查询</a>
							</li>	            
		            	</ul>
-->
					</li>
				</ul>
			</section>
		</aside>
		<section class="content-wrapper right-side" id="riframe" style="margin: 0px; margin-left: 250px;">
			<iframe id='rightContent' name='rightContent' width="100%" ></iframe>
		</section>
		</div>
		
    </body>
    <div style="text-align:center;">
    </div>
</html>