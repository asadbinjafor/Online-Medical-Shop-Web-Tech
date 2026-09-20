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


if(isset($_POST["delete_medicine"])){
    $delId = (int)($_POST["med_id"] ?? 0);

    if($mydb->medicineInAnyOrder($delId, $conn)){
        $errors["delete"] = "Cannot delete: this medicine is part of an order";
    } else {
        $medResult = $mydb->getMedicineById($delId, $conn);
        if($medResult->num_rows > 0){
            $medRow = $medResult->fetch_assoc();
            if($mydb->deleteMedicine($delId, $conn)){
                deleteMedicineImageFile($medRow["image_path"]);
                $success = "Medicine deleted successfully";
            } else {
                $errors["delete"] = "Failed to delete medicine. Please try again.";
            }
        } else {
            $errors["delete"] = "Medicine not found";
        }
    }
}

$medicines  = $mydb->getAllMedicines($conn);
$categories = $mydb->getCategories($conn);
?>
