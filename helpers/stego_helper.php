<?php
// Delimiter untuk menandai akhir pesan rahasia
define('STEGO_DELIMITER', '||EOF||');

function hide_message_in_image($image_path, $message, $output_path) {
    $message_to_hide = $message . STEGO_DELIMITER;
    $message_bits = '';
    
    for ($i = 0; $i < strlen($message_to_hide); $i++) {
        $message_bits .= str_pad(decbin(ord($message_to_hide[$i])), 8, '0', STR_PAD_LEFT);
    }
    
    $bit_index = 0;
    $total_bits = strlen($message_bits);
    
    // Hanya gunakan PNG
    $image = imagecreatefrompng($image_path);
    if (!$image) return false;
    
    imagesavealpha($image, true); // Jaga transparansi
    
    list($width, $height) = getimagesize($image_path);
    
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            if ($bit_index >= $total_bits) {
                break 2; // Keluar dari kedua loop
            }
            
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            
            $bit = $message_bits[$bit_index];
            
            // Ganti LSB dari komponen BIRU (B)
            if ($bit == '1') {
                $b = $b | 1; 
            } else {
                $b = $b & ~1; 
            }
            
            $new_color = imagecolorallocatealpha($image, $r, $g, $b, imagecolorat($image, $x, $y) >> 24 & 0xFF);
            imagesetpixel($image, $x, $y, $new_color);
            
            $bit_index++;
        }
    }
    
    imagepng($image, $output_path, 9); // Kompresi PNG level 9 (lossless)
    imagedestroy($image);
    
    return ($bit_index >= $total_bits);
}

function extract_message_from_image($image_path) {
    $image = imagecreatefrompng($image_path);
    if (!$image) return "Gagal membaca gambar.";
    
    list($width, $height) = getimagesize($image_path);
    
    $char_bits = '';
    $message = '';
    
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            $b = $rgb & 0xFF;
            
            $bit = $b & 1;
            $char_bits .= $bit;
            
            if (strlen($char_bits) == 8) {
                $char = chr(bindec($char_bits));
                $message .= $char;
                $char_bits = '';
                
                if (strpos($message, STEGO_DELIMITER) !== false) {
                    imagedestroy($image);
                    return str_replace(STEGO_DELIMITER, '', $message);
                }
            }
        }
    }
    imagedestroy($image);
    return "Error: Pesan tidak ditemukan atau gambar korup.";
}
?>