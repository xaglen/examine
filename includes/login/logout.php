<?php

session_start();
unset($_SESSION['validUser']);	// remove user's access to session
session_destroy();				// end session
header("Location: ..");			// send the user to the website homepage

?>