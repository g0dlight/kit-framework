<!DOCTYPE HTML>
<html>
<head>
	<style type="text/css">
		body{
			background-color:#292929; font-size:18px; margin:0; padding:0; font-family:Verdana;
		}
		.clear{
			clear:both; margin:0; padding:0;
		}
		.container{
			margin-bottom:50px; width:100%; padding:0;
		}
		.headLine{
			background-color:#78C878; padding:8px; font-size:32px; font-weight:bold;
		}
		.mainBox{
			background-color:#97b7b4;
		}
		.box{
			overflow-wrap:break-word;
		}
		.leftBox{
			float:left; width:59%; padding:4px 0.5% 0 0.5%;
		}
		.rightBox{
			float:right; width:40%;
		}
		.error{
			background-color:#b3d3d0; margin-bottom:4px; padding:4px;
		}
		.fatal{
			background-color:#7a1915; color:#ffffff; font-weight:bold; text-align:center; padding:8px;
		}
		.more{
			background-color:#e7e7e7; margin-bottom:0; padding:8px;
		}
		.item{
			float:left; margin:10px; padding:6px; border:2px dotted #aeaeae;
		}
		.title{
			font-size:22px;
		}
		.boxName{
			background-color:#3e5a7c; color:#ffffff; padding:4px;
		}
		.details{
			margin:8px 0; color:#292929; text-align:center;
		}
		.trace{
			margin:2px 0; color:#292929; text-align:center; font-size:14px; cursor:pointer;
		}
		ul{
			font-size:16px; list-style-type:square;
		}
		pre{
			display:none; font-size:12px;
		}
		.trace:focus > pre{
			display:block; text-align:left;
		}
	</style>
	<title>Oops</title>
</head>
<body><div class="container">
	<div class="headLine">Oops! <?php echo (count($errors) > 1) ? count($errors).' errors' : 'An error'; ?> occurred!</div>
	<div class="mainBox">
		<div class="box leftBox">
			<?php
			foreach ($errors as $error) {
				echo '<div class="error">';
				echo '<div class="title"><b>'.$error['title'].':</b> '.$error['message'].'</div>';
				echo '<div class="details">on file: <b>'.$error['file'].'</b> in line: <b>'.$error['line'].'</b></div>';
				if ($error['fatal']) {
					echo '<div class="fatal">This is a fatal error! the script can\'t proceed!</div>';
				}
				if ($error['trace']) {
					echo '<div class="trace" tabindex="0">Show Trace';
					echo '<pre>';
					foreach ($error['trace'] as $trace) {
						$print = print_r($trace, true);
						echo htmlentities($print);
					}
					echo '</pre></div>';
				}
				echo '</div>';
			}
			?>
		</div>
		<div class="box rightBox">
			<?php
			$dumpList = array(
				'GET' => &$_GET,
				'POST' => &$_POST,
				'FILES' => &$_FILES,
				'SESSION' => &$_SESSION,
				'COOKIE' => &$_COOKIE,
			);
			foreach ($dumpList as $dumpKey => $dumpValue) {
				echo '<div class="boxName title"><b>'.$dumpKey.'</b></div>';
				echo '<div class="more">';
				foreach ((array) $dumpValue as $key => $value) {
					echo '<div class="item"><b>'.$key.'</b>: '.$value.'</div>';
				}
				if (!count((array) $dumpValue)) {
					echo '<li>Empty</li>';
				}
				else {
					echo '<p class="clear"></p>';
				}
				echo '</div>';
			}
			?>
		</div>
		<p class="clear"></p>
	</div>
	<p class="clear"></p>
</div></body>
</html>
