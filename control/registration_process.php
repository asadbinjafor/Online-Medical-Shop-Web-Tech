<?php
include_once '../model/mydb.php';
include_once 'auth.php';
include_once 'upload.php';
session_start();

$errors = array();
$old    = array("name"=>"", "email"=>"", "address"=>"", "phone"=>"");

if(isset($_POST["register"])){
    $old["name"]    = trim($_POST["name"] ?? "");
    $old["email"]   = trim($_POST["email"] ?? "");
    $old["address"] = trim($_POST["address"] ?? "");
    $old["phone"]   = trim($_POST["phone"] ?? "");
    $password       = $_POST["password"] ?? "";

    if(count($errors) == 0 && $old["name"] == ""){
        $errors["name"] = "Name is required";
    }
    if($old["email"] == "" || !filter_var($old["email"], FILTER_VALIDATE_EMAIL)){
        $errors["email"] = "Valid email is required";
    }
    if(strlen($password) < 8){
        $errors["password"] = "Password must be at least 8 characters";
    }
    if($old["address"] == ""){
        $errors["address"] = "Address is required";
    }
    if($old["phone"] == ""){
        $errors["phone"] = "Phone is required";
    }
    else if(!ctype_digit($old["phone"])){
        $errors["phone"] = "Phone number must contain digits only";
    }

    $mydb           = new MyDB();
    $conn           = $mydb->createConn();
    $profilePicture = "";

    if(count($errors) == 0 && $mydb->emailExists($old["email"], $conn)->num_rows > 0){
        $errors["email"] = "Email already exists";
    }

    if(count($errors) == 0){
        $profilePicture = uploadProfilePicture("profile_picture", "", $errors);
    }

    if(count($errors) == 0){
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $result       = $mydb->createUser($old["name"], $old["email"], $passwordHash, "customer", $old["address"], $old["phone"], $profilePicture, $conn);
        if($result === true){
            header("Location: ../view/login.php?registered=1");
            exit();
        }
        else{
            $errors["database"] = "Registration failed. Please try again.";
        }
    }

    $mydb->closeConn($conn);
}
?>
