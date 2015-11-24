<html>
<head>
<title>SIMPLEST CMS IN THE WEST</title>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=iso-8859-1" />
<meta name="description" content="Your website description goes here" />
<meta name="keywords" content="your,keywords,goes,here" />
<link rel="stylesheet" href="style.css" type="text/css" />
<link rel="stylesheet" href="GOOGLEFORMS/prettify/prettify.css"  type="text/css" />
</head>

<body>
<div id="container" >
		<div id="headerWrap">
			<div id="header">
				<h1><a href="index.html">SIMPLEST CMS IN THE WEST</a></h1>
				<ul id="navigation">
					<li><a href="#">ABOUT</a></li>
					<li><a href="#">ADD PAGE</a></li>
					<li><a href="#">CATEGORIES</a></li>
					<li><a href="#">SETTINGS</a></li>
				</ul>
			</div>
		</div>
		<div id="content">
			<div id="contentHeader">
				<div id="siteDescription"><p>SIMPLEST CMS IN THE WEST !!!</p></div>
			</div>
			<?php
			if (isset($error)) { echo '<h3 style="color:#f30b21;"><color ="red">'.$error.'</h3>'; }
			?>
		
			<div id="main">
					<h2>LOGOUT</h2>
					<?php
						$_SESSION = array(); // Destroy the variables.
						session_destroy(); // Destroy the session itself.
						//setcookie (session_name(), '', time()-300, '/', '', 0); // Destroy the cookie.    
						///setcookie("loginuser", "", time()-3600);
						$url = 'login.php';
						ob_end_clean(); // Delete the buffer.
						header("Location: $url");
						exit(); // Quit the script.
					?>
			</div>
		
		
		
			<div id="secondary">
				<h2>SETUP</h2>
				<p>Initial setup of the CMS requires entry of some basic info - and email address which can be
				any valid email address you use - e.g. Hotmail or Yahoo or gmail. a password to get into the 
				admin area and a name for your site.</p>
				<h2>Search</h2>
				<form method="get" id="searchform" action="#"><fieldset>
					<input type="text" name="s" id="searchtext" />
					<input type="submit" id="searchsubmit" value="search" />
				</fieldset></form>
			</div>
		</div>
</div>


</body>
</html>
