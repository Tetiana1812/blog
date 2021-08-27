<?php
#*****************************************************************************#


				function errorLog($logMessage, $file) {
if(DEBUG)		echo "<p class='debug'><b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "() <i>(" . basename(__FILE__) . ")</i></p>\n";	
					
				
					// Vorbereiten des Logeintrags
					$errorMessage  = "\t\t\t<tr><td>" . date('Y-m-d - H:i:s') . "</td>";
					$errorMessage .= "<td><i>'$file'</i></td>";
					$errorMessage .= "<td><b>$logMessage</b></td></tr>\n";
					
					// Logeintrag in Datei schreiben (anhängen)
					if( !@file_put_contents('logfiles/errorLog.html', $errorMessage, FILE_APPEND) ) {
						// Fehlerfall
if(DEBUG)			echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Schreiben in Datei! <i>(" . basename(__FILE__) . ")</i></p>\n";				
					
					} else {
						// Erfolgsfall
if(DEBUG)			echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Erfolgreich in Datei geschrieben. <i>(" . basename(__FILE__) . ")</i></p>\n";				
					}
				}
				
				
#*****************************************************************************#
?>