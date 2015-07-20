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

  echo "<div id='donorschoose'>";

    echo "<div id='donorschooseSummary'>";
      echo "<span id='total_proposals'> {$jsonFeed['totalProposals']} Proposals</span>";
      echo "<a href='{$jsonFeed['searchURL']}' id='all_proposals' target='_blank'>All Proposals</a>";
    echo "</div>";

    foreach($proposals as $proposal) {
      echo "<div class='donorschoosePanel'>";
        echo "<div class='donorschooseDetails'>";
          echo "<img src='{$proposal['imageURL']}' class='proposal-image' />";
          echo "<a href='{$proposal['proposalURL']}' class='proposal-title' >{$proposal['title']}</a>";
          echo "<span class='proposal-desc'>{$proposal['shortDescription']}</span>";
          echo "<span class='proposal-fulfillment'>{$proposal['fulfillmentTrailer']}</span>";
        echo "</div>";

        echo "<div class='donorschooseCallToAction'>";
          echo "<h3>\${$proposal['costToComplete']} to go!</h3>";
          echo "<p>\${$proposal['numDonors']}</p>";
          echo "<p><a class='donorschooseFundBtn' href='{$proposal['fundURL']}'>Fund Proposal</a></p>";
        echo "</div>";
      echo "</div>";
    }

    echo "<a href='{$jsonFeed['searchURL']}'>See more...</a>";

  echo "</div>";
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

  outputTemplates($content, $defaultHeadingTemplate, $defaultProposalTemplate);
}

add_shortcode('donorschoose', 'donorschoose');
