<?php
include '../control/profile_process.php';
include_once '../control/app.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MediShop</title>
    <link rel="stylesheet" type="text/css" href="../css/task1_style.css">
</head>
<body>

<nav>
    <div class="nav-inner">
        <a class="nav-brand" href="Home.php">MediShop</a>
        <div class="nav-links">
            <a href="Home.php">Home</a>
            <?php if($role === "customer"){ ?>
                <a href="cart.php">Cart</a>
                <a href="customer_orders.php">My Orders</a>
            <?php } ?>
            <a href="profile.php">Profile</a>
            <?php if($role === "admin"){ ?>
                <a href="admin_dashboard.php">Admin Dashboard</a>
            <?php } ?>
            <a href="../control/logout_process.php">Logout</a>
            <span class="nav-user-info">Hi, <?php echo htmlspecialchars($_SESSION["name"]); ?></span>
        </div>
    </div>
</nav>

<div class="page-header">
    <h1>My Profile</h1>
    <p>View and manage your account details</p>
</div>

<div class="main-container">

    <?php if(isset($_GET["updated"])){ ?>
        <div class="msg-success">Profile updated successfully.</div>
    <?php } ?>

    <div class="card">
        <div class="profile-layout">
            <?php if($profilePicture != ""){ ?>
                <img class="profile-avatar"
                     src="<?php echo htmlspecialchars(mediaUrl($profilePicture, 'profile'), ENT_QUOTES, 'UTF-8'); ?>"
                     alt="Profile Picture">
            <?php } else { ?>
                <div class="profile-avatar-placeholder">&#9786;</div>
            <?php } ?>

            <div class="profile-details">
                <h2><?php echo htmlspecialchars($name); ?></h2>
                <span class="role-badge role-<?php echo $role; ?>">
                    <?php echo ucfirst(htmlspecialchars($role)); ?>
                </span>
                <p>Email: <?php echo htmlspecialchars($email); ?></p>
                <p>Phone: <?php echo htmlspecialchars($phone); ?></p>
                <p>Address: <?php echo htmlspecialchars($address); ?></p>
                <a class="btn-edit" href="editprofile.php">Edit Profile</a>
            </div>
        </div>
    </div>

</div>

</body>
</html>
<?php $mydb->closeConn($conn); ?>
