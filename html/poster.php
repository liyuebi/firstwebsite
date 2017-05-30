<?php
	
$row = false;
$idx = 0;

if (isset($_GET['idx'])) {
	$idx = $_GET['idx'];
}

include "../php/database.php";
$con = connectToDB();
if (!$con)
{
	return false;
}

if ($idx > 0) {
	$result = mysql_query("select * from PostTable where IndexId='$idx'");	
	if ($result && mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>通告</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
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
		</script>
	</head>
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
        <div align="center">
            <h3><?php echo $row["Title"]; ?></h3>
        </div>
        
        <div name="display">
			<p>
				<?php
					if ($row) {
						$filename = $row["TextFile"];
						$fileNameStr = dirname(__FILE__) . "/../poster/" . $filename;
						
						if (file_exists($fileNameStr)) {
							
							$file = fopen($fileNameStr, "r");
							if (!$file) {
								return;
							}
							while (($line = fgets($file)) !== false) {
				?>
								<p><?php echo $line; ?></p>
				<?php
							}
							fclose($file);
						}
					}
				?>
			</p>
			<p style="float: right;">
				<?php
					date_default_timezone_set('PRC');
					echo date("Y-m-d", $row["OnlineTime"]); 
				?>
			</p>
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>