<?php
include_once '../model/mydb.php';
include_once 'auth.php';
include_once 'upload.php';
session_start();

$mydb = new MyDB();
$conn = $mydb->createConn();
tryRememberLogin($mydb, $conn);

if(!isset($_SESSION["user_id"])){
    header("Location: ../view/login.php");
    exit();
}

$errors     = array();
$successMsg = "";
$result     = $mydb->getUserById($_SESSION["user_id"], $conn);

if($result->num_rows == 0){
    header("Location: ../control/logout_process.php");
    exit();
}

$user           = $result->fetch_assoc();
$name           = $user["name"];
$email          = $user["email"];
$address        = $user["address"];
$phone          = $user["phone"];
$profilePicture = $user["profile_picture"];

if(isset($_POST["update"])){
    $name        = trim($_POST["name"] ?? "");
    $email       = trim($_POST["email"] ?? "");
    $address     = trim($_POST["address"] ?? "");
    $phone       = trim($_POST["phone"] ?? "");
    $currentPass = $_POST["current_password"] ?? "";
    $newPass     = $_POST["new_password"] ?? "";

    if(count($errors) == 0 && $name == ""){
        $errors["name"] = "Name is required";
    }
    if($email == "" || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors["email"] = "Valid email is required";
    }
    if($address == ""){
        $errors["address"] = "Address is required";
    }
    if($phone == ""){
        $errors["phone"] = "Phone is required";
    }
    if(empty($errors["email"]) && $mydb->emailExistsForOtherUser($email, $_SESSION["user_id"], $conn)->num_rows > 0){
        $errors["email"] = "Email already used by another account";
    }
    if($newPass != ""){
        if(strlen($newPass) < 8){
            $errors["new_password"] = "New password must be at least 8 characters";
        }
        if($currentPass == "" || !password_verify($currentPass, $user["password_hash"])){
            $errors["current_password"] = "Current password is required and must be correct";
        }
    }

    if(count($errors) == 0){
        $profilePicture = uploadProfilePicture("profile_picture", $profilePicture, $errors);
    }

    if(count($errors) == 0){
        $updateResult = $mydb->updateProfile($_SESSION["user_id"], $name, $email, $address, $phone, $profilePicture, $conn);
        if($updateResult === true && $newPass != ""){
            $updateResult = $mydb->updatePassword($_SESSION["user_id"], password_hash($newPass, PASSWORD_DEFAULT), $conn);
        }
        if($updateResult === true){
            if($profilePicture !== $user["profile_picture"]){
                deleteProfileImageFile($user["profile_picture"]);
            }
            if($newPass != ""){
                clearRememberCookie();
                $_SESSION = array();
                session_destroy();
                header("Location: ../view/login.php?password_changed=1");
                exit();
            }
            $_SESSION["name"] = $name;
            header("Location: ../view/profile.php?updated=1");
            exit();
        }
        else{
            $errors["database"] = "Update failed. Please try again.";
        }
    }
}
?>
