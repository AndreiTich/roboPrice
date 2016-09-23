<html>
  <head>
    <meta charset="utf-8">
    <title>Hello Shopify!</title>
    <link rel="stylesheet" href="css/foundation.css">
    <link rel="stylesheet" href="css/app.css">
  </head>
  <body>
    <div class="row">
      <div class="large-12 columns">
	<center><h3>Store Product Summation Tool</h3></center> 
      </div>
    </div>
    <div class="row">
      <div class="large-8 medium-8 large-offset-2 columns">
<?php
#minor thing to make UX nicer
$user_agent = getenv("HTTP_USER_AGENT");
if(strpos($user_agent, "Win") !== FALSE){
	echo "<h5>Ctrl-click catagories to sum them<small> Default selection made, just click submit ;)</small></h5>";
}
elseif(strpos($user_agent, "Mac") !== FALSE){
	echo "<h5>&#8984;-click catagories to sum them<small> Default selection made, just click submit ;)</small></h5>";
}
?>
<?php
function getPageResults($page){
	$url = "http://shopicruit.myshopify.com/products.json?page=".(string)$page;
	$response = file_get_contents($url);
	$response = json_decode($response, true);
	return $response;
}

function countProducts($json_data){
	$output = count($json_data['products']);
	return $output;
}

function mergeAllProducts(){
	$page_index = 1;
	$all_products = [];
	while(true){
		$results = getPageResults($page_index);
		if(countProducts($results) == 0){
			#echo "success! Found ".($page_index-1)." pages of products!";
			break;
		};
		$count = countProducts($results);
		$page_price = sumPrices($results);
		#echo "The total products on page $page_index is $count </br>";
		#echo "<blockquote>The total price on this page is $page_index is $page_price</blockquote> </br>";
		$all_products = array_merge_recursive($all_products, $results);
		$page_index++;
	}
	return $all_products;
}

function sumPrices($products){
	$total_price = 0;
	foreach($products['products'] as $key => $product){
		foreach($product['variants'] as $key => $variant){
			$total_price = $total_price + (float)$variant['price'];
		}
	}
	return $total_price;
}

function filterItemsByType($all_products, $array_of_types){
	$filtered_array["products"] = [];
	foreach($all_products['products'] as $key => $product){
		if(in_array($product['product_type'], $array_of_types)){
			#echo "FOUND A CLOCK CALLED: ".$product['title']."</br>";
			array_push($filtered_array["products"], $product);
		}
	}
	return $filtered_array;
}
function getAllProductTypes($all_products){
	$product_types = [];
	foreach($all_products['products'] as $key => $product){
		$product_types[$product['product_type']] = 1;
	}
	return $product_types;
}
function echo_product_list($product_list){
	echo "<table role=\"grid\">
		  <thead>
		    <tr>
		      <th>Product Title</th>
		      <th>Product variant</th>
		      <th>Product price</th>
		    </tr>
		  </thead>
		  <tbody>";
	foreach($product_list['products'] as $key => $product){
		foreach($product['variants'] as $key2 => $variant){
			echo "<tr>";
			echo "<th>".$product['title']."</th>";
			echo "<th>".$variant['title']."</th>";
			echo "<th>".$variant['price']."</th>";
			echo "</tr>";
		}
	}
	echo "</tbody></table>";
}
$all = mergeAllProducts();
$type_array = [];
echo '<form action="roboPrice.php"> 
	<select multiple="multiple" name="types[]">';
# I just did this for visibility
foreach(getAllProductTypes($all) as $key => $value){
	if($key == "Clock" | $key == "Watch"){
		array_unshift($type_array, $key);
	} else {
		array_push($type_array, $key);
	}
} 
foreach($type_array as $type){
	if($type == "Clock" | $type == "Watch"){
		echo "<option selected>".$type."</option>";
	} else {
		echo "<option>".$type."</option>";
	}
}
echo '</select>
<input type="submit">
</form>';

if(isset($_GET["types"])){
	$type_array = [];
	foreach($_GET["types"] as $type){
		array_push($type_array, $type);
	}
	$filtered_products = filterItemsByType($all, $type_array);
	echo "<h4>Found ". countProducts($filtered_products). " products in that search </h4></br>";
	echo "<h4>&#x1F4B8;&#x1F4B8;&#x1F4B8;They cost a total of ". sumPrices($filtered_products). " dollars!&#x1F4B8;&#x1F4B8;&#x1F4B8;</h4></br>";
	echo "<h3>Here is the product list!</h3>";
	echo_product_list($filtered_products);
	
	
};
?>
      </div> 
    </div>


  </body>
</html>
