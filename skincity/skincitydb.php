<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
header("Content-Type: application/json");
$data = array();
$iList = array();
$includeFilter = $_REQUEST['include'];
$excludeFilter = $_REQUEST['exclude'];
/*
for ($i=0; $i<100; $i++) {
  $num = rand();
  if ($type == 'evens' && $num % 2 == 0 || $type == 'odds' && $num %2 == 1)
    array_push($data, $num);
}
*/



class Product {
            // Creating some properties (variables tied to an object
            public $name;
            public $brand;
            public $url;
            public $ingredients;
            public $type;
            
            // Assigning the values
            public function __construct($name, $brand, $type, $ingredients, $url) {
              $this->ingredients = $ingredients;
              $this->name = $name;
              $this->brand = $brand;
              $this->url = $url;
              $this->type = $type;
            }
 }

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
   
/*   
$sql =<<<EOF
      SELECT* from Product;
EOF;

   $ret = $db->query($sql);
   while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
      $p = new Product($row['ProductID'], $row['Name'], $row['Brand'] , $row['URL'], $row['Type']);
      array_push($data, $p);
   }
*/

//pull me the stuff i need to fill database
$sql =<<<EOF
	SELECT Product.Name, Product.Brand, Product.Type, GROUP_CONCAT(Ingredients.Name, ", ") AS AllIngredients, Product.URL
	FROM Product
        LEFT JOIN ProductIngredients ON Product.ProductID = ProductIngredients.ProductID
        JOIN Ingredients ON ProductIngredients.IngredientsID = Ingredients.IngredientsID
	GROUP BY Product.Name, Product.Brand, Product.Type, Product.URL
	HAVING AllIngredients LIKE '%' || :include || '%' AND AllIngredients NOT LIKE '%' || :exclude || '%';
EOF;
   //echo "Operation done successfully\n";

$statement = $db->prepare($sql);
if(empty($excludeFilter))
  $excludeFilter="secrettolifetheuniverseandeverything";
$statement->bindValue(':include', $includeFilter, SQLITE3_TEXT);
$statement->bindValue(':exclude', $excludeFilter, SQLITE3_TEXT);
$results = $statement->execute();

while($row = $results->fetchArray(SQLITE3_ASSOC)) {
	$p = new Product($row['Name'], $row['Brand'], $row['Type'], $row['AllIngredients'], $row['URL']);
	array_push($data, $p);
}   
   

   $db->close();
   
   echo json_encode($data);
?>