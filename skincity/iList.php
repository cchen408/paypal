<?php

	error_reporting(E_ALL);
	ini_set("display_errors", 1);	
	header("Content-Type: application/json");
	$iList = array();
	
class MyDB extends SQLite3
 {
      function __construct()
      {
         $this->open('skin.db');
      }
}

$db = new MyDB();
 
if(!$db){
      echo $db->lastErrorMsg();
   } else {
      //echo "Opened database successfully\n";
   }

	//pull me a list of ingredients
$sql2 =<<<EOF
		SELECT Name FROM Ingredients;
EOF;

	$stmt = $db->prepare($sql2);
	$res2 = $stmt->execute();

	while($row = $res2->fetchArray(SQLITE3_ASSOC)) {
		array_push($iList, $row['Name']);
	} 

   $db->close();
   
   echo json_encode($iList);
?>