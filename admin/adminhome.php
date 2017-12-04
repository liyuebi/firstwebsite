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
		<meta name="viewport" content="initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/adminlte/AdminLTE.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/adminlte/skins/_all-skins.min.css" />
		<!-- Font Awesome -->
	    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script>
<!-- 		<script src="../js/slimScroll/jquery.slimscroll.min.js"></script> -->
<!-- 		<script src="../js/fastclick/fastclick.min.js"></script> -->
		<!-- Bootstrap 3.3.7 -->
		<!-- <script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script> -->
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
	<body class="hold-transition skin-blue sidebar-mini">		
		<div class="wrapper">
			<header class="main-header">
			    <!-- Logo -->
			    <a href="index2.html" class="logo">
			    	<!-- mini logo for sidebar mini 50x50 pixels -->
					<span class="logo-mini"><b>连</b></span>
					<!-- logo for regular state and mobile devices -->
					<span class="logo-lg"><b>连物网</b>后台</span>
			    </a>
				<!-- Header Navbar: style can be found in header.less -->
		    	<nav class="navbar navbar-static-top">
		        	<!-- Sidebar toggle button-->
		        	<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
		        		<span class="sr-only">Toggle navigation</span>
		        	</a>
	    		</nav>
			</header>			
			
			<aside class="main-sidebar">
				<section class="sidebar">
					<ul class="sidebar-menu" data-widget="tree">
						<li class="treeview">
			        	    <a href="javascript:void(0)">
					            <i class="fa fa-user"></i><span>用户管理</span><i class="fa fa-angle-left pull-right"></i>
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
					            <i class="fa fa-money"></i><span>订单管理</span><i class="fa fa-angle-left pull-right"></i>
				            </a>
				            <ul class="treeview-menu">
			            		<li onclick="makecss(this)" data-id="index_Order">
			            			<a href='ordermgr.php' target='rightContent'><i class="fa fa-circle-o"></i>新用户订单</a>
								</li>
			            		<li onclick="makecss(this)" data-id="index_Order3">
			            			<a href='ordermgr1.php' target='rightContent'><i class="fa fa-circle-o"></i>话费订单</a>
								</li>
			            		<li onclick="makecss(this)" data-id="index_Order1">
			            			<a href='ordermgr2.php' target='rightContent'><i class="fa fa-circle-o"></i>油费订单</a>
								</li>
	<!--
			            		<li onclick="makecss(this)" data-id="index_Order2">
			            			<a href='ordermgr2.php' target='rightContent'><i class="fa fa-circle-o"></i>订单查询</a>
								</li>	            
	-->
			            	</ul>
						</li>
						<li class="treeview">
			        	    <a href="javascript:void(0)">
					            <i class="fa fa-shopping-cart"></i><span>线下商家</span><i class="fa fa-angle-left pull-right"></i>
				            </a>
				            <ul class="treeview-menu">
			            		<li onclick="makecss(this)" data-id="index_OLShopSearch">
			            			<a href='olsmgr.php' target='rightContent'><i class="fa fa-circle-o"></i>商家查询</a>
								</li>	            
			            		<li onclick="makecss(this)" data-id="index_OlShopApply">
			            			<a href='olsreviewmgr.php' target='rightContent'><i class="fa fa-circle-o"></i>待审核商家</a>
								</li>	            
			            		<li onclick="makecss(this)" data-id="index_OlShopWithdraw">
			            			<a href='withdrawmgr.php' target='rightContent'><i class="fa fa-circle-o"></i>提现申请</a>
								</li>	           
			            	</ul>
						</li>
						<li class="treeview">
			        	    <a href="javascript:void(0)">
					            <i class="fa fa-edit"></i><span>配置管理</span><i class="fa fa-angle-left pull-right"></i>
				            </a>
				            <ul class="treeview-menu">
			            		<li onclick="makecss(this)" data-id="index_Config">
			            			<a href='configmgr.php' target='rightContent'><i class="fa fa-circle-o"></i>全局配置</a>
								</li>	            
	<!--
			            		<li onclick="makecss(this)" data-id="index_Config">
			            			<a href='lvl_configmgr.php' target='rightContent'><i class="fa fa-circle-o"></i>等级配置</a>
								</li>	            
	-->
			            	</ul>
						</li>
						<li class="treeview">
			        	    <a href="javascript:void(0)">
					            <i class="fa fa-bar-chart-o"></i><span>统计数据</span><i class="fa fa-angle-left pull-right"></i>
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
					            <i class="fa fa-envelope-o"></i><span>公告管理</span><!-- <i class="fa fa-angle-left pull-right"></i> -->
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
					            <i class="fa fa-eye"></i><span>管理员账号维护</span><!-- <i class="fa fa-angle-left pull-right"></i> -->
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
			<section class="content-wrapper right-side" id="riframe">
				<iframe id='rightContent' name='rightContent' width="100%"></iframe>
			</section>
		</div>
		
    </body>
    <div style="text-align:center;">
    </div>
</html>