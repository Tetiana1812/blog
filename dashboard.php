<?php
#*****************************************************************************#
				
			#***********************************#
			#********** CONFIGURATION **********#
			#***********************************#
			
			require_once('./include/config.inc.php');
			require_once('./include/form.inc.php');
			require_once('./include/db.inc.php');
			require_once('./include/logger.inc.php');

#*****************************************************************************#
			
			#******************************************#
			#********** INITIALIZE VARIABLES **********#
			#******************************************#
			
			$errorMessage = NULL;
			$erfolgsMessage = NULL;
			$erfolgsBildMessage = NULL;
			$errorImageUpload = NULL;

			$category = NULL;
			$align = NULL;
			$content = NULL;
			$imagePath = NULL;
			$headline = NULL;
			$newCategory = NULL;

#*****************************************************************************#

			#****************************************#
			#********** SECURE PAGE ACCESS **********#
			#****************************************#
			
			// Session fortführen
			session_name('blog');
			session_start();

/* if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)	print_r($_SESSION);					
if(DEBUG)	echo "</pre>";	
 */
			// Prüfen, ob der aufrufende User eingeloggt ist
			if( !isset($_SESSION['usr_id']) ) {
				logout();
			}
#*****************************************************************************#				

			#********************************************#
			#********** PROCESS URL PARAMETERS **********#
			#********************************************#

/*
if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)	print_r($_GET);					
if(DEBUG)	echo "</pre>";	
*/
			// Schritt 1 URL: Prüfen, ob URL-Parameter übergeben wurde
			if( isset($_GET['action']) ) {
if(DEBUG)		echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: URL-Parameter 'action' wurde übergeben. <i>(" . basename(__FILE__) . ")</i></p>\n";										
						
				// Schritt 2 URL: Werte auslesen, entschärfen, DEBUG-Ausgabe
				$action = cleanString( $_GET['action'] );
if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$action: $action <i>(" . basename(__FILE__) . ")</i></p>\n";
				
				// Schritt 3 URL: Verzweigen

				#********** LOGOUT **********#
				if( $action == 'logout' ) {
if(DEBUG)			echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Logout wird durchgeführt... <i>(" . basename(__FILE__) . ")</i></p>\n";
											
					// Schritt 4 URL: Daten weiterverarbeiten

					logout();

				}//Verzweigen LOGOUT END

			}//PROCESS URL PARAMETERS END

#*****************************************************************************#

			#***********************************#
			#********** DB OPERATIONS **********#
			#***********************************#

			// Schritt 1 DB: DB-Verbindung herstellen
			$pdo = dbConnect('blog');

			#********** FETCH DATA FROM DB **********#
			$sql 		= 'SELECT usr_id, usr_firstname, usr_lastname FROM users WHERE usr_id = :ph_usr_id';
			$params 	= array( 'ph_usr_id' => $_SESSION['usr_id'] );

			// Schritt 2 DB: SQL-Statement vorbereiten
			$statement = $pdo->prepare($sql);
				
			// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
			try {	
				$statement->execute($params);						
			} catch(PDOException $error) {
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
				$dbError = 'Fehler beim Zugriff auf die Datenbank!';
			}	
			
			// Schritt 4 DB: Daten weiterverarbeiten
			// Bei lesendem Zugriff: Datensätze abholen
			$row = $statement->fetch(PDO::FETCH_ASSOC);
			
/* if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)	print_r($row);					
if(DEBUG)	echo "</pre>";
 */

#*****************************************************************************#


			#*******************************************#
			#********** PROCESS FORM KATEGORIE **********#
			#*******************************************#

