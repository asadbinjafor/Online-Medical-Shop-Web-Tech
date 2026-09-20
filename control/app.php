<?php
define("ROOT_DIR", dirname(__DIR__));
define("PROFILE_UPLOAD_DIR",  ROOT_DIR . "/uploads/profile/");
define("PROFILE_UPLOAD_WEB",  "../uploads/profile/");
define("MEDICINE_UPLOAD_DIR", ROOT_DIR . "/uploads/medicines/");
define("MEDICINE_UPLOAD_WEB", "../uploads/medicines/");
define("REMEMBER_SECRET", getenv("REMEMBER_SECRET") ?: "");

function mediaUrl($imagePath, $kind){
    if(preg_match('~^https://~i', (string)$imagePath)){
        return $imagePath;
    }
    $prefix = $kind === 'profile' ? PROFILE_UPLOAD_WEB : MEDICINE_UPLOAD_WEB;
    return $prefix . rawurlencode(basename((string)$imagePath));
}

function ensureUploadDir($dir){
    if(!is_dir($dir)){
        mkdir($dir, 0755, true);
    }
}
?>
