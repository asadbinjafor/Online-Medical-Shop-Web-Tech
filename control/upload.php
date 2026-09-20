<?php
include_once __DIR__ . "/app.php";

function storageConfigured(){
    return getenv('SUPABASE_URL') && getenv('SUPABASE_SECRET_KEY') && getenv('SUPABASE_STORAGE_BUCKET');
}

function storageObjectUrl($objectPath){
    return rtrim(getenv('SUPABASE_URL'), '/') . '/storage/v1/object/public/'
        . rawurlencode(getenv('SUPABASE_STORAGE_BUCKET')) . '/' . $objectPath;
}

function uploadToStorage($tmpName, $fileType, $objectPath){
    $key = getenv('SUPABASE_SECRET_KEY');
    $url = rtrim(getenv('SUPABASE_URL'), '/') . '/storage/v1/object/'
        . rawurlencode(getenv('SUPABASE_STORAGE_BUCKET')) . '/' . $objectPath;
    $handle = curl_init($url);
    curl_setopt_array($handle, array(
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => file_get_contents($tmpName),
        CURLOPT_HTTPHEADER => array('apikey: ' . $key, 'Content-Type: ' . $fileType),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ));
    $response = curl_exec($handle);
    $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);
    if($response === false || $status < 200 || $status >= 300){
        error_log('Supabase Storage upload failed, HTTP ' . $status . ': ' . (string)$response);
        return false;
    }
    return storageObjectUrl($objectPath);
}

function deleteStorageObject($imageUrl){
    if(!storageConfigured()){
        return;
    }
    $prefix = storageObjectUrl('');
    if(strncmp((string)$imageUrl, $prefix, strlen($prefix)) !== 0){
        return;
    }
    $key = getenv('SUPABASE_SECRET_KEY');
    $url = rtrim(getenv('SUPABASE_URL'), '/') . '/storage/v1/object/'
        . rawurlencode(getenv('SUPABASE_STORAGE_BUCKET'));
    $handle = curl_init($url);
    curl_setopt_array($handle, array(
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_POSTFIELDS => json_encode(array('prefixes' => array(substr($imageUrl, strlen($prefix))))),
        CURLOPT_HTTPHEADER => array('apikey: ' . $key, 'Content-Type: application/json'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ));
    curl_exec($handle);
    $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);
    if($status < 200 || $status >= 300){
        error_log('Supabase Storage delete failed, HTTP ' . $status);
    }
}

function uploadProfilePicture($fieldName, $oldPicture, &$errors){
    if(empty($_FILES[$fieldName]["name"])){
        return $oldPicture;
    }

    if($_FILES[$fieldName]["error"] !== UPLOAD_ERR_OK){
        $errors["profile_picture"] = "File upload error. Please try again.";
        return $oldPicture;
    }

    $allowedTypes = array("image/jpeg" => "jpg", "image/png" => "png");
    $maxSize      = 2 * 1024 * 1024;
    $tmpName      = $_FILES[$fieldName]["tmp_name"];
    $fileType     = mime_content_type($tmpName);

    if(!isset($allowedTypes[$fileType])){
        $errors["profile_picture"] = "Only JPEG or PNG profile picture is allowed";
        return $oldPicture;
    }
    if($_FILES[$fieldName]["size"] > $maxSize){
        $errors["profile_picture"] = "Profile picture must be 2MB or less";
        return $oldPicture;
    }

    $fileName   = "profile_" . bin2hex(random_bytes(12)) . "." . $allowedTypes[$fileType];
    if(storageConfigured()){
        $url = uploadToStorage($tmpName, $fileType, 'profile/' . $fileName);
        if($url !== false){
            return $url;
        }
        $errors["profile_picture"] = "Profile picture upload failed";
        return $oldPicture;
    }
    if(getenv('APP_ENV') === 'production'){
        $errors["profile_picture"] = "Image storage is not configured";
        return $oldPicture;
    }
    ensureUploadDir(PROFILE_UPLOAD_DIR);
    $uploadPath = PROFILE_UPLOAD_DIR . $fileName;

    if(move_uploaded_file($tmpName, $uploadPath)){
        return $fileName;
    }

    $errors["profile_picture"] = "Profile picture upload failed";
    return $oldPicture;
}

function uploadMedicineImage($fieldName, $oldImage, &$errors){
    if(empty($_FILES[$fieldName]["name"])){
        return $oldImage;
    }

    if($_FILES[$fieldName]["error"] !== UPLOAD_ERR_OK){
        $errors["image"] = "File upload error. Please try again.";
        return $oldImage;
    }

    $allowedTypes = array("image/jpeg" => "jpg", "image/png" => "png");
    $maxSize      = 2 * 1024 * 1024;
    $tmpName      = $_FILES[$fieldName]["tmp_name"];
    $fileType     = mime_content_type($tmpName);

    if(!isset($allowedTypes[$fileType])){
        $errors["image"] = "Only JPEG or PNG image is allowed";
        return $oldImage;
    }
    if($_FILES[$fieldName]["size"] > $maxSize){
        $errors["image"] = "Medicine image must be 2MB or less";
        return $oldImage;
    }

    $fileName   = "medicine_" . bin2hex(random_bytes(12)) . "." . $allowedTypes[$fileType];
    if(storageConfigured()){
        $url = uploadToStorage($tmpName, $fileType, 'medicines/' . $fileName);
        if($url !== false){
            return $url;
        }
        $errors["image"] = "Medicine image upload failed";
        return $oldImage;
    }
    if(getenv('APP_ENV') === 'production'){
        $errors["image"] = "Image storage is not configured";
        return $oldImage;
    }
    ensureUploadDir(MEDICINE_UPLOAD_DIR);
    $uploadPath = MEDICINE_UPLOAD_DIR . $fileName;

    if(move_uploaded_file($tmpName, $uploadPath)){
        return $fileName;
    }

    $errors["image"] = "Medicine image upload failed";
    return $oldImage;
}

function deleteMedicineImageFile($imagePath){
    if(preg_match('~^https://~i', (string)$imagePath)){
        deleteStorageObject($imagePath);
        return;
    }
    if(!empty($imagePath)){
        $fullPath = MEDICINE_UPLOAD_DIR . basename($imagePath);
        if(file_exists($fullPath)){
            unlink($fullPath);
        }
    }
}

function deleteProfileImageFile($imagePath){
    if(preg_match('~^https://~i', (string)$imagePath)){
        deleteStorageObject($imagePath);
        return;
    }
    if(!empty($imagePath)){
        $fullPath = PROFILE_UPLOAD_DIR . basename($imagePath);
        if(file_exists($fullPath)){
            unlink($fullPath);
        }
    }
}
?>
