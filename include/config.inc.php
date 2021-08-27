<?php
#*****************************************************************************#				
				
				#********** DATABASE CONFIGURATION **********#
				define('DB_SYSTEM',						'mysql');
				define('DB_HOST',							'localhost');
				define('DB_NAME',							'blog');
				define('DB_USER',							'root');
				define('DB_PWD',							'');
				
				
				#********** FORM CONFIGURATION **********#
				define('INPUT_MIN_LENGTH',				2);
				define('INPUT_MAX_LENGTH',				256);


				#********** IMAGE UPLOAD CONFIGURATION **********#
				define('IMAGE_MAX_WIDTH', 				800);
				define('IMAGE_MAX_HEIGHT', 			800);
				define('IMAGE_MAX_SIZE', 				800*1024);				
				define('IMAGE_ALLOWED_MIME_TYPES', 	array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'));
				
				
				#********** STANDARD PATHS CONFIGURATION **********#
				define('IMAGE_UPLOAD_PATH',			'uploaded_images/');
				
				#********** DEBUGGING **********#
				
				define('DEBUG', 							true);	// DEBUGGING for main documents
				define('DEBUG_F', 						true);	// DEBUGGING for functions
				define('DEBUG_DB', 						true);	// DEBUGGING for database functions
				
				
#*****************************************************************************#
?>