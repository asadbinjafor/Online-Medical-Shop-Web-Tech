<?php
include '../control/medicine_detail_process.php';
include_once '../control/app.php';

$imageWeb = "";
if(!empty($medicine["image_path"])){
    $imageWeb = htmlspecialchars(mediaUrl($medicine["image_path"], 'medicine'), ENT_QUOTES, 'UTF-8');
}
$isCustomer = isset($_SESSION["user_id"]) && $_SESSION["role"] === "customer";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($medicine["name"]); ?> - MediShop</title>
    <link rel="stylesheet" type="text/css" href="../css/task1_style.css">
    <?php if($isCustomer){ ?>
    <link rel="stylesheet" type="text/css" href="../css/task3_style.css">
    <link rel="stylesheet" type="text/css" href="../css/task4_style.css">
    <?php } else { ?>
    <link rel="stylesheet" type="text/css" href="../css/task4_style.css">
    <?php } ?>
</head>
<body>

<nav>
    <div class="nav-inner">
        <a class="nav-brand" href="Home.php">MediShop</a>
        <div class="nav-links">
            <a href="Home.php">Home</a>
            <?php if(isset($_SESSION["user_id"])){ ?>
                <?php if($_SESSION["role"] === "customer"){ ?>
                    <a href="cart.php">Cart (<span id="navCartCount"><?php echo $cartCount; ?></span>)</a>
                    <a href="customer_orders.php">My Orders</a>
                <?php } ?>
                <a href="profile.php">Profile</a>
                <?php if($_SESSION["role"] === "admin"){ ?>
                    <a href="admin_dashboard.php">Admin Dashboard</a>
                <?php } ?>
                <a href="../control/logout_process.php">Logout</a>
                <span class="nav-user-info">Hi, <?php echo htmlspecialchars($_SESSION["name"]); ?></span>
            <?php } else { ?>
                <a href="login.php">Login</a>
                <a href="Registration.php">Register</a>
            <?php } ?>
        </div>
    </div>
</nav>

<div class="page-header">
    <h1><?php echo htmlspecialchars($medicine["name"]); ?></h1>
    <p>Medicine details</p>
</div>

<div class="main-container">

    <div class="medicine-detail-card">
        <div class="medicine-detail-image">
            <?php if($imageWeb != ""){ ?>
                <img src="<?php echo $imageWeb; ?>" alt="<?php echo htmlspecialchars($medicine["name"]); ?>">
            <?php } else { ?>
                <div class="medicine-no-image">No Image</div>
            <?php } ?>
        </div>
        <div class="medicine-detail-info">
            <span class="badge badge-<?php echo htmlspecialchars($medicine["category_type"] ?? "solid"); ?>">
                <?php echo ucfirst(htmlspecialchars($medicine["category_type"] ?? "")); ?>
            </span>
            <p><strong>Genre:</strong> <?php echo htmlspecialchars($medicine["category_name"] ?? "—"); ?></p>
            <p><strong>Vendor:</strong> <?php echo htmlspecialchars($medicine["vendor_name"]); ?></p>
            <p><strong>Price:</strong> <span class="medicine-price">BDT <?php echo number_format($medicine["price"], 2); ?></span></p>
            <p><strong>Stock:</strong>
                <?php if($medicine["availability"] > 0){ ?>
                    <?php echo (int)$medicine["availability"]; ?> units available
                <?php } else { ?>
                    <span class="text-out-of-stock">Out of stock</span>
                <?php } ?>
            </p>
            <p><strong>Description:</strong></p>
            <p class="medicine-description"><?php echo nl2br(htmlspecialchars($medicine["description"] ?? "No description available.")); ?></p>

            <?php if($isCustomer && $medicine["availability"] > 0){ ?>
                <div class="add-cart-form medicine-detail-cart">
                    <label for="detailQty">Quantity</label>
                    <input type="number" id="detailQty" class="add-cart-qty" value="1" min="1"
                           max="<?php echo (int)$medicine["availability"]; ?>"
                           data-stock="<?php echo (int)$medicine["availability"]; ?>">
                    <button type="button" class="btn-add-cart" onclick="addToCartFromDetail(<?php echo $medicine["id"]; ?>)">Add to Cart</button>
                </div>
            <?php } elseif($isCustomer){ ?>
                <p class="text-out-of-stock">This medicine is currently out of stock.</p>
            <?php } elseif(!isset($_SESSION["user_id"])){ ?>
                <p><a href="login.php">Login</a> as a customer to add this medicine to your cart.</p>
            <?php } ?>

            <div class="medicine-detail-back">
                <a class="btn-secondary" href="Home.php">&larr; Back to Home</a>
            </div>
        </div>
    </div>

</div>

<?php if($isCustomer){ ?>
<script src="../js/task3_script.js"></script>
<script src="../js/task4_script.js"></script>
<?php } ?>
</body>
</html>
<?php $mydb->closeConn($conn); ?>
