<?php
#*****************************************************************************#
				
				#***********************************#
				#********** CONFIGURATION **********#
				#***********************************#
				
				require_once('./include/config.inc.php');
				require_once('./include/form.inc.php');
                require_once('./include/db.inc.php');

#*****************************************************************************#

				#******************************************#
				#********** INITIALIZE VARIABLES **********#
				#******************************************#

				$errorLogin = NULL;
				$flag		= true;
				$flagFilter = false;
				$action		= NULL;
				$id			= NULL;


#*****************************************************************************#

				#****************************************#
				#********** SECURE PAGE ACCESS **********#
				#****************************************#
				
				// Session fortführen
				session_name('blog');
				session_start();

/* if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)	print_r($_SESSION);					
if(DEBUG)	echo "</pre>";	 */

			// Prüfen, ob der aufrufende User eingeloggt ist
			if( !isset($_SESSION['usr_id']) ) {
				$flag = false;
			} 


#*****************************************************************************#

			

			#***********************************#
			#********** DB OPERATIONS **********#
			#***********************************#

			
			// Schritt 1 DB: DB-Verbindung herstellen
			$pdo = dbConnect('blog');
			
			#********** FETCH KATEGORIES FROM DB **********#
			$sql 		= 'SELECT * FROM categories';
if(DEBUG)	echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Versuche alle Kategorie auslesen <i>(" . basename(__FILE__) . ")</i></p>\n";
			
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
			$row = $statement->fetchAll(PDO::FETCH_ASSOC);
			$arrayCategory = array();

			foreach( $row AS $array) {
				$arrayCategory[$array['cat_id']] = $array['cat_name'];
			}
			
/* if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)	print_r($arrayCategory);					
if(DEBUG)	echo "</pre>"; */

#*****************************************************************************#

			#********************************************#
			#********** PROCESS URL PARAMETERS **********#
			#********************************************#


/* if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)	print_r($_GET);					
if(DEBUG)	echo "</pre>";	 */

				// Schritt 1 URL: Prüfen, ob URL-Parameter übergeben wurde
				if( isset($_GET['action']) ) {
if(DEBUG)		echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: URL-Parameter 'action' wurde übergeben. <i>(" . basename(__FILE__) . ")</i></p>\n";										
				
				// Schritt 2 URL: Werte auslesen, entschärfen, DEBUG-Ausgabe
				$action = cleanString( $_GET['action'] );
if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$action: $action <i>(" . basename(__FILE__) . ")</i></p>\n";
									
				// Schritt 3 URL: Verzweigen

				//ZURÜCK AUF HAUPTSEITE
				if( $action == 'backIndex' ) {
if(DEBUG)			echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Prüfe, ob der aufrufende User eingeloggt ist... <i>(" . basename(__FILE__) . ")</i></p>\n";				

					// Prüfen, ob der aufrufende User eingeloggt ist
					if( !isset($_SESSION['usr_id']) ) {
						//Fehlerfall
if(DEBUG)			echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: der aufrufende User ist nicht eingeloggt ... <i>(" . basename(__FILE__) . ")</i></p>\n";				

						$flag = false;
					} else {
						//Ergolgsfall
if(DEBUG)			echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Generiere neuen Header für eingeloggeten User... <i>(" . basename(__FILE__) . ")</i></p>\n";				

						$flag = true;
					}


				//LOGOUT
				}elseif( $action == 'logout') {
					logout();
					$flag = false;

				// KATEGORIEN FILTERN	
				}elseif( $action == 'showCategory')	{
if(DEBUG)			echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Ich muss Kategorien filtern... <i>(" . basename(__FILE__) . ")</i></p>\n";
					$id = $_GET['id'];
if(DEBUG)			echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Ich muss Kategorien nach  Kategorie $arrayCategory[$id] filtern... <i>(" . basename(__FILE__) . ")</i></p>\n";


					#***********************************#
					#********** DB OPERATIONS **********#
					#***********************************#

					#********** FILTERN KATEGORIEN FROM DB **********#
					$sql 		= 'SELECT * FROM blogs LEFT JOIN users USING(usr_id)
									WHERE cat_id = :ph_cat_id ORDER BY blog_date DESC';
					$params		= array( 'ph_cat_id' => $id);

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
					$arrayContent = $statement->fetchAll(PDO::FETCH_ASSOC);
					
/* if(DEBUG)			echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)			print_r($newArray);					
if(DEBUG)			echo "</pre>"; */

					$flagFilter = true;

					
				} //FILTERN KATEGORIEN FROM DB END					
			
			} //PROCESS URL PARAMETERS END



