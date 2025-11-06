<?php
// Delimiter untuk menandai akhir pesan rahasia
define('STEGO_DELIMITER_AUDIO', '||EOF_AUDIO||');

// PERBAIKAN: Gunakan offset aman 1024 byte, bukan 44 byte.
// Ini untuk menghindari kerusakan pada header/metadata file WAV.
define('STEGO_AUDIO_OFFSET', 1024);

function hide_message_in_wav($wav_path, $message, $output_path) {
    // Siapkan pesan
    $message_to_hide = $message . STEGO_DELIMITER_AUDIO;
    $message_bits = '';
    for ($i = 0; $i < strlen($message_to_hide); $i++) {
        $message_bits .= str_pad(decbin(ord($message_to_hide[$i])), 8, '0', STR_PAD_LEFT);
    }
    
    $bit_index = 0;
    $total_bits = strlen($message_bits);

    // Baca data file WAV
    $wav_data = file_get_contents($wav_path);
    if ($wav_data === false) return false;

    // Pisahkan header (offset aman) dan data audio
    $header_safe_zone = substr($wav_data, 0, STEGO_AUDIO_OFFSET);
    $audio_data = substr($wav_data, STEGO_AUDIO_OFFSET);
    $data_len = strlen($audio_data);

    if ($total_bits > $data_len) {
        return false; // Pesan terlalu panjang
    }

    // Modifikasi LSB dari setiap byte data audio
    for ($i = 0; $i < $data_len; $i++) {
        if ($bit_index >= $total_bits) {
            break; // Selesai
        }

        $byte = ord($audio_data[$i]);
        $bit = $message_bits[$bit_index];

        // Ganti LSB
        if ($bit == '1') {
            $byte = $byte | 1;
        } else {
            $byte = $byte & ~1;
        }
        
        $audio_data[$i] = chr($byte);
        $bit_index++;
    }

    // Gabungkan kembali header dan data audio yang sudah dimodifikasi
    $new_wav_data = $header_safe_zone . $audio_data;
    
    // Simpan file WAV baru
    if (file_put_contents($output_path, $new_wav_data)) {
        return true;
    } else {
        return false;
    }
}

function extract_message_from_wav($wav_path) {
    $wav_data = file_get_contents($wav_path);
    if ($wav_data === false) return "Gagal membaca file audio.";

    // Ambil hanya data chunk (setelah offset aman)
    $audio_data = substr($wav_data, STEGO_AUDIO_OFFSET);
    $data_len = strlen($audio_data);

    $char_bits = '';
    $message = '';
    
    for ($i = 0; $i < $data_len; $i++) {
        $byte = ord($audio_data[$i]);
        
        // Ekstrak LSB
        $bit = $byte & 1;
        $char_bits .= $bit;
        
        if (strlen($char_bits) == 8) {
            $char = chr(bindec($char_bits));
            $message .= $char;
            $char_bits = '';
            
            // Cek apakah delimiter ditemukan
            if (strpos($message, STEGO_DELIMITER_AUDIO) !== false) {
                return str_replace(STEGO_DELIMITER_AUDIO, '', $message);
            }
        }
    }
    
    return "Error: Pesan tidak ditemukan atau file korup.";
}
?>