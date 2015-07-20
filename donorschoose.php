<?php
/*
Plugin Name: Donors Choose
Plugin URI:  https://github.com/coding4kindness/wp-donorschoose
Description: Plugin for donors choose campaigns
Version:     0.1
Author:      Andrew Roden, Freeman Parks
License:     Apache 2.0
License URI: http://www.apache.org/licenses/
*/
$donsorsChoosebaseUrl = "http://api.donorschoose.org/common/json_feed.html"

function curlGetContent($baseUrl, $apiKey, $filters)
{
	$apiKeyQs = http_build_query(array('APIKey' => $apiKey));
	$filtersQs = http_build_query($filters);

	$url = $baseUrl . "?" . $apiKeyQs . "&" . $filtersQs;

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_GETT, true);
	$rawData = curl_exec($curl);

	$json = json_decode($rawData, true);
	return $json;
}

?>

