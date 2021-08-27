<?php
#*****************************************************************************#

				
				/**
				*
				*	Entschärft und säubert einen String, falls er einen Wert besitzt
				*	Falls der String ein Leerstring ist, wird NULL zurückgegeben
				*
				*	@param 	String 	$value 	- Der zu entschärfende und zu bereinigende String
				*
				*	@return 	String|NULL 		- Der entschärfte und bereinigte String oder NULL
				*
				*/
				function cleanString($value) {
if(DEBUG_F)		echo "<p class='debugCleanString'><b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$value') <i>(" . basename(__FILE__) . ")</i></p>\n";	
									
					// SICHERHEIT: Damit so etwas nicht passiert: ?action=<script>alert("HACK!")</script>
					// muss der empfangene String ZWINGEND entschärft werden!
					// htmlspecialchars() wandelt potentiell gefährliche Steuerzeichen wie
					// < > "" & in HTML-Code um (&lt; &gt; &quot; &amp;)
					// Der Parameter ENT_QUOTES wandelt zusätzlich einfache '' in &apos; um
					// Der Parameter ENT_HTML5 sorgt dafür, dass der generierte HTML-Code HTML5-konform ist
					// Der optionale Parameter 'false' steuert, dass bereits vorhandene HTL-Entities nicht
					// noch einmal codiert werden (&auml; => &amp;auml;)
					$value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
					
					// trim() entfernt vor und nach einem String sämtliche Whitespaces (Leerzeichen, Tabs, Zeilenumbrüche)
					$value = trim($value);
					
					// Damit cleanString() nicht NULL-Werte in Leerstings verändert, wird 
					// ein eventueller Leerstring in $value mit NULL überschrieben 
					if( $value === '' ) {
						$value = NULL;
					}
					
					return $value;
				}


#*****************************************************************************#


				/**
				*
				*	Prüft einen String auf Leerstring, Mindest- und Maxmimallänge
				*
				*	@param 	String 		$value 									- Der zu prüfende String
				*	@param 	Integer 		$minLength=INPUT_MIN_LENGTH	 	- Die erforderliche Mindestlänge
				*	@param 	Integer 		$maxLength=INPUT_MAX_LENGTH 		- Die erlaubte Maximallänge
				*
				*	@return 	String|NULL 											- Fehlermeldung oder NULL
				*	
				*/
				function checkInputString($value, $minLength=INPUT_MIN_LENGTH, $maxLength=INPUT_MAX_LENGTH) {
if(DEBUG_F)		echo "<p class='debugCheckInputString'><b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$value' [min:$minLength | max:$maxLength]) <i>(" . basename(__FILE__) . ")</i></p>\n";	
					
					// von PHP als false interpretiert werden: '', '0', NULL, 0, 0.0, false
					/*
						WICHTIG: Die Prüfung auf Leerfeld muss zwingend den Datentyp Sting mitprüfen,
						da ansonsten bei einer Eingabe '0' (z.B. Anzahl der im Haushalt lebenden Kinder: 0)
						die '0' als false und somit als leeres Feld gewertet wird!
					*/
					
					// Prüfen auf Leerstring
					if( $value === '' ) {
						$errorMessage = 'Dies ist ein Pflichtfeld!';
					
					// Prüfen auf Mindestlänge
					} elseif( mb_strlen($value) < $minLength ) {
						$errorMessage = "Muss mindestens $minLength Zeichen lang sein!";
					
					// Prüfen auf Maximallänge
					} elseif( mb_strlen($value) > $maxLength ) {
						$errorMessage = "Darf maximal $maxLength Zeichen lang sein!";
						
					} else {
						$errorMessage = NULL;
					}
					
					return $errorMessage;
				}


