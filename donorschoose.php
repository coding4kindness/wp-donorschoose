<?php
/*
Plugin Name: Donors Choose
Plugin URI:  https://github.com/coding4kindness/wp-donorschoose
Description: Plugin for donors choose campaigns
Version:     0.1
Author:      Andrew Roden, Freeman Parks, Josh Miller
License:     Apache 2.0
License URI: http://www.apache.org/licenses/
*/

$donsorsChoosebaseUrl   = "http://api.donorschoose.org/common/json_feed.html";
$defaultApiKey          = "DONORSCHOOSE";
$cacheTtl               = 60 * 5;
$defaultHeadingTemplate = '?><div class="donorschooseHeader"><h2><?php echo count($jsonFeed[\'totalProposals\']); ?> Proposals</h2>
<a href="<?php echo $jsonFeed[\'searchURL\']; ?>">See more</a>
</div> <?';
$defaultProposalTemplate = '?><div class="donorschooseHeaderPropsoal">
<a href="<?php echo $proposal[\'fundURL\']; ?>">Fund <? echo $proposal[\'title\']; ?></a>
<img src="<?php echo $proposal[\'thumbImagURL\']; ?>" />
</div><?';

function curlGetContent($baseUrl, $apiKey, $filters) {
  $apiKeyQs  = http_build_query(array('APIKey' => $apiKey));
  $filtersQs = http_build_query($filters);
  $url       = "$baseUrl?$apiKeyQs&$filtersQs";

  if (function_exists("curl_version")) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_GETT, true);
    $rawData = curl_exec($curl);
  } else {
    $rawData = file_get_contents($url);
  }

  $json = json_decode($rawData, true);
  return $json;
}

/*
function cacheGetContent($baseUrl, $apiKey, $filters) {
  global $cacheTtl;

  $key        = $baseUrl . $apiKey . implode("|", $filters);
  $foundCache = false;
  $content    = apc_fetch($key, $foundCache);

  if ( ! $foundCache ) {
    $content = curlGetContent($baseUrl, $apiKey, $filters);
    apc_store($key, $content ,$cacheTtl);
  }

  return $content;
}
*/

function outputTemplates($jsonFeed, $headingTemplate, $proposalsTemplate) {
  $proposals = $jsonFeed['proposals'];

  $outputString = "<div id='donorschoose'>";

    $outputString .= "<div id='donorschooseSummary'>";
      $outputString .= "<span id='total_proposals'> {$jsonFeed['totalProposals']} Proposals</span>";
      $outputString .= "<a href='{$jsonFeed['searchURL']}' id='all_proposals' target='_blank'>All Proposals</a>";
    $outputString .= "</div>";

    foreach($proposals as $proposal) {
      $outputString .= "<div class='donorschoosePanel'>";
        $outputString .= "<div class='donorschooseDetails'>";
          $outputString .= "<img src='{$proposal['imageURL']}' class='proposal-image' />";
          $outputString .= "<a href='{$proposal['proposalURL']}' class='proposal-title' >{$proposal['title']}</a>";
          $outputString .= "<div class='proposal-desc'>{$proposal['shortDescription']}</div>";
          $outputString .= "<div class='proposal-fulfillment'>{$proposal['fulfillmentTrailer']}</div>";
        $outputString .= "</div>";

        $outputString .= "<div class='donorschooseCallToAction'>";
          $outputString .= "<div class='amount-left'>\${$proposal['costToComplete']} to go!</div>";
          $outputString .= "<div class='donor-count'>{$proposal['numDonors']} donors</div>";
          $outputString .= "<a class='donorschooseFundBtn' href='{$proposal['fundURL']}' target='_blank'>Fund Proposal</a>";
        $outputString .= "</div>";

        $outputString .= "<div class='clearer'></div>";
      $outputString .= "</div>";
    }

    $outputString .= "<a href='{$jsonFeed['searchURL']}' target='_blank'>See more...</a>";

  $outputString .= "</div>";

  return $outputString;
}

function getApiKey() {
  global $defaultApiKey;
  return $defaultApiKey;
}

function donorschoose($atts) {
  global $donsorsChoosebaseUrl, $defaultHeadingTemplate, $defaultProposalTemplate;

  $filters = shortcode_atts(array(
    "max"        => "5",
    "state"      => "", // "IN"
    "community"  => "", // "2021:2"
    "matchingId" => "0" // "20479550"
  ), $atts);

  $apiKey  = getApiKey();
  $content = curlGetContent($donsorsChoosebaseUrl, $apiKey, $filters);

  return outputTemplates($content, $defaultHeadingTemplate, $defaultProposalTemplate);
}

add_shortcode('donorschoose', 'donorschoose');
