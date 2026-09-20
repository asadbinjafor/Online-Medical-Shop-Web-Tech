<?php
include_once __DIR__ . "/app.php";

function setLoginSession($user){
    session_regenerate_id(true);
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["name"]    = $user["name"];
    $_SESSION["role"]    = $user["role"];
}

function setRememberCookie($userId){
    if(REMEMBER_SECRET === ''){
        return;
    }
    $token   = hash_hmac("sha256", (string)$userId, REMEMBER_SECRET);
    $options = array(
        "expires"  => time() + (86400 * 7),
        "path"     => "/",
        "httponly" => true,
        "secure"   => getenv('APP_ENV') === 'production',
        "samesite" => "Strict"
    );
    setcookie("remember_me", $userId . ":" . $token, $options);
}

function clearRememberCookie(){
    $options = array(
        "expires"  => time() - 3600,
        "path"     => "/",
        "httponly" => true,
        "samesite" => "Strict"
    );
    setcookie("remember_me", "", $options);
}

function tryRememberLogin($mydb, $conn){
    if(REMEMBER_SECRET === '' || isset($_SESSION["user_id"]) || !isset($_COOKIE["remember_me"])){
        return;
    }

    $parts = explode(":", $_COOKIE["remember_me"]);
    if(count($parts) != 2){
        return;
    }

    $cookieUserId = (int)$parts[0];
    $cookieToken  = $parts[1];
    $expected     = hash_hmac("sha256", (string)$cookieUserId, REMEMBER_SECRET);

    if(!hash_equals($expected, $cookieToken)){
        return;
    }

    $result = $mydb->getUserById($cookieUserId, $conn);
    if($result->num_rows > 0){
        setLoginSession($result->fetch_assoc());
    }
}
?>
