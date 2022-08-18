<?php

// Escapes a string to render it safe for SQL.
// Assumes your database connection is assigned to $mydb.
// Modify this if you use something else ($db, $sqli, $mysql, etc.).
function sql_prep($string) {
	global $mydb;
	if($mydb) {
		return mysqli_real_escape_string($mydb, $string);
	} else {
		// addslashes is almost the same, but not quite as secure.
		// Fallback only when there is no database connection available.
	 	return addslashes($string);
	}
}

// Usage:
// $sql_safe_username = sql_prep($_POST['username']);

?>