#*****************************************************************************#

			#***********************************#
			#********** DB OPERATIONS **********#
			#***********************************#

			#********** FETCH CONTENT FROM DB **********#
			if( !$flagFilter ){
				$sql 		= 'SELECT * FROM blogs LEFT JOIN users USING(usr_id) ORDER BY blog_date DESC';
if(DEBUG)		echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Versuche alle Einträge auslesen <i>(" . basename(__FILE__) . ")</i></p>\n";
			
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
				$arrayContent = $statement->fetchAll(PDO::FETCH_ASSOC);}
			
/* if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)	print_r($arrayContent);					
if(DEBUG)	echo "</pre>"; */

#*****************************************************************************#


				#****************************************#
				#********** PROCESS FORM LOGIN **********#
				#****************************************#

/* if(DEBUG)	echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)	print_r($_POST);					
if(DEBUG)	echo "</pre>"; */

				// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
				if( isset($_POST['formLogin']) ) {
if(DEBUG)		echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Formular 'Login' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";										

				// Schritt 2 FORM: Werte auslesen, entschärfen, DEBUG-Ausgabe
				$email = cleanString($_POST['email']);
				$password = cleanString($_POST['password']);

if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$email: $email <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: \$password: $password <i>(" . basename(__FILE__) . ")</i></p>\n";

				// Schritt 3 FORM: Feldvalidierung
				$errorEmail = checkEmail($email);
				$errorPassword = checkInputString($password, 4);

				#********** FINAL FORM VALIDATION **********#
				if( $errorEmail OR $errorPassword) {
					// Fehlerfall
if(DEBUG)			echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";				
					$errorLogin = '<p class="error">Die Logindaten sind ungültig!</p>';
				} else {
					// Erfolgsfall
if(DEBUG)			echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular ist fehlerfrei und wird nun verarbeitet... <i>(" . basename(__FILE__) . ")</i></p>\n";				
						
					// Schritt 4 FORM: Daten weiterverarbeiten

					#***********************************#
					#********** DB OPERATIONS **********#
					#***********************************#
					
					/* // Schritt 1 DB: DB-Verbindung herstellen
					$pdo = dbConnect('blog'); */

					#********** FETCH DATA FROM DB **********#
					$sql 		= 'SELECT usr_id, usr_email, usr_password FROM users WHERE usr_email = :ph_usr_email';
					$params 	= array( 'ph_usr_email' => $email );

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
					
/* if(DEBUG)			echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)			print_r($row);					
if(DEBUG)			echo "</pre>"; */

					#********** 1. VERIFY USERS EMAIL **********#
					if( !$row ) {
						// Fehlerfall
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Email '$email' existiert nicht in der DB! <i>(" . basename(__FILE__) . ")</i></p>\n";				
						$errorLogin = '<p class="error">Die Logindaten sind ungültig!</p>';
						
					} else {
						// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Email '$email' wurde in der DB gefunden. <i>(" . basename(__FILE__) . ")</i></p>\n";				
					
						
						#********** 2. VERIFY PASSWORD **********#
						if( !password_verify( $password, $row['usr_password'] ) ) {
							// Fehlerfall
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Passwort aus dem Formular stimmt nicht mit dem Passwort aus der DB überein! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							$errorLogin = '<p class="error">Die Logindaten sind ungültig!</p>';
	
							} else {
								// Erfolgsfall
if(DEBUG)						echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Passwort aus dem Formular stimmt mit dem Passwort aus der DB überein. <i>(" . basename(__FILE__) . ")</i></p>\n";				
if(DEBUG)						echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Login wird durchgeführt... <i>(" . basename(__FILE__) . ")</i></p>\n";
	
								#********** START SESSION **********#
								session_name('blog');
								session_start();

								$_SESSION['usr_id'] = $row['usr_id'];

/* if(DEBUG)						echo "<pre class='debug'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)						print_r($_SESSION);					
if(DEBUG)						echo "</pre>"; */

								#********** REDIRECT TO profile.php **********#
								header('Location: dashboard.php');


							} //VERIFY PASSWORD END
						} // VERIFY USERS EMAIL END
					} //FINAL FORM VALIDATION END
				} //PROCESS FORM LOGIN END




