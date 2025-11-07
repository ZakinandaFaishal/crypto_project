<?php
define('STEGO_DELIMITER', '||EOF||');

function hide_message_in_image($image_path, $message, $output_path)
{
    //logika persiapan $message_bits 
    $message_to_hide = $message . STEGO_DELIMITER;
    $message_bits = '';
    for ($i = 0; $i < strlen($message_to_hide); $i++) {
        $message_bits .= str_pad(decbin(ord($message_to_hide[$i])), 8, '0', STR_PAD_LEFT);
    }
    $bit_index = 0;
    $total_bits = strlen($message_bits);

    // Buka gambar
    $image = imagecreatefrompng($image_path);
    if (!$image) return false;

    // Cek apakah gambar dimuat sebagai palet (indexed color)
    if (!imageistruecolor($image)) {
        // Jika ya, PAKSA konversi ke truecolor (RGBA)
        // kunci modifikasi LSB konsisten
        imagepalettetotruecolor($image);
    }

    imagesavealpha($image, true); // Jaga transparansi

    list($width, $height) = getimagesize($image_path);

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            if ($bit_index >= $total_bits) {
                break 2; // Keluar dari kedua loop
            }

            // Ambil data piksel
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $alpha = ($rgb >> 24) & 0x7F; // Ambil 7-bit alpha

            $bit = $message_bits[$bit_index];

            // Ganti LSB dari komponen BIRU (B)
            if ($bit == '1') {
                $b = $b | 1;
            } else {
                $b = $b & ~1;
            }

            // Alokasikan warna baru
            $new_color = imagecolorallocatealpha($image, $r, $g, $b, $alpha);
            if ($new_color === false) {
                // Gagal alokasi (seharusnya tidak terjadi di truecolor)
                imagedestroy($image);
                return false;
            }

            imagesetpixel($image, $x, $y, $new_color);

            $bit_index++;
        }
    }

    // Simpan TANPA kompresi
    imagepng($image, $output_path, 9);
    imagedestroy($image);

    return ($bit_index >= $total_bits);
}

// Fungsi extract_message_from_image
function extract_message_from_image($image_path)
{
    $image = imagecreatefrompng($image_path);
    if (!$image) return "Gagal membaca gambar.";

    list($width, $height) = getimagesize($image_path);

    $char_bits = '';
    $message = '';

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            $b = $rgb & 0xFF;

            // Ekstrak LSB
            $bit = $b & 1;
            $char_bits .= $bit;

            if (strlen($char_bits) == 8) {
                $char = chr(bindec($char_bits));
                $message .= $char;
                $char_bits = '';

                // Cek delimiter
                if (strpos($message, STEGO_DELIMITER) !== false) {
                    imagedestroy($image);
                    // Hapus delimiter dari pesan
                    return str_replace(STEGO_DELIMITER, '', $message);
                }
            }
        }
    }
    imagedestroy($image);
    return "Error: Delimiter tidak ditemukan atau gambar korup.";
}
