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
			margin:50px auto; width:60%;
		}
		.headLine{
			background-color:#78c878; padding:5px;
		}
		.descriptionLine{
			background-color:#97b7b4; padding:10px 5px 50px 5px;
		}
		h2,h3{
			margin:0; padding:0;
		}
		.trace{
			margin:2px 0; color:#292929; text-align:center; font-size:14px; cursor:pointer;
		}
		pre{
			display:none; font-size:12px; word-wrap: break-word;
		}
		.trace:focus > pre{
			display:block; text-align:left;
		}
    </style>
    <title>Oops</title>
</head>
<body>
	<div class="container">
		<div class="headLine">
			<h2>Error 404.</h2>
		</div>
		<div class="descriptionLine">
			<h3>The requested URL was not found on this server.</h3>
			<?php
			echo '<div class="trace" tabindex="0">Show Trace<pre>';
			print_r($error);
			echo '</pre></div>';
			?>
		</div>
	</div>
</body>
</html>
