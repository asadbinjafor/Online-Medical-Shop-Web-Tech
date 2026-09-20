<?php
include_once '../model/mydb.php';
include_once 'auth.php';
include_once 'admin_gate.php';
include_once 'upload.php';

if(session_status() == PHP_SESSION_NONE){ session_start(); }
requireAdmin();

$mydb    = new MyDB();
$conn    = $mydb->createConn();
$errors  = array();
$success = "";
$isEdit  = false;
$editMed = null;

$old = array(
    "name"         => "",
    "category_id"  => "",
    "vendor_name"  => "",
    "price"        => "",
    "availability" => "",
    "description"  => "",
    "image_path"   => ""
);


if(isset($_GET["edit"])){
    $editId = (int)$_GET["edit"];
    $result = $mydb->getMedicineById($editId, $conn);
    if($result->num_rows > 0){
        $editMed              = $result->fetch_assoc();
        $isEdit               = true;
        $old["name"]          = $editMed["name"];
        $old["category_id"]   = $editMed["category_id"];
        $old["vendor_name"]   = $editMed["vendor_name"];
        $old["price"]         = $editMed["price"];
        $old["availability"]  = $editMed["availability"];
        $old["description"]   = $editMed["description"];
        $old["image_path"]    = $editMed["image_path"];
    } else {
        header("Location: ../view/admin_medicine.php");
        exit();
    }
}


if(isset($_POST["save_medicine"])){
    $isEdit      = !empty($_POST["med_id"]);
    $medId       = (int)($_POST["med_id"] ?? 0);
    $name        = trim($_POST["name"] ?? "");
    $categoryId  = (int)($_POST["category_id"] ?? 0);
    $vendorName  = trim($_POST["vendor_name"] ?? "");
    $price       = $_POST["price"] ?? "";
    $availability = $_POST["availability"] ?? "";
    $description = trim($_POST["description"] ?? "");

    $old["name"]         = $name;
    $old["category_id"]  = $categoryId;
    $old["vendor_name"]  = $vendorName;
    $old["price"]        = $price;
    $old["availability"] = $availability;
    $old["description"]  = $description;

    if($name == ""){
        $errors["name"] = "Medicine name is required";
    }
    if($categoryId <= 0){
        $errors["category_id"] = "Please select a category";
    }
    if($vendorName == ""){
        $errors["vendor_name"] = "Vendor name is required";
    }
    if($price === "" || !is_numeric($price) || (float)$price <= 0){
        $errors["price"] = "Price must be a number greater than 0";
    }
    if($availability === "" || !ctype_digit((string)$availability) || (int)$availability < 0){
        $errors["availability"] = "Availability must be a non-negative whole number";
    }

    $existingImage = "";
    if($isEdit){
        $currentMedicine = $mydb->getMedicineById($medId, $conn);
        if($currentMedicine->num_rows === 0){
            $errors["database"] = "Medicine not found";
        } else {
            $existingImage = $currentMedicine->fetch_assoc()["image_path"] ?? "";
        }
    }
    $old["image_path"] = $existingImage;
    $imagePath = uploadMedicineImage("image", $existingImage, $errors);

    if(empty($errors)){
        $priceVal        = (float)$price;
        $availabilityVal = (int)$availability;

        if($isEdit){
            if($mydb->updateMedicine($medId, $name, $categoryId, $vendorName, $priceVal, $availabilityVal, $description, $imagePath, $conn)){
                if($imagePath !== $existingImage){
                    deleteMedicineImageFile($existingImage);
                }
                $success = "Medicine updated successfully";
                $result  = $mydb->getMedicineById($medId, $conn);
                if($result->num_rows > 0){
                    $editMed             = $result->fetch_assoc();
                    $old["image_path"]   = $editMed["image_path"];
                }
            } else {
                $errors["database"] = "Failed to update medicine. Please try again.";
            }
        } else {
            if($mydb->createMedicine($name, $categoryId, $vendorName, $priceVal, $availabilityVal, $description, $imagePath, $conn)){
                header("Location: ../view/admin_medicine.php?added=1");
                exit();
            } else {
                $errors["database"] = "Failed to add medicine. Please try again.";
            }
        }
    }
}

$categories = $mydb->getCategories($conn);
?>
