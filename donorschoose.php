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
$defaultApiKey = "DONORSCHOOSE";
$cacheTtl = 60 * 5;
$defaultHeadingTemplate = "?><div class='donorschooseHeader'><h2><?=count($jsonFeed['totalProposals'])?> Proposals</h2>
<a href='<?=$jsonFeed['searchURL']?>'>Open Results</a>
</div> <?";
$defaultProposalTemplate = "?><div class='donorschooseHeaderPropsoal'>
<a href='<?=$proposal['fundURL']?>'>Fund <?=$proposal['title']?></a>
<img src='<?=$proposal['thumbImagURL']?>' />
</div><?";

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

function getContent($baseUrl, $apiKey, $filters)
{
	$key = $baseUrl . $apiKey . implode("|", $filters);
	$foundCache = false;

	$content = apc_fetch($key, $foundCache);

	if ( ! $foundCache )
	{
		$content = curlGetContent($baseUrl, $apiKey, $filters);
		apc_store($key, $content ,$cacheTtl);
	}
	return $content;
}

function outputTemplates($jsonFeed, $headingTemplate, $proposalsTemplate) 
{
	$proposals = $jsonFeed['proposals'];

	eval( $headingTemplate );

	for ($proposals as $proposal)
	{
		eval( $proposalsTemplate );
	}
}

function getApiKey()
{
	return $defaultApiKey;
}


function donorschoose()
{
	$filters = array(
		"max"=>"10",
		"state"=>"IN",
		"community"=>"2021:2"
	);

	$apiKey = getApiKey();
	$content = getContent($baseUrl, $apiKey, $filters);
	outputTemplates($content, $defaultHeadingTemplate, $defaultProposalTemplate);
}

if ( function_exists('add_action') )
{
	add_action('init', 'donorschoose');
}

if ( function_exists('add_shortcode') )
{
	add_action('donorschoose', 'add_shortcode');
}

?>

