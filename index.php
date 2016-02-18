<!DOCTYPE>
<?php require_once 'functions.php';?>
<html>
<head>
	<title>Instagram Admin</title>
	<link href='https://fonts.googleapis.com/css?family=Raleway:400,200,600' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="inc/css/style.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	<script src="inc/js/scripts.js"></script>
</head>
<body>

<div class="sidebar">
	<ul id="js-sidebar-nav" class="top-nav">
		<li data-nav="confirmed">
			Confirmed <span class="count"><?php echo count_confirmed(); ?></span>
		</li>
		<li data-nav="all_posts">
			#530medialab <span class="count"><?php echo count_stored_posts(); ?></span>
		</li>
	</ul>

	<ul id="js-sidebar-admin" class="bottom-nav">
		<li data-nav="update">
			update
		</li>
		<li data-nav="reload_all">
			reload all
		</li>
	</ul>


</div>

<div class="main-container">
	<div id="js-main-content" class="internal">

	</div>
</div>



</body>
</html>