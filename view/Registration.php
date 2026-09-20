<?php
include '../control/registration_process.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MediShop</title>
    <link rel="stylesheet" type="text/css" href="../css/task1_style.css">
</head>
<body>

<nav>
    <div class="nav-inner">
        <a class="nav-brand" href="Home.php">MediShop</a>
        <div class="nav-links">
            <a href="login.php">Login</a>
            <a href="Registration.php">Register</a>
        </div>
    </div>
</nav>

<div class="auth-wrapper">
    <div class="auth-box" style="max-width:520px;">
        <h2>Create an Account</h2>

        <?php if(isset($errors["database"])){ ?>
            <div class="msg-error"><?php echo htmlspecialchars($errors["database"]); ?></div>
        <?php } ?>

        <form action="" method="post" enctype="multipart/form-data" onsubmit="return validateRegistration()">
            <div class="form-group">
                <label for="reg_name">Full Name</label>
                <input type="text" id="reg_name" name="name" value="<?php echo htmlspecialchars($old["name"]); ?>" placeholder="Enter your full name">
                <span class="error"><?php echo $errors["name"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="text" id="reg_email" name="email" autocomplete="off" value="<?php echo htmlspecialchars($old["email"]); ?>" placeholder="Enter your email">
                <span class="error"><?php echo $errors["email"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="password">Password (min 8 characters)</label>
                <input type="password" id="reg_password" name="password" autocomplete="new-password" placeholder="Create a password">
                <span class="error"><?php echo $errors["password"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" placeholder="Enter your address"><?php echo htmlspecialchars($old["address"]); ?></textarea>
                <span class="error"><?php echo $errors["address"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($old["phone"]); ?>"
                       placeholder="Enter your phone number" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                <span class="error"><?php echo $errors["phone"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture (JPEG/PNG, max 2MB)</label>
                <input type="file" id="profile_picture" name="profile_picture">
                <span class="error"><?php echo $errors["profile_picture"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <input type="submit" name="register" value="Create Account">
            </div>
        </form>

        <div class="auth-footer">
            Already registered? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<script src="../js/task1_script.js"></script>
</body>
</html>
