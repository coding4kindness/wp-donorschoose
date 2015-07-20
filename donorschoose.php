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
$donsorsChoosebaseUrl = "http://api.donorschoose.org/common/json_feed.html";
$defaultApiKey = "DONORSCHOOSE";
$cacheTtl = 60 * 5;
$defaultHeadingTemplate = '?><div class="donorschooseHeader"><h2><?php echo count($jsonFeed[\'totalProposals\']); ?> Proposals</h2>
<a href="<?php echo $jsonFeed[\'searchURL\']; ?>">See more</a>
</div> <?';
$defaultProposalTemplate = '?><div class="donorschooseHeaderPropsoal">
<a href="<?php echo $proposal[\'fundURL\']; ?>">Fund <? echo $proposal[\'title\']; ?></a>
<img src="<?php echo $proposal[\'thumbImagURL\']; ?>" />
</div><?';

function GetContent($baseUrl, $apiKey, $filters)
{	
	$apiKeyQs = http_build_query(array('APIKey' => $apiKey));
	$filtersQs = http_build_query($filters);

	$url = $baseUrl . "?" . $apiKeyQs . "&" . $filtersQs;

	$rawData = file_get_contents($url);

	$json = json_decode($rawData, true);
	return $json;
}

/*
function cacheGetContent($baseUrl, $apiKey, $filters)
{
	global $cacheTtl;

	$key = $baseUrl . $apiKey . implode("|", $filters);
	$foundCache = false;

	$content = apc_fetch($key, $foundCache);

	if ( ! $foundCache )
	{
		$content = GetContent($baseUrl, $apiKey, $filters);
		apc_store($key, $content ,$cacheTtl);
	}
	return $content;
}
*/

function outputTemplates($jsonFeed, $headingTemplate, $proposalsTemplate) 
{
	$proposals = $jsonFeed['proposals'];

	$outputString = "<div class='donorschoose'>";

	$outputString .= "<div class='donorschooseSummary'>";
		$outputString .= "<h2> {$jsonFeed['totalProposals']} Proposals</h2>";
		$outputString .= "<a href='{$jsonFeed['searchURL']}'>All Proposals</a>";
	$outputString .= "</div>";

	foreach ($proposals as $proposal)
	{ 
		$outputString .= "<div class='donorschoosePanel'>";
			$outputString .= "<div class='donorschooseDetails'>";
				$outputString .= "<div><img src='{$proposal['imageURL']}' /></div>";
				$outputString .= "<a href='{$proposal['proposalURL']}'>{$proposal['title']}</a>";
				$outputString .= "<p>{$proposal['shortDescription']}</p>";
				$outputString .= "<p>{$proposal['fulfillmentTrailer']}</p>";
			$outputString .= "</div>";
			$outputString .= "<div class='donorschooseCallToAction'>";
				$outputString .= "<h3>\${$proposal['costToComplete']} to go!</h3>";
				$outputString .= "<p>\${$proposal['numDonors']}</p>";
				$outputString .= "<p><a class='donorschooseFundBtn' href='{$proposal['fundURL']}>Fund Proposal</a></p>";
			$outputString .= "</div>";
		$outputString .= "</div>";
	}

	$outputString .= "<a href='{$jsonFeed['searchURL']}'>See more...</a>";

	$outputString .= "</div>";
	
	return $outputString;
}

function getApiKey()
{
	global $defaultApiKey;
	return $defaultApiKey;
}


function donorschoose($atts)
{
	global $donsorsChoosebaseUrl, $defaultHeadingTemplate, $defaultProposalTemplate;

	$filters = shortcode_atts( array(
		"max" => "5",
		"state"=>"", // ex: "IN"
		"community"=>"", // ex: "2021:2"
		"matchingId"=> "" // ex: "20479550"
	), $atts);

	$apiKey = getApiKey();

	$content = GetContent($donsorsChoosebaseUrl, $apiKey, $filters);

	echo outputTemplates($content, $defaultHeadingTemplate, $defaultProposalTemplate);
}


add_shortcode('donorschoose', 'donorschoose');

?>