#*****************************************************************************#

			
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="css/styles.css">
	<link rel="stylesheet" href="css/debug.css">
		
		<title>blog_Tetiana_Panasevych</title>
</head>
<body>
	<header>
		<div>
			<img class='main-logo' src="css/images/1.png" alt="logo">
			<h1>PHP-Projekt BLOG</h1>
		</div>
		<?php if( $flag) : ?>
			<div class="dasch_navigation">
				<a href="dashboard.php">Zum Dashboard >></a>
				<a href="?action=logout">Logout</a>
			</div>	
		<?php else : ?>
			<!-- LOGIN FORM START -->
			<form class="main-input__login" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
				<div class="form_wrapper">
					<input type="hidden" name="formLogin">
					<input class="main-email" type="text" name="email" id="email" placeholder="Email">
					<input class="main-password" type="text" name="password" id="password" placeholder="Password">
					<button class="button" type="submit">Login</button>
				</div>
				<?php echo $errorLogin ?>
			</form>
			<!-- LOGIN FORM END -->
		<?php endif ?>
	</header>
	
	<a href="index.php">Alle Einträge einzeigen</a>
	
	<div class="mainContent">

		<!-- FORM CONTENT START -->
		<div class="mainContent-articles">
		
		<?php foreach( $arrayContent AS $array ) : ?>
			<h4 class="mainContent-category">Kategorie: <?php echo $arrayCategory[$array['cat_id']] ?></h4>
			<h2 class="mainContent-headline"><?php echo $array['blog_headline'] ?></h2>
			<p class="mainContent-autor"><?php echo $array['usr_firstname']?> <?php echo $array['usr_lastname']?> (<?php echo $array['usr_city']?>) schrieb am <?php echo substr($array['blog_date'], 8, 2)?>.<?php echo substr($array['blog_date'], 5, 2) ?>.<?php echo substr($array['blog_date'], 0, 4)?> um <?php echo substr($array['blog_date'], 11, 8) ?>:</p>

			<?php if($array['blog_imagePath']): ?>
				<img class="<?php if($array['blog_imageAlignment'] == 2) echo "right" ?>" src="<?php echo $array['blog_imagePath'] ?>" alt="bild">
			<?php endif ?>
			<p class="mainContent-articles__content"> <?php echo nl2br($array['blog_content'])?> </p>	
		<hr>
		<?php endforeach ?>
		</div>
		<!-- FORM CONTENT END -->


		<!-- FORM KATEGORIE START -->
		<div class="category">
			<?php foreach( $arrayCategory AS $key => $value) : ?>
				<a <?php if( $id == $key) echo 'class="active"'?> href="?action=showCategory&id=<?php echo $key ?>"> <?php echo $value ?></a>
			<?php endforeach ?>
		</div>
		<!-- FORM KATEGORIE END -->

	</div>

	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	
	<footer>
		<p>&copy 20.08.2021 - <?php echo date("F j, Y")?> - Tetiana Panasevych</p>
	</footer>	

	</body>
	
</html>