#*****************************************************************************#


				/**
				*
				*	Prüft eine Email-Adresse auf Leerstring und Validität
				*
				*	@param 	String 	$value 			- Die zu prüfende Email-Adresse
				*
				*	@return 	String|NULL 				- Fehlermeldung oder NULL
				*
				*/
				function checkEmail($value) {
if(DEBUG_F)		echo "<p class='debugCheckEmail'><b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$value') <i>(" . basename(__FILE__) . ")</i></p>\n";	
					
					// von PHP als false interpretiert werden: '', '0', NULL, 0, 0.0, false
					/*
						WICHTIG: Die Prüfung auf Leerfeld muss zwingend den Datentyp Sting mitprüfen,
						da ansonsten bei einer Eingabe '0' (z.B. Anzahl der im Haushalt lebenden Kinder: 0)
						die '0' als false und somit als leeres Feld gewertet wird!
					*/
					
					// Prüfen auf Leerstring
					if( $value === '' ) {
						$errorMessage = 'Dies ist ein Pflichtfeld!';
					
					// Prüfen auf Validität
					} elseif( !filter_var($value, FILTER_VALIDATE_EMAIL) ) {
						$errorMessage = 'Dies ist keine gültige Email-Adresse!';
						
					} else {
						$errorMessage = NULL;
					}
					
					return $errorMessage;

				}


#*****************************************************************************#

				
				/**
				*
				*	Prüft ein hochgeladenes Bild auf MIME-Type, Datei- und Bildgröße
				*	Bereinigt den Dateinamen URL-konform und wandelt ihn in Kleinbuchstaben um
				*	Speichert das erfolgreich geprüfte Bild unter dem bereinigten Dateinamen mit einem zufällig generierten Präfix
				*
				*	@param 	Array 	$uploadedImage														- Das in $_FILES enthaltene Array mit den Informationen zum hochgeladenen Bild
				*	@param 	Int 		$imageMaxWidth				=	IMAGE_MAX_WIDTH				- Die maximal erlaubte Bildbreite in PX	
				*	@param 	Int 		$imageMaxHeight			=	IMAGE_MAX_HEIGHT				- Die maximal erlaubte Bildhöhe in PX						
				*	@param 	Int 		$imageMaxSize				=	IMAGE_MAX_SIZE					- Die maximal erlaubte Dateigröße in Bytes
				*	@param 	String 	$imageUploadPath			=	IMAGE_UPLOAD_PATH				- Das Speicherverzeichnis auf dem Server
				*	@param 	Array 	$imageAllowedMimeTypes	=	IMAGE_ALLOWED_MIME_TYPES	- Whitelist der erlaubten MIME-Types
				*
				*	@return 	Array { 	'imagePath'	=>String/NULL										- Der Speicherpfad auf dem Server im Erfolgsfall }
				*							'imageError'=>String/NULL 										- Fehlermeldung im Fehlerfall
				*
				*/
				function imageUpload(
											$uploadedImage,
											$imageMaxWidth 			= IMAGE_MAX_WIDTH,
											$imageMaxHeight 			= IMAGE_MAX_HEIGHT,
											$imageMaxSize 				= IMAGE_MAX_SIZE,
											$imageUploadPath 			= IMAGE_UPLOAD_PATH,
											$imageAllowedMimeTypes 	= IMAGE_ALLOWED_MIME_TYPES
											) {
if(DEBUG_F)		echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "() <i>(" . basename(__FILE__) . ")</i></p>\n";	
					
					/*
						Das Array $_FILES['avatar'] bzw. $uploadedImage enthält:
						Den Dateinamen [name]
						Den generierten (also ungeprüften) MIME-Type [type]
						Den temporären Pfad auf dem Server [tmp_name]
						Die Dateigröße in Bytes [size]
					*/
/*					
if(DEBUG_F)		echo "<pre class='debugImageUpload'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_F)		print_r($uploadedImage);					
if(DEBUG_F)		echo "</pre>";					
*/				

					#********** GATHER IMAGE INFORMATION **********#
					// file size in bytes
					$fileSize = $uploadedImage['size'];
					// temp path on server
					$fileTemp = $uploadedImage['tmp_name'];
					// file name
					$fileName = cleanString($uploadedImage['name']);
if(DEBUG_F)		echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: \$fileName: $fileName <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)		echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: \$fileTemp: $fileTemp <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)		echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: \$fileSize: " . round($fileSize/1024, 2) . "kB <i>(" . basename(__FILE__) . ")</i></p>\n";

					
					#********** GATHER MORE INFORMATION FOR IMAGE FILE **********#
					/*
						Die Funktion getimagesize() liefert bei gültigen Bildern ein Array zurück:
						Die Bildbreite in PX [0]
						Die Bildhöhe in PX [1]
						Einen für die HTML-Ausgabe vorbereiteten String für das IMG-Tag
						(width="480" height="532") [3]
						Die Anzahl der Bits pro Kanal ['bits']
						Die Anzahl der Farbkanäle (somit auch das Farbmodell: RGB=3, CMYK=4) ['channels']
						Den echten(!) MIME-Type ['mime']
					*/
					$imageDataArray = @getimagesize($fileTemp);
/*
if(DEBUG_F)		echo "<pre class='debugImageUpload'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_F)		print_r($imageDataArray);					
if(DEBUG_F)		echo "</pre>";
*/
					$imageWidth 	= @$imageDataArray[0];
					$imageHeight 	= @$imageDataArray[1];
					$imageMimeType = @$imageDataArray['mime'];
if(DEBUG_F)		echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: \$imageWidth: $imageWidth px <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)		echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: \$imageHeight: $imageHeight px <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)		echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: \$imageMimeType: $imageMimeType <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					// Whitelist mit erlaubten MIME-Types
					// $allowedMimeTypes = array('image/jpg', 'image/jpeg', 'image/gif', 'image/png');
					

					#********** VALIDATE IMAGE **********#
					// MIME-Type prüfen
					if( !in_array($imageMimeType, $imageAllowedMimeTypes) ) {
						$errorMessage = "Dies ist kein erlaubter Bildtyp!";
						
					// maximal erlaubte Bildbreite in Pixeln	
					} elseif( $imageWidth > $imageMaxWidth ) {
						$errorMessage = "Die Bildbreite darf $imageMaxWidth px nicht überschreiten!";
												
					// maximal erlaubte Bildbhöhe in Pixeln	
					} elseif( $imageHeight > $imageMaxHeight ) {
						$errorMessage = "Die Bildhöhe darf $imageMaxHeight px nicht überschreiten!";
												
					// maximal erlaubte Dateigröße in Bytes	
					} elseif( $fileSize > $imageMaxSize ) {
						$errorMessage = "Die Dateigröße darf $imageMaxSize kB nicht überschreiten!";
												
					} else {
						$errorMessage = NULL;
					}
					#*************************************#
					
										
					#********** FINAL IMAGE VALIDATION **********#
					if( $errorMessage ) {
						// Fehlerfall
if(DEBUG_F)			echo "<p class='debugImageUpload err'><b>Line " . __LINE__ . "</b>: $errorMessage <i>(" . basename(__FILE__) . ")</i></p>\n";				
						$fileTarget = NULL;
						
					} else {
						// Erfolgsfall
if(DEBUG_F)			echo "<p class='debugImageUpload ok'><b>Line " . __LINE__ . "</b>: Die Bildprüfung ergab keine Fehler. <i>(" . basename(__FILE__) . ")</i></p>\n";				
						
						
						#********** MAKE FILE NAME A VALID URL **********#
						// $fileName = "?mein blöÖd.er Avatar's.2.jpg";
						
						// ggf. vorhandene Leerzeichen durch _ ersetzen
						$fileName = str_replace(' ', '_', $fileName);
						// Dateinamen in Kleinbuchstaben umwandeln
						$fileName = mb_strtolower($fileName);
						// Umlaute ersetzen
						$fileName = str_replace( array('ä','ö','ü','ß'), array('ae','oe','ue','ss'), $fileName );
						
						// Dateinamen von zusätzlichen . bereinigen
						// Erlaubt sein soll nur der letzte Punkt vor der Dateiendung
						
						// Position des letzten . im String ermitteln
						$startPositionFileExtension = strrpos($fileName, '.');
// if(DEBUG_F)		echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: \$startPositionFileExtension: $startPositionFileExtension <i>(" . basename(__FILE__) . ")</i></p>\n";
					
						// Dateiendung ausschneiden und zwischenspeichern
						$fileExtension = substr($fileName, $startPositionFileExtension);
// if(DEBUG_F)		echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: \$fileExtension: $fileExtension <i>(" . basename(__FILE__) . ")</i></p>\n";
					
						// Dateiendung vom ursprünglichen Dateinamen abschneiden
						$fileName = str_replace($fileExtension, '', $fileName);
						
						// Nicht erlaubte Zeichen aus ursprünglichem Dateinamen löschen
						// $fileNamePrefix = str_replace( array("'","#","?","!","&",'"',"@",",","|","~","*","´","`","°","[","]","{","}","/","²","§","³","$","%","^","<",">","(",")",",",";",":","+",".","/"), "", $fileName );
						// Bessere Variante: Mittels regulärem Ausdruck (regEx)
						$fileName = preg_replace('/[^a-z0-9_\-]/', '', $fileName);
						
						// Dateiendung wieder an den ursprünglichen Dateinamen anhängen
						// $fileName .= $fileExtension;
						$fileName = $fileName . $fileExtension;
						

						#********** MAKE FILE NAME UNIQUE **********#
						$randomPrefix = rand(1,999999) . str_shuffle('abcdefghijklmnopqrstuvwxyz') . time();
						
						
						#********** CREATE FILE TARGET **********#
						$fileTarget = $imageUploadPath . $randomPrefix . '_' . $fileName;
// if(DEBUG_F)			echo "<p class='debugImageUpload'><b>Line " . __LINE__ . "</b>: \$fileTarget: $fileTarget <i>(" . basename(__FILE__) . ")</i></p>\n";
						#******************************************************#
						
												
						#********** MOVE IMAGE TO FINAL DASTINATION **********#
						if( !@move_uploaded_file($fileTemp, $fileTarget) ) {
							// Fehlerfall
if(DEBUG_F)				echo "<p class='debugImageUpload err'><b>Line " . __LINE__ . "</b>: FEHLER beim Verschieben der Datei nach <i>'$fileTarget'</i> <i>(" . basename(__FILE__) . ")</i></p>\n";				
							$errorMessage = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
							errorLog("FEHLER beim Verschieben der Datei nach <i>'$fileTarget'</i>!", basename(__FILE__));
							
							// Wenn $errorMessage vorhanden, Uploadpfad löschen
							$fileTarget = NULL;
							
						} else {
							// Erfolgsfall
if(DEBUG_F)				echo "<p class='debugImageUpload ok'><b>Line " . __LINE__ . "</b>: Datei erfolgreich nach <i>'$fileTarget'</i> verschoben. <i>(" . basename(__FILE__) . ")</i></p>\n";				
							
						} // MOVE IMAGE TO FINAL DASTINATION END
						
					} // FINAL IMAGE VALIDATION END


					#********** RETURN NEW IMAGE PATH OR ERROR MESSAGE **********#
					return array('imagePath' => $fileTarget, 'imageError' => $errorMessage);
				}


#*****************************************************************************#

				#****************************#
				#********** LOGOUT **********#
				#****************************#

				/* Die Funktion macht Logout und leitet auf Hauptseite um */
				
				function logout() {
if(DEBUG_F)			echo "<p class='debug'><b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "() <i>(" . basename(__FILE__) . ")</i></p>\n";	
										
					// Session löschen
					session_destroy();
					
					// Umleitung auf index.php
					header('Location: index.php');
					exit;
				}

#*****************************************************************************#
?>