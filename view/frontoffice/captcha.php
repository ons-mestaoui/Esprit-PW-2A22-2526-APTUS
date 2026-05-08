<?php
session_start();

$code = '';
for ($i = 0; $i < 5; $i++) {
    $code .= chr(rand(97, 122)); // Lowercase letters only for ease of reading
}
$_SESSION['captcha'] = $code;

$width = 120;
$height = 40;
$image = imagecreatetruecolor($width, $height);

// Colors
$bg_color = imagecolorallocate($image, 245, 245, 245); // light gray
$text_color = imagecolorallocate($image, 50, 50, 50); // dark gray
$noise_color = imagecolorallocate($image, 180, 180, 180); // mid gray

// Fill background
imagefill($image, 0, 0, $bg_color);

// Add noise (lines)
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $noise_color);
}

// Add noise (dots)
for ($i = 0; $i < 50; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noise_color);
}

// Draw the string
// We'll use the built-in GD font (size 5 is the largest default)
// For better styling we center it
$font_size = 5;
$char_width = imagefontwidth($font_size);
$char_height = imagefontheight($font_size);

$text_width = $char_width * strlen($code);
$x = ($width - $text_width) / 2;
$y = ($height - $char_height) / 2;

imagestring($image, $font_size, $x, $y, $code, $text_color);

// Output
header('Content-Type: image/png');
header('Cache-Control: no-cache, must-revalidate'); // Avoid caching old captcha
imagepng($image);
imagedestroy($image);
?>
