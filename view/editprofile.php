<?php
include '../control/editprofile_process.php';
include_once '../control/app.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - MediShop</title>
    <link rel="stylesheet" type="text/css" href="../css/task1_style.css">
</head>
<body>

<nav>
    <div class="nav-inner">
        <a class="nav-brand" href="Home.php">MediShop</a>
        <div class="nav-links">
            <a href="Home.php">Home</a>
            <?php if($_SESSION["role"] === "customer"){ ?>
                <a href="cart.php">Cart</a>
                <a href="customer_orders.php">My Orders</a>
            <?php } ?>
            <a href="profile.php">Profile</a>
            <?php if($_SESSION["role"] === "admin"){ ?>
                <a href="admin_dashboard.php">Admin Dashboard</a>
            <?php } ?>
            <a href="../control/logout_process.php">Logout</a>
        </div>
    </div>
</nav>

<div class="page-header">
    <h1>Edit Profile</h1>
    <p>Update your account information</p>
</div>

<div class="main-container">
    <div class="card" style="max-width:580px; margin:0 auto;">

        <?php if(isset($errors["database"])){ ?>
            <div class="msg-error"><?php echo htmlspecialchars($errors["database"]); ?></div>
        <?php } ?>

        <form action="" method="post" enctype="multipart/form-data" onsubmit="return validateProfile()">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
                <span class="error"><?php echo $errors["name"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <span class="error"><?php echo $errors["email"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address"><?php echo htmlspecialchars($address); ?></textarea>
                <span class="error"><?php echo $errors["address"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                <span class="error"><?php echo $errors["phone"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <?php if($profilePicture != ""){ ?>
                    <img src="<?php echo htmlspecialchars(mediaUrl($profilePicture, 'profile'), ENT_QUOTES, 'UTF-8'); ?>"
                         alt="Current Picture" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:8px;display:block;">
                <?php } ?>
                <label for="profile_picture">Profile Picture (JPEG/PNG, max 2MB)</label>
                <input type="file" id="profile_picture" name="profile_picture">
                <span class="error"><?php echo $errors["profile_picture"] ?? ""; ?></span>
            </div>

            <hr style="border:none;border-top:1px solid #e5e7eb;margin:20px 0;">
            <p style="font-size:13px;color:#6b7280;margin-bottom:14px;">Leave password fields blank to keep current password.</p>

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" placeholder="Required only if changing password">
                <span class="error"><?php echo $errors["current_password"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="new_password">New Password (min 8 characters)</label>
                <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current password">
                <span class="error"><?php echo $errors["new_password"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <input type="submit" name="update" value="Save Changes">
                &nbsp;<a href="profile.php" style="color:#6b7280;font-size:13px;">Cancel</a>
            </div>
        </form>

    </div>
</div>

<script src="../js/task1_script.js"></script>
</body>
</html>
<?php $mydb->closeConn($conn); ?>
