<?php
require_once __DIR__ . '/../config/crypto_keys.php';

// --- Fungsi Vigenere Cipher ---
function vigenere_encrypt($text, $key) {
    $keyLen = strlen($key);
    $textLen = strlen($text);
    $encrypted = '';
    for ($i = 0; $i < $textLen; $i++) {
        $char = $text[$i];
        $keyChar = $key[$i % $keyLen];
        $encrypted .= chr((ord($char) + ord($keyChar)) % 256);
    }
    return $encrypted;
}

function vigenere_decrypt($text, $key) {
    $keyLen = strlen($key);
    $textLen = strlen($text);
    $decrypted = '';
    for ($i = 0; $i < $textLen; $i++) {
        $char = $text[$i];
        $keyChar = $key[$i % $keyLen];
        $decrypted .= chr((ord($char) - ord($keyChar) + 256) % 256);
    }
    return $decrypted;
}

// --- Fungsi Super Enkripsi (Vigenere + AES) ---
function super_encrypt($plaintext) {
    $vigenere_ciphertext = vigenere_encrypt($plaintext, VIGENERE_KEY);
    
    $iv_length = openssl_cipher_iv_length(AES_METHOD);
    $iv = openssl_random_pseudo_bytes($iv_length);
    
    $aes_ciphertext = openssl_encrypt(
        $vigenere_ciphertext, 
        AES_METHOD, 
        AES_KEY, 
        OPENSSL_RAW_DATA,
        $iv
    );
    
    // Gabungkan IV (16 bytes) + Ciphertext, lalu Base64 encode
    return base64_encode($iv . $aes_ciphertext);
}

// --- Fungsi Super Dekripsi ---
function super_decrypt($base64_encrypted_data) {
    $data = base64_decode($base64_encrypted_data);
    $iv_length = openssl_cipher_iv_length(AES_METHOD);
    
    $iv = substr($data, 0, $iv_length);
    $aes_ciphertext = substr($data, $iv_length);
    
    $vigenere_ciphertext = openssl_decrypt(
        $aes_ciphertext, 
        AES_METHOD, 
        AES_KEY, 
        OPENSSL_RAW_DATA,
        $iv
    );
    
    // Cek jika dekripsi gagal
    if ($vigenere_ciphertext === false) {
        return "DEKRIPSI GAGAL: Kunci atau data salah.";
    }
    
    return vigenere_decrypt($vigenere_ciphertext, VIGENERE_KEY);
}
?>