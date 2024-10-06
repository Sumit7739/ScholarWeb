<?php
function encryptMessage($plaintext, $encryption_key) {
    // Generate an initialization vector
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    
    // Encrypt the message
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $encryption_key, 0, $iv);
    
    // Return the IV and ciphertext
    return base64_encode($iv . $ciphertext);
}

function decryptMessage($ciphertext, $encryption_key) {
    // Decode the base64 encoded ciphertext
    $ciphertext = base64_decode($ciphertext);
    
    // Extract the IV and actual ciphertext
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($ciphertext, 0, $iv_length);
    $ciphertext = substr($ciphertext, $iv_length);
    
    // Decrypt the message
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $encryption_key, 0, $iv);
}
?>
