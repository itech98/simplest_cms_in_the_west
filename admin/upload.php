<?php
if (!empty($_FILES)) {
	if(is_array($_FILES)) {
		if(is_uploaded_file($_FILES['userImage']['tmp_name'])) {
				//
				include_once('../inc/class_database.php');
				include_once('../inc/class_general.php');
				$f  	= 	config::get_path_url("file_path");
				$f1 	=   str_replace( "inc" , "" , $f  );
				$t  	=	config::get_options( "theme" );
				$t1 	=   str_replace( "inc" , "" , $t  );
				$f1		= 	$f1.'theme/'.$t1.'/';
				$u		= 	config::get_path_url("url");

				$sourcePath 	= $_FILES['userImage']['tmp_name'];
				$tp				= $f1.$_FILES['userImage']['name'];
				$targetPath 	= $u . '/theme/' . $t1 . '/' . $_FILES['userImage']['name'];
				if(move_uploaded_file( $sourcePath , $tp )) {
						echo $targetPath;
				}
		}
	}
}
?>