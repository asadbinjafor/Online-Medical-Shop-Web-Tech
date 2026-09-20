<?php
include '../control/admin_medicine_form_process.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? "Edit Medicine" : "Add Medicine"; ?> - MediShop</title>
    <link rel="stylesheet" type="text/css" href="../css/task2_style.css">
</head>
<body>

<nav>
    <div class="nav-inner">
        <a class="nav-brand" href="admin_dashboard.php">MediShop Admin</a>
        <div class="nav-links">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="admin_categories.php">Categories</a>
            <a href="admin_medicine.php">Medicines</a>
            <a href="admin_customers.php">Customers</a>
            <a href="admin_orders.php">Orders</a>
            <a href="admin_history.php">History</a>
            <a href="Home.php">Home</a>
            <a href="../control/logout_process.php">Logout</a>
            <span class="nav-user-info">Admin: <?php echo htmlspecialchars($_SESSION["name"]); ?></span>
        </div>
    </div>
</nav>

<div class="page-header">
    <h1><?php echo $isEdit ? "Edit Medicine" : "Add New Medicine"; ?></h1>
    <p><?php echo $isEdit ? "Update medicine details" : "Fill in the details to add a new medicine"; ?></p>
</div>

<div class="main-container">

    <?php if(!empty($errors["database"])){ ?>
        <div class="msg-error"><?php echo htmlspecialchars($errors["database"]); ?></div>
    <?php } ?>
    <?php if($success != ""){ ?>
        <div class="msg-success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <div class="card" style="max-width:620px;">
        <form action="" method="post" enctype="multipart/form-data"
              onsubmit="return validateMedicineForm()">
            <?php if($isEdit && $editMed){ ?>
                <input type="hidden" name="med_id" value="<?php echo $editMed["id"]; ?>">
            <?php } ?>

            <div class="form-group">
                <label for="name">Medicine Name</label>
                <input type="text" id="name" name="name"
                       value="<?php echo htmlspecialchars($old["name"]); ?>"
                       placeholder="e.g. Napa 500">
                <span class="error"><?php echo $errors["name"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">-- Select Category --</option>
                    <?php while($cat = $categories->fetch_assoc()){ ?>
                        <option value="<?php echo $cat["id"]; ?>"
                            <?php if($old["category_id"] == $cat["id"]) echo "selected"; ?>>
                            <?php echo htmlspecialchars($cat["name"]); ?>
                            (<?php echo htmlspecialchars($cat["category_type"]); ?>)
                        </option>
                    <?php } ?>
                </select>
                <span class="error"><?php echo $errors["category_id"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="vendor_name">Vendor Name</label>
                <input type="text" id="vendor_name" name="vendor_name"
                       value="<?php echo htmlspecialchars($old["vendor_name"]); ?>"
                       placeholder="e.g. Beximco Pharma">
                <span class="error"><?php echo $errors["vendor_name"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="price">Price (BDT)</label>
                <input type="text" id="price" name="price"
                       value="<?php echo htmlspecialchars($old["price"]); ?>"
                       placeholder="e.g. 12.50">
                <span class="error"><?php echo $errors["price"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="availability">Availability (Stock)</label>
                <input type="text" id="availability" name="availability"
                       value="<?php echo htmlspecialchars($old["availability"]); ?>"
                       placeholder="e.g. 100">
                <span class="error"><?php echo $errors["availability"] ?? ""; ?></span>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"
                          placeholder="Brief medicine description"><?php echo htmlspecialchars($old["description"]); ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Medicine Image (JPEG/PNG, max 2MB)</label>
                <?php if(!empty($old["image_path"])){ ?>
                    <div class="current-image-preview">
                        <img src="<?php echo htmlspecialchars(mediaUrl($old["image_path"], 'medicine'), ENT_QUOTES, 'UTF-8'); ?>"
                             alt="Current image" class="medicine-thumb-lg">
                        <small>Current image — upload new to replace</small>
                    </div>
                <?php } ?>
                <input type="file" id="image" name="image">
                <span class="error"><?php echo $errors["image"] ?? ""; ?></span>
            </div>

            <div class="form-group" style="display:flex; gap:10px;">
                <input type="submit" name="save_medicine"
                       value="<?php echo $isEdit ? "Update Medicine" : "Add Medicine"; ?>">
                <a class="btn-cancel" href="admin_medicine.php">Cancel</a>
            </div>
        </form>
    </div>

</div>

<script src="../js/task2_script.js"></script>
</body>
</html>
<?php $mydb->closeConn($conn); ?>
