<?php
session_start();
$code = rand(1000, 9999);
$_SESSION['captcha_code'] = $code;

header("Content-Type: image/png");
$image = imagecreate(100, 40);
$bg = imagecolorallocate($image, 230, 230, 250); // lavender
$text_color = imagecolorallocate($image, 70, 0, 130); // dark purple
imagestring($image, 5, 25, 10, $code, $text_color);
imagepng($image);
imagedestroy($image);
?>
