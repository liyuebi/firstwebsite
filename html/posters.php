<?php
	
$res = false;

include "../php/database.php";
$con = connectToDB();
if ($con) {
	
 	$res = mysql_query("select * from PostTable where Status='$postStatusOnline' order by OnlineTime desc");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>公告</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		<link rel="stylesheet" href="../css/buttons.css">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/md5.js" ></script>
		<script type="text/javascript">

			$(document).ready(function(){
				if (isNotLoginAndJump()) {
					return;
				}
			})
			
			function goback()
			{
				location.href = "home.php";
			}
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-4 col-md-4"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-4 col-md-4"><h3 style="display: table-cell; text-align: center; color: white">公告</h3></div>
				<div class="col-xs-4 col-md-4"></div>
			</div>
		</div>
		 
        <div style="margin-top: 5px;">
			<?php 
				if ($res) {
					while ($row = mysql_fetch_array($res)) {
			?>
					<a href="poster.php?idx=<?php echo $row["IndexId"]; ?>">
						<p><?php echo $row["Title"]; ?></p>
					</a>
			<?php
					}
				}
			?>
        </div>
    </body>
</html>