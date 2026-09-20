<?php
include '../control/admin_medicine_process.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Management - MediShop</title>
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
    <h1>Medicine Management</h1>
    <p>Add, edit and delete medicines</p>
</div>

<div class="main-container">

    <?php if(!empty($errors["delete"])){ ?>
        <div class="msg-error"><?php echo htmlspecialchars($errors["delete"]); ?></div>
    <?php } ?>
    <?php if($success != ""){ ?>
        <div class="msg-success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>
    <?php if(isset($_GET["added"])){ ?>
        <div class="msg-success">Medicine added successfully</div>
    <?php } ?>

    <div style="margin-bottom:18px;">
        <a class="btn-add" href="admin_medicine_form.php">+ Add New Medicine</a>
    </div>

    <?php if($medicines->num_rows == 0){ ?>
        <div class="no-results">No medicines found. Add one to get started.</div>
    <?php } else { ?>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Vendor</th>
                        <th>Price (BDT)</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while($med = $medicines->fetch_assoc()){ ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td>
                                <?php if(!empty($med["image_path"])){ ?>
                                    <img src="<?php echo htmlspecialchars(mediaUrl($med["image_path"], 'medicine'), ENT_QUOTES, 'UTF-8'); ?>"
                                         alt="medicine" class="medicine-thumb">
                                <?php } else { ?>
                                    <span class="no-image">No image</span>
                                <?php } ?>
                            </td>
                            <td><?php echo htmlspecialchars($med["name"]); ?></td>
                            <td>
                                <?php echo htmlspecialchars($med["category_name"] ?? "—"); ?>
                                <?php if(!empty($med["category_type"])){ ?>
                                    <span class="badge badge-<?php echo htmlspecialchars($med["category_type"]); ?>">
                                        <?php echo htmlspecialchars($med["category_type"]); ?>
                                    </span>
                                <?php } ?>
                            </td>
                            <td><?php echo htmlspecialchars($med["vendor_name"]); ?></td>
                            <td><?php echo number_format($med["price"], 2); ?></td>
                            <td>
                                <span class="<?php echo $med["availability"] > 0 ? "stock-ok" : "stock-out"; ?>">
                                    <?php echo $med["availability"]; ?>
                                </span>
                            </td>
                            <td>
                                <a class="btn-action btn-edit-sm"
                                   href="admin_medicine_form.php?edit=<?php echo $med["id"]; ?>">Edit</a>

                                <form action="" method="post" style="display:inline;"
                                      onsubmit="return confirmDelete('medicine')">
                                    <input type="hidden" name="med_id" value="<?php echo $med["id"]; ?>">
                                    <button type="submit" name="delete_medicine" class="btn-action btn-delete-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>

</div>

<script src="../js/task2_script.js"></script>
</body>
</html>
<?php $mydb->closeConn($conn); ?>
