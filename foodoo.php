<?php

/**
 * Plugin Name: FooDoo
 * Plugin URI: https://cromian.com/foodoo/plugins/wordpress/foodoo.zip
 * Description: Adds the FooDoo link icon to all posts/recipes, for getting ingredients in a shopping list in the FooDoo mobile app.
 * Tags: FooDoo, Foodoo, Food, App, Mobile, Ingredients, Shopping list, Grocery, iPhone, Android, Partner
 * Version: 1.0.0
 * Author: Cromian
 * Author URI: http://cromian.com
 * License: GPL2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

/*  2015  Cromian  (email : contact@cromian.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Adds the FooDoo link to posts that contains a recipe,
 * which is possible to find in the FooDoo app
 *
 * @param $content The content of the post
 * @return mixed|string Returns the modified content
 */
function add_foodoo_link($content) {
  // If Wordpress is showing only a single post then insert the FooDoo link
  if (is_single()) {

    // Get the URL for this post
    global $wp;
    $currentURL = home_url(add_query_arg(array(),$wp->request));

    // TESTING URL
    // $currentURL = "http://www.angsarap.net/2015/03/13/mohinga/";

    // First check if this post is a recipe that we have in the app
    $url = "https://cromian.com/mealChaser/api/mealChaserAPI/public/v1/recipe/id?url=$currentURL";

    // Call the FooDoo API to find out if this recipe is in the app
    $c = curl_init($url);
    //Set this option to stop CURL from dumping results
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($c);
    curl_close ($c);

    $recipeID = intval($result);
    // If the recipeID is 0, then this recipe is not in the app
    if ($recipeID == 0) {
      return $content;
    }

    $altText = array(
      "en" => "Get the shopping list with the FooDoo app",
      "da" => "Få indkøbslisten på din mobil/tablet"
    );

    $ingredientsText = array(
      "en" => "Ingredients",
      "da" => "Ingredienser"
    );

    // Get the current locale
    $locale = get_locale();
    if ($locale == "da_DK") {
      // Danish
      $languageCode = "da";
    } else {
      // English is default language
      $languageCode = "en";
    }

    // Ingredients headline is the text that the blog would write above the Ingredients - localized
    $ingredientsHeadline = $ingredientsText[$languageCode];

    if (strpos($content, $ingredientsHeadline) !== false) {
      // Content contains the Ingredients headline
      $containsIngHeadline = true;
      $marginStyle = "margin-left: 20px;";
    } else {
      // It does not contain it
      $containsIngHeadline = false;
      $marginStyle = "margin-top: 20px;";
    }

    $foodooLink = "<script>function redirect(){window.open('//cromian.com/foodoo/applink/app_redirect.php?referer=' + document.URL, '_blank');}</script><img alt='".$altText[$languageCode]."' style='cursor: pointer; $marginStyle' src='//cromian.com/foodoo/applink/graphics/link_$languageCode.png' onclick='redirect();'>";

    if ($containsIngHeadline) {
      $content = preg_replace("/$ingredientsHeadline/", $ingredientsHeadline . " " . $foodooLink, $content);
    } else {
      $content .= "<br>" . $foodooLink;
    }
  }
  return $content;
}


add_filter ('the_content', 'add_foodoo_link', 0);