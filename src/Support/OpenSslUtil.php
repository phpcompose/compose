<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2017-11-09
 * Time: 10:04 AM
 */

namespace Compose\Support;


class OpenSslUtil
{
    /**
     * @param $length
     * @return string
     */
    static public function generateKey($length)
    {
        return base64_encode(openssl_random_pseudo_bytes($length));
    }

    /**
     * @param $data
     * @param $key
     * @return string
     */
    static public function encrypt($data, $key)
    {
        // Remove the base64 encoding from our key
        $encryption_key = base64_decode($key);
        // Generate an initialization vector
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        // Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
        // The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * @param $data
     * @param $key
     * @return string
     */
    static public function decrypt($data, $key)
    {
        // Remove the base64 encoding from our key
        $encryption_key = base64_decode($key);
        // To decrypt, split the encrypted data from our IV - our unique separator used was "::"
        $parts = explode('::', base64_decode($data), 2);
        if(count($parts) != 2) return null;
        list($encrypted_data, $iv) = $parts;
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    }
}