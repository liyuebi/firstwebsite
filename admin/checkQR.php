<?php

$idx = '';
$img = '';
$name = '';

if (isset($_GET['img'])) {
	$img = $_GET['img'];
}

if (isset($_GET['id'])) {
	$idx = $_GET['id'];
}

if (isset($_GET['name'])) {
	$name = $_GET['name'];
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>商家二维码</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle1.0.1.css" />
		<link rel="stylesheet" href="../css/buttons.css">
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/jquery.form-3.46.0.js" ></script>
		<script src="../js/md5.js" ></script>
		<script type="text/javascript">
				
			$(document).ready(function(){

				// $('#main').height($(window).height() - $('#title').height();
				setContentVertialCenter();
				
				$(window).resize(function() {

					setContentVertialCenter();
				});
			});

			function setContentVertialCenter()
			{
				var mainHeight = $(window).height();
				var contentHeight = $(`#content`).height();

				if (contentHeight >= mainHeight) {
					$('#main').height(contentHeight);
					$('#main').css("margin-top", "10px");
					$('#space').height(0);
				}
				else {
					$('#main').height(mainHeight);
					$('#main').css("margin-top", "0");
					$('#space').height((mainHeight - contentHeight) / 2);	
				}
			}

			function goback() 
			{
				location.href = "myolshop.php";
			}
		</script>
	</head>
	<body>
		<div id="main" style="margin: 10px 3px 0 3px;">
			<div id="space" width="50px">
			</div>
			<div id="content">
				<div class="alert-info" style="padding: 20px; text-align: center;">
					<h3>连物网商家收款二维码</h3>
					<div style="padding: 40px 10% 0 10%;">
						<p>
							<span class="pull-left"><b>商家编号：</b> <?php echo $idx; ?></span>
							<span class="pull-right"><?php echo $name;?></span>
						</p>
						<br>
						<img src="<?php echo '../olqrc/' . $img; ?>" style="width: 100%;" >
						<div style="margin-top: 20px; margin-bottom: 0">
							<font size="5" color="green">连接你我   半价消费</font>
						</div>
					</div>
				</div>
			</div>
		</div>	
	</body>
</html>
