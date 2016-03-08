<!DOCTYPE html>
<htmL>
<head>
    <title>Inventory List</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.min.js"></script>

	<?php
	// start the session
	session_start();
	// make sure the user is logged in (if the session variable "manager" is set or not)
	if (!isset($_SESSION["manager"])) {
		//otherwise send to the login page
		header("location: admin_login.php");
		// prevent resof of the page from running if user is not logged in
		exit();
	}
	// get value from session variables and assign them to to local variables
	$managerID = preg_replace('#[^0-9]#i', '', $_SESSION["id"]); // filter non-numbers for security preg_replace(RegEx, substitute , data )
	$manager = preg_replace('#[^A-Za-z0-9]#i', '', $_SESSION["manager"]); // filter non-numbers and non-letters for security
	$password = preg_replace('#[^A-Za-z0-9]#i', '', $_SESSION["password"]);
	// connect to MySQL Database
	include "../connect_to_mysql_pdo.php";
	//$res= $dbh->prepare("SELECT * FROM admin WHERE id='$managerID' AND username='$manager' AND password='$password' LIMIT 1");
	$res= $dbh->prepare("SELECT * FROM admin WHERE id=? AND username=? AND password=? LIMIT 1");
	$res->execute([$managerID,$manager,$password]);
	$existCount = $res->rowCount();  //count the number of rows, same as mysql mysql_num_rows($sqlquery);
	if($existCount==0){
		echo "Your login session data is not on record in the database. Please log in again here:<a href='admin_login.php'>Admin Login</a><br>"; //echo this if someone tries to forge session cookies
		exit(); // prevent resof of the page from running if user is not logged in
	}

	/*-------------------- DELETE ITEMS-------------------*/

	if(isset($_GET["pid"])){
		$targetID = $_GET["pid"];
		$product_list = "";
		$res = $dbh->prepare("SELECT * FROM products WHERE id='$targetID' LIMIT 1");
		$res->execute();
		$productCount = $res->rowCount();
		if ($productCount > 0) {
			while ($row = $res->fetch()) {
			    $id = $row['id'];
	    		$product_name = $row['product_name'];
	    		$price = $row['price'];
	    		$category = $row['category'];
 				$subcategory = $row['subcategory'];
 				$details = $row['details'];
	    		$date_added = strftime("%b %d, %Y", strtotime($row['date_added']));
			}
		}
		else {
			$product_list = "Item with ID $targetID does not exist!";
			exit();
		}
	}


	/*-------------------- GET FORM DATA ---------------*/
	if(isset($_POST['fr_itemname']) && isset($_POST["fr_price"])) {
 		$fr_name = $_POST['fr_itemname'];
 		$fr_price = $_POST['fr_price'];
 		$fr_category = $_POST['fr_category'];
 		$fr_subcategory = $_POST['fr_subcategory'];
 		$fr_details = $_POST['fr_details'];

 		$res= $dbh->prepare("SELECT id FROM products WHERE product_name=? LIMIT 1");
		$res->execute([$fr_name]);
		$productCount = $res->rowCount();  //count the number of rows, same as mysql mysql_num_rows($sqlquery);
		if($productCount > 0){
			echo 'Sorry, but an item with the name "$fr_name" already exists. Click here to reload page and try again: <a href"inventory_list.php">Refresh Page</a>'; //echo this if someone tries to forge session cookies
			exit(); // prevent resof of the page from running if user is not logged in
		}
		$res= $dbh->prepare("INSERT INTO products (product_name, price, details, category, subcategory, date_added) VALUES(?, ?, ?, ?, ?, now())");
		$res->execute([$fr_name, $fr_price, $fr_details, $fr_category, $fr_subcategory]);
		$created_id = $dbh->lastInsertId();
		$newname = "$created_id.jpg";
		move_uploaded_file($_FILES['fr_image']['tmp_name'], "../inventory_images/".$newname);
		// prevent form resubmission on reload
		header("location: inventory_list.php");
		exit();

	}
	?>
	<style type="text/css">
		.input-group-addon {
			min-width: 125px;
			text-align: right;
		}

	</style>
</head>
<body>
<?php include_once("../header.php"); ?>
<div class="container">
	<div class="row">
		<div class="col-xs-1 col-md-2">
		</div>
		<div class="col-xs-10 col-md-8">
			<a href="#">+Add Inventory Items</a>
		</div>
		<div class="col-xs-1 col-md-2">
		</div>
	</div>
	<div class="row">
		<div class="col-xs-0 col-md-2">
		</div>
		<div class="col-xs-12 col-md-8">
		
		<table class="table table-striped table-bordered">
			<thead>
				<tr><th>ID</th><th>Name</th><th>Date Added</th></tr>
			</thead>
			<tbody>
				<?php echo $product_list; ?>
			</tbody>
		</table>
		</div>
		<div class="col-xs-0 col-md-2">
		</div>
	</div>
	<div class="row">
		<form id="form1" name="form1" method="post" action="inventory_list.php" class="form-horizontal" enctype="multipart/form-data">
			<div class="form-group">
			    <div class="input-group">
			    	<span class="input-group-addon">Product Name</span>
					<input name="fr_itemname" type="text" class="form-control" id="fr_itemname" size="40" value="<?php echo $product_name;?>"/>
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
			    	<span class="input-group-addon">Product Price</span>
					<input name="fr_price" type="text" class="form-control" id="fr_price" size="40" value="<?php echo $price;?>" />
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
			    	<span class="input-group-addon">Category</span>
					<input name="fr_category" type="text" class="form-control" id="fr_category" size="40" value="<?php echo $category;?>" />
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
			    	<span class="input-group-addon">Subcategory</span>
					<input name="fr_subcategory" type="text" class="form-control" id="fr_subcategory" size="40" value="<?php echo $subcategory;?>" />
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
			    	<span class="input-group-addon">Product Details</span>
					<textarea name="fr_details" type="textarea" rows="4" class="form-control" id="fr_details" size="40" value="<?php echo $details;?>"> </textarea>
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
			    	<span class="input-group-addon">Product Image</span>
					<input name="fr_image" type="file" class="form-control" id="fr_image" />
				</div>
			</div>
			<div class="form-group">
			<input name="button" type="submit" class="btn btn-default btn-primary" id="button" value="Add Item Now"/>
			<!--<p class="helper-text">Not an admin? <a href="#">Go to the store.</a></p>-->
			</div>
		</form>
	</div>
</div>
<?php include_once("../footer.php"); ?>
</body>
</htmL>