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
        // Check if decoding was successful and if the API returned a 200 status code
        if ($data && isset($data['status']['code']) && $data['status']['code'] === 200 && isset($data['results']) && !empty($data['results'])) {
            return $data['results'][0]['formatted'];
        } else {
            // Log API error if status code is not 200 or results are empty
            $errorMessage = "OpenCage Geocoding API Error: ";
            if ($data && isset($data['status']['message'])) {
                $errorMessage .= $data['status']['message'];
            } else {
                $errorMessage .= "Unknown error or invalid response structure.";
            }
            error_log($errorMessage);
        }
    } else {
        error_log("OpenCage Geocoding API: No response from cURL.");
    }
    
    // Fallback if API fails or returns no data or an error
    return "{$latitude}, {$longitude}";
}

?>