/* if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)	print_r($_POST);					
if(DEBUG)	echo "</pre>"; */

			// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
			if( isset($_POST['newCategorySend']) ) {
if(DEBUG)		echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Formular 'newCategorySend' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";										

				// Schritt 2 FORM: Werte auslesen, entschärfen, DEBUG-Ausgabe
				$newCategory= cleanString($_POST['newCategory']);

if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$newCategory: $newCategory <i>(" . basename(__FILE__) . ")</i></p>\n";

				// Schritt 3 FORM: Feldvalidierung
				$errorNewCategory = checkInputString($newCategory);

				#********** FINAL FORM VALIDATION **********#
				if( $errorNewCategory ) {
					//Fehlerfall
if(DEBUG)			echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";				
					$errorMessage = '<p class="error">Das Formular enthält noch Fehler! Sie sollen eine neue Kategorie anlegen</p>';
				} else {
					//Erfolgsfall
if(DEBUG)			echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular ist fehlerfrei und wird nun verarbeitet... <i>(" . basename(__FILE__) . ")</i></p>\n";				

					#***********************************#
					#********** DB OPERATIONS **********#
					#***********************************#

					#********** CHECK IF KATEGORIE ALREADY EXISTS IN DATABASE **********#
if(DEBUG)			echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Prüfe, ob Kategorie bereits registriert wurde... <i>(" . basename(__FILE__) . ")</i></p>\n";

					$sql 		= 'SELECT COUNT(cat_name) FROM categories WHERE cat_name = :ph_cat_name';
					$params 	= array( 'ph_cat_name' => $newCategory );
					
					// Schritt 2 DB: SQL-Statement vorbereiten
					$statement = $pdo->prepare($sql);
				
					// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
					try {	
						$statement->execute($params);						
					} catch(PDOException $error) {
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
						$dbError = 'Fehler beim Zugriff auf die Datenbank!';
					}

					// Schritt 4 DB: Daten weiterverarbeiten
					// Bei SELECT COUNT(): Wert von COUNT() auslesen
					$count = $statement->fetchColumn();
if(DEBUG)			echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$count: $count <i>(" . basename(__FILE__) . ")</i></p>\n";
				
					if( $count ) {
						// Fehlerfall
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Die Kategory '$newCategory' ist bereits in der DB registriert! <i>(" . basename(__FILE__) . ")</i></p>\n";				
						$errorMessage = '<p class="error">Diese Kategorie ist bereits in der Datenbank registriert!</p>';
					
					} else {
						// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Die Kategory '$newCategory' ist noch nicht in der DB registriert. <i>(" . basename(__FILE__) . ")</i></p>\n";				
					
						#********** INSERT KATEGORIE INTO DB **********#
if(DEBUG)				echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Speichere Kategory in DB... <i>(" . basename(__FILE__) . ")</i></p>\n";

						$sql 	= 'INSERT INTO categories
									(cat_name)
									VALUES
									(:ph_cat_name)';
						$params = array( 'ph_cat_name' => $newCategory );
		
						// Schritt 2 DB: SQL-Statement vorbereiten
						$statement = $pdo->prepare($sql);
							
						// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
						try {	
							$statement->execute($params);						
						} catch(PDOException $error) {
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
						$dbError = 'Fehler beim Zugriff auf die Datenbank!';
						}
				
						// Schritt 4 DB: Daten weiterverarbeiten

						// Bei schreibendem Vorgang: Schreiberfolg prüfen
						$rowCount = $statement->rowCount();
if(DEBUG)				echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";
													
						if( !$rowCount ) {
							// Fehlerfall
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Speichern der Kategory! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							$dbMessage = "<h3 class='error'>Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.</h3>";

						} else {
							// Erfolgsfall

if(DEBUG)					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Kategory war erfolgreich gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";				
							$erfolgsMessage = '<p class="success">Diese Kategorie war erfolgsreich gespeichert!</p>';
							$newCategory = NULL;
						}//INSERT KATEGORIE INTO DB END									

					}//CHECK IF KATEGORIE ALREADY EXISTS IN DATABASE END

				}//FINAL FORM VALIDATION END

			}//PROCESS FORM KATEGORIE END
#*****************************************************************************#

			#***********************************#
			#********** DB OPERATIONS **********#
			#***********************************#

			// Schritt 1 DB: DB-Verbindung herstellen
			//ist bereits geschehen

			#********** FETCH KATEGORIES FROM DB **********#
			$sql 		= 'SELECT cat_id, cat_name FROM categories';

			// Schritt 2 DB: SQL-Statement vorbereiten
			$statement = $pdo->prepare($sql);
				
			// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
			try {	
				$statement->execute();						
			} catch(PDOException $error) {
if(DEBUG)		echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
				$dbError = 'Fehler beim Zugriff auf die Datenbank!';
			}	
						
			// Schritt 4 DB: Daten weiterverarbeiten
			// Bei lesendem Zugriff: Datensätze abholen
			$rowCategory = $statement->fetchAll(PDO::FETCH_ASSOC);
						
