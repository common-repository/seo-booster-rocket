=== SEO Booster Rocket ===
Contributors: WebSourceGroup
Tags: Booster Rocket, SEO, SEO Boost, Places, Maps, Google, Search Engine Optimization, API, Booster Rocket, US Map, Sitemap, Geography, US State, US City, US County, USA Map, US Geography, Yelp, Fusion API, Facebook API, Graph API
Donate link: https://www.paypal.me/SEOBoosterRocket
Requires at least: 4.9.1
Tested up to: 4.9.4
Requires PHP: 5.6.32
Stable tag: 1.1.1.2
License: GPLv2 or later.
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The SEO Booster Rocket is used to increase your Wordpress Websites SEO Footprint & Functionality. It currently uses Google Places API, Google Maps API, Facebook Graph API & Yelp Fusion API to create over 50,000 additional unique indexable URL's. These URL's are created by picking a search term (ex: Yoga Studios) and allows a user/search engine the ability to navigate to results by State, County & City. This system only supports US Geographies at this time. By using this software you agree that you are using this software at your Own Risk and that this software provides no guarantees. This plugin supports two short codes: [seo_booster_rocket_process_requests] &amp; [seo_booster_rocket_map]

== Description ==
Do you need to provide additional URL's to search engines like Google or Bing? Does your website have less than 20 pages but you'd wish to have 50,000? Is your website's content themed to include searchable Google Map Business Listings for every US State, County, and City? If you've answered yes to any of these questions then this SEO Boster Rocket might put your website into orbit.

The SEO Booster Rocket plugin uses the Google Places API, the Google Maps API, Facebook Graph API & the Yelp Fusion API and merges this data together. It creates a single result for each location record from multiple data sources. This plugin also makes use of a database containing every US State, County, and Town, which can be downloaded after installation. These State, County, Town database increases the searchable footprint of your web site by adding over 50,000 unique web pages! Once you fill in the Places API, Maps API, Yelp API, Facebook Graph API, Search Results URI & set your preferred Keyword you'll be able to submit an additional sitemap to your preferred search engine resulting in a massive increase of indexable pages!

For a live example of this plugin please visit: <a href="https://usayo.ga/find-yoga-studio-by-geography/" target="_blank">USA Yoga</a>

This plugin has only been test with Wordpress version 4.9.1 but is highly likely to work fine with much earlier versions.

Please be aware that we're interested in your feedback so we can deliver you the best, and most useful, plugin possible.

All product names, logos, and brands are property of their respective owners. All company, product and service names used in this website are for identification purposes only. Use of these names, logos, and brands does not imply endorsement.

The Software Is Provided "As Is", Without Warranty Of Any Kind, Express Or Implied, Including But Not Limited To The Warranties Of Merchantability, Fitness For A Particular Purpose And Noninfringement.

== Installation ==

= Traditional Option =

Installation Instructions:
1) Download the plugin and unzip it.
2) Put the ‘SEO-Booster-Rocket’ directory into your wp-content/plugins/ directory.
3) Go to the Plugins page in your WordPress Administration area and click ‘Activate’ next to Search Everything.
4) Go to the Settings > Search Everything and configure it.
5) That’s it. Enjoy searching.

= Smoother Option =
1) Click Install
2) Click Active

= Either Way =
You'll also be required to install both a <a href="https://websourcegroup.com/how-to-get-a-google-places-api-key/" target="_blank">Google Places API Key<a/> & a <a href="https://websourcegroup.com/how-to-get-a-google-maps-api-key/" target="_blank">Google Maps Javascript API Key</a>, <a href="https://websourcegroup.com/how-to-get-a-facebook-graph-access-token/" target="_blank">Facebook Graph API Key</a> & a <a href="https://websourcegroup.com/how-to-get-a-yelp-fusion-api-key/" target="_blank">Yelp Fushion API</a>. You'll also need to hard code a page URI that is used to the Wordpress Rewrite API.

== Changelog ==
= 1.0 = 
* Initial Release. Please note that this plugin is currently in its initial Stable Build.

= 1.0.1 =
* Initial Updates regarding known issues with the initial code base of the wordpress plugin. These including updates to form templates, properly representing search terms & more. More updates are expected within the very near term.

= 1.0.2 =
* Updated url rewrite functionality & misc UX/UI updates.

= 1.1 =
* Integrated Yelp Fusion API & Fused it together with Google Places API. This functionality also has duplicate record detection and merging capabilities. We hope you like the Integration! We have more integrations planned.
* Updated UI/UX slightly - mostly the results table.

= 1.1.0.1 =
* Added a significant amount of error catching code to eliminate warings in error logs
* autocomplete = off for sensitive information in admin forms.

= 1.1.1 =
* Added support for Facebook Graph API
* Updated the duplicate record detection algorithm.
* Added error handling functionality & improvements.
* Minor Template updates.

= 1.1.1.1 = 
* Added a poor mans autocomplete on search form
* updated what URL is used to link for individual records. Now defaults to the source page, website or google map.
* added rel="nofollow" for SEO
* updated bug where search form doesn't point to the correct URI when initially using the State, County & Town navigation.

= 1.1.1.2 =
* Added dynamic page title functionality based on selected Geography. This is for SEO purposes.

== Upgrade Notice ==

= 1.1.1.2 =
SEO Improvements when selecting Geography.

= 1.1.1.1 =
Updated no follow, autocomplete lite, other template data.

= 1.1.1 =
Added support for Facebook Graph API. This updated allows 3 content providers.

= 1.1.0.1 =
A recommended, but not required, update. Mostly error catching & UI/UX updates.

= 1.1 =
Tested with new & existing installations. This upgrade should work fine.

= 1.0.2 =
Added functionality that uses the WP Rewrite API. Please use at your own discretion.

= 1.0.1 =
Tested some bug updates which were incomplete at this phase.

= 1.0 =
Inital realease. This version has some bugs which were updated in future version.

== Screenshots ==

1. Its easy to provide traditional Geographic Searches for your websites specific topic. Simply use the [seo_booster_rocket_map] shortcode. This is great functionality to provide your visitors if your website tailors to a specific topic of interest.

2. For the full SEO Booster Rocket experience you can list every US state & territory by using their full name. Each of these links are clickable and lead into the individual states/territory’s counties. Simply place the [seo_booster_rocket_process_requests] shortcode on your registered URI page.

3. The [seo_booster_rocket_process_requests] shortcode also displays every state/territory using their respective short name. Each of these links are clickable and lead into the individual states/territory’s counties.

4. Once you click a State, or Territory, you’ll then be sent to another listing containing Every County within the geography you selected. In this example, we’ve selected New York.

5. Once you click a County you’ll then be sent to another listing containing Every City within the geography you selected. In this example, we’ve selected Albany County in New York State.

6. Once you click on a specific City/Town you’ll then be sent to a search results page that contains a filled out search form, a map with results for your selected topic.

7. Additionally, the search results page also contains a basic table that contains actionable information so that your visitors/customers can make an educated decision.

8. The Administration page has a location for a Google Place & Google Maps API Key, a hard coded URI for a page containing the page that uses the SEO Booster Rocket shortcode, search cache age & an option to Show Support for the SEO Booster Rocket.

9. Just after activating the SEO Booster Rocket you’ll have to create the required database tables.

10. Once the tables are installed you’ll then have to install the Geographic data. This data is downloaded over an encrypted channel from our servers. (don’t forget to provide a positive review!)

11. Once the Geographic database is installed you’ll then see that, at the time of this writing, contains 63,207 unique geographic records that your site will now be able to use! (Don’t forget to submit the SEO Booster Rocket Site Map to your preferred search engines!)
