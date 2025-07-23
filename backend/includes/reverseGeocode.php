<?php
// backend/includes/reverseGeocode.php

function getAddressFromCoordinates($latitude, $longitude) {
    $apiKey = '920278be1d4d4f0aa2542e3aaf52b5e9';
    $url = "https://api.opencagedata.com/geocode/v1/json?q={$latitude}+{$longitude}&key={$apiKey}&pretty=1";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['results']) && !empty($data['results'])) {
            return $data['results'][0]['formatted'];
        }
    }
    
    // Fallback if API fails or returns no data
    return "{$latitude}, {$longitude}";
}

?>