/* if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)	print_r($rowCategory);					
if(DEBUG)	echo "</pre>"; */
		
	
#*****************************************************************************#

			#***********************************************#
			#********** PROCESS FORM BLOGFORMULAR **********#
			#***********************************************#

			// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
			if( isset($_POST['newEntryForm']) ) {
if(DEBUG)		echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Formular 'newEntryForm' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";										

				// Schritt 2 FORM: Werte auslesen, entschärfen, DEBUG-Ausgabe
				$category 	= cleanString($_POST['category']);
				$headline 	= cleanString($_POST['headline']);
				$align 		= cleanString($_POST['align']);
				$content 	= cleanString($_POST['content']);

/* if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$category: $category <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$headline: $headline <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$align: $align <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$content: $content <i>(" . basename(__FILE__) . ")</i></p>\n";
 */
				// Schritt 3 FORM: Feldvalidierung
				$errorCategory = checkInputString($category, 1);
				$errorHeadline = checkInputString($headline);
				$errorAlign = checkInputString($align, 1, 2);
				$errorContent = checkInputString($content, 10, 65535);

				#********** FINAL FORM VALIDATION **********#
				if( $errorCategory OR $errorHeadline OR $errorAlign OR $errorContent) {
					//Fehlerfall
if(DEBUG)			echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";				
					$errorMessage = '<p class="error">Das Formular enthält noch Fehler! Bitte füllen Sie alle Felder aus!</p>';
				} else {
					//Erfolgsfall
if(DEBUG)			echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular ist fehlerfrei und wird nun verarbeitet... <i>(" . basename(__FILE__) . ")</i></p>\n";				



					#********** IMAGE UPLOAD **********#
					
					// Prüfen, ob Bildupload vorliegt
/* if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)	print_r($_FILES);					
if(DEBUG)	echo "</pre>"; */


					if( !$_FILES['bild']['tmp_name'] ) {
if(DEBUG)				echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Bildupload ist nicht aktiv. <i>(" . basename(__FILE__) . ")</i></p>\n";
													
					} else {
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Bildupload ist aktiv. <i>(" . basename(__FILE__) . ")</i></p>\n";
													
						$imageUploadReturnArray = imageUpload($_FILES['bild']);

						
/* if(DEBUG)				echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)				print_r($imageUploadReturnArray);					
if(DEBUG)				echo "</pre>";	 */						

						#********** VALIDATE IMAGE UPLOAD **********#
						if( $imageUploadReturnArray['imageError'] ) {
							// Fehlerfall
							$errorImageUpload = $imageUploadReturnArray['imageError'];
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Bildupload: <i>'$errorImageUpload'</i>! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							

						} else {
							// Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Bild wurde erfolgreich unter <i>'$imageUploadReturnArray[imagePath]'</i> gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";				
							$imagePath = $imageUploadReturnArray['imagePath'];
						} // VALIDATE IMAGE UPLOAD END

					}// IMAGE UPLOAD END 


					#***********************************#
					#********** DB OPERATIONS **********#
					#***********************************#

					#********** INSERT CONTENT INTO DB **********#
					if( !$errorImageUpload ) {
if(DEBUG)			echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Speichere Content in DB... <i>(" . basename(__FILE__) . ")</i></p>\n";

					$sql 	= 'INSERT INTO blogs
								(blog_headline, blog_imageAlignment, blog_content, cat_id, usr_id, blog_imagePath)
								VALUES
								(:ph_blog_headline, :ph_blog_imageAlignment, :ph_blog_content, :ph_cat_id, :ph_usr_id, :ph_blog_imagePath)';
					$params = array( 'ph_blog_headline' 		=> $headline,
									 'ph_blog_imageAlignment' 	=> $align,
									 'ph_blog_imagePath'		=> $imagePath,
									 'ph_blog_content' 			=> $content,
									 'ph_cat_id' 				=> $category,
									 'ph_usr_id' 				=> $_SESSION['usr_id'] );

					// Schritt 2 DB: SQL-Statement vorbereiten
					$statement = $pdo->prepare($sql);

					// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
					try {	
						$statement->execute($params);						
					} catch(PDOException $error) {
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
						$dbError = 'Fehler beim Zugriff auf die Datenbank!';
					}
			
					// Schritt 4 DB: Daten weiterverarbeiten

					// Bei schreibendem Vorgang: Schreiberfolg prüfen
					$rowCount = $statement->rowCount();
if(DEBUG)			echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";
												
					if( !$rowCount ) {
						// Fehlerfall
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Speichern des Contents! <i>(" . basename(__FILE__) . ")</i></p>\n";				
						$dbMessage = "<h3 class='error'>Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.</h3>";

					} else {
						// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Content war erfolgreich gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";				
						
						// Last Insert ID auslesen
						$newCatId = $pdo->lastInsertId();										
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Neues Content erfolgreich unter ID $newCatId gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";				
						$erfolgsMessage = "<p class='success'>Neues Content erfolgreich unter ID" . $newCatId . " gespeichert </p>";			
						if( $imagePath ) {
							$erfolgsBildMessage = '<p class="success">Bild wurde erfolgreich gespeichert.</p>';
						}
						
						$headline = NULL;
						$category = NULL;
						$align	  = NULL;
						$content = NULL;
/* if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$category: $category <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$headline: $headline <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$align: $align <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$content: $content <i>(" . basename(__FILE__) . ")</i></p>\n";
 */					}

					} //INSERT CONTENT INTO DB END
					
				}//FINAL FORM VALIDATION END	

			}//PROCESS FORM BLOGFORMULAR END

			 

#*****************************************************************************#
?>
<!doctype html>

<html>
	
	<head>	
		<meta charset="utf-8">
		<link rel="stylesheet" href="css/styles.css">
		<link rel="stylesheet" href="css/debug.css">
		<title>Dashboard</title>
	</head>
	
	<body>	
	<header class='dash_header'>
				
		<img class='main-logo' src="css/images/1.png" alt="logo">
		

	</header>	
	<div class="dasch_navigation">
		<a href="index.php?action=backIndex">Zurück zur Hauptseite</a> 
		<a href="?action=logout">Logout</a>
	</div>
	<h1>Wilkommen zum Dashboard</h1>	
	<h3>Aktiver Benutzer: <?php echo $row['usr_firstname'] ?> <?php echo $row['usr_lastname'] ?></h3>

	
	<?php echo $errorMessage ?><br>
	<?php if($errorImageUpload): ?>
		<span class="error"><?php echo $errorImageUpload ?></span><br>
	<?php endif ?>
	<?php echo $erfolgsBildMessage ?> <br>
	<?php echo $erfolgsMessage ?><br>

	<div class="dashboard_content">

		
		<!-- FORM BLOG-EINTRAG VERFASSEN START -->
		<form class="dashboard-input  einsatz" enctype="multipart/form-data" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
			<input type="hidden" name="newEntryForm">
			<select name="category" id="category">
				<option value="" disabled>Kategorie</option>
				<?php foreach( $rowCategory AS $array ) :?>
					<option value="<?php echo $array['cat_id'] ?>" <?php if( $array['cat_name'] == $category) echo 'selected'?>> <?php echo $array['cat_name'] ?> </option>
				<?php endforeach ?>
			</select>
			<input type="text" name="headline" id="headline" placeholder="Betreff" value="<?php echo $headline ?>">
			<fieldset>
				
				<input type="file" name="bild" id="bild" value="<?php echo $imageUploadReturnArray['imagePath'] ?>">
				<select name="align" id="align">
					<option value="1" <?php if($align == 1) echo 'selected' ?>>align left</option>
					<option value="2" <?php if($align == 2) echo 'selected' ?>>align right</option>
				</select>
			</fieldset>
			<textarea name="content" cols="30" rows="10" placeholder="Text..."><?php echo $content ?></textarea>
			<input class="button  dash_input" type="submit" value="Veröffentlichen">
		</form>
		<!-- FORM BLOG-EINTRAG VERFASSEN END -->

		<!-- FORM NEUE KATEGORY ANLEGEN START -->
		<form class="dashboard-input" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
			<input type="hidden" name="newCategorySend">
			<input type="text" name="newCategory" id="newCategory" placeholder="Name der Kategorie" value="<?php echo $newCategory ?>">
			<input class="button  dash_input" type="submit" value="Neue Kategorie anlegen">
		</form>
		<!-- FORM NEUE KATEGORY ANLEGEN END -->
	</div>

	<footer>
		&copy 20.08.2021 - <?php echo date("F j, Y")?> - Tetiana Panasevych
	</footer>
</body>
	
</html>