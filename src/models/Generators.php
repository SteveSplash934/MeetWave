<?php
class Generators
{
    // Function to generate UUID / GUID (Version 4)
    public static function generate_uuid()
    {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0F) | 0x40); // Set the version to 4
        $data[8] = chr((ord($data[8]) & 0x3F) | 0x80); // Set the variant to RFC 4122
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    // Function to generate random data, encode it and return as a hash ID
    public static function generate_hash_id(): string
    {
        $data = openssl_random_pseudo_bytes(16);
        $hash_id = bin2hex($data);
        return $hash_id;
    }

    // Function to generate random numeric ID
    public static function generate_nid($len = 18): string
    {
        $nid = '';
        for ($i = 0; $i < $len; $i++) {
            $nid .= random_int(0, 9);
        }
        return $nid;
    }

    // Function to generate timestamp ID + random byte
    public static function generate_tid(): string
    {
        $timestamp = time(); // Get UNIX timestamp
        $random_bytes = bin2hex(openssl_random_pseudo_bytes(4));
        return $timestamp . $random_bytes;
    }

    // Function to generate Random Alphanumeric String ID
    public static function generate_rasid($len = 4): string
    {
        $str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        $generated_rasid = '';

        // Cryptographically secure way to generate random alphanumeric string
        for ($i = 0; $i < $len; $i++) {
            $index = random_int(0, strlen($str) - 1);
            $generated_rasid .= $str[$index];
        }

        return $generated_rasid;
    }

    // Function to generate shortened ID
    public static function generate_shid(): string
    {
        $data = openssl_random_pseudo_bytes(4);
        return bin2hex($data);
    }

    // Function to generate base64 encoded ID
    public static function generate_b64id(): string
    {
        $data = openssl_random_pseudo_bytes(16);
        return base64_encode($data);
    }

    // Function to generate the meeting URL ;)
    public static function generate_meeting_url($len = 32): string
    {
        // Use random alphanumeric string and chunk it into 8 character segments
        $generated_meeting_url = 'm-' . Generators::chunk_strings(Generators::generate_rasid($len), 8);
        return $generated_meeting_url;
    }

    // Helper function to chunk string into parts of a given length
    public static function chunk_strings($str, $len): string
    {
        $chunks = [];
        for ($i = 0; $i < strlen($str); $i += $len) {
            $chunks[] = substr($str, $i, $len);
        }

        return implode('-', $chunks); // Join with dashes
    }
}

// Testing the functions
// echo "UUID: " . Generators::generate_uuid() . "\n";
// echo "Hash ID: " . Generators::generate_hash_id() . "\n";
// echo "Numeric ID (NID): " . Generators::generate_nid() . "\n";
// echo "Timestamp + Random ID (TID): " . Generators::generate_tid() . "\n";
// echo "Random Alphanumeric String ID (RASID): " . Generators::generate_rasid(12) . "\n";
// echo "Shortened ID (SHID): " . Generators::generate_shid() . "\n";
// echo "Base64 Encoded ID (B64ID): " . Generators::generate_b64id() . "\n";
// echo "Meeting URL: " . Generators::generate_meeting_url(32) . "\n";
