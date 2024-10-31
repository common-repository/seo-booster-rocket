<?php
/**
 * Plugin Name: SEO Booster Rocket
 * Plugin URI: https://websourcegroup.com/
 * Description: This plugin provides over 50,000 unique indexable web pages to your Wordpress Website! Uses Google Places API, Google Maps API & Yelp Fusion API to create an industry focused data driven user experience.
 * Version: 1.1.1.2
 * Author: Web Source Group
 * Author URI: http://websourcegroup.com/seo-booster-rocket-wordpress-plugin-rocket-boost-seo-results/
 * License: GPL2
 */

defined( 'ABSPATH' ) or die( 'Direct access to this file is prohibited.' );

if(!class_exists('Smarty')) {
	include(__DIR__.'/smarty/libs/Smarty.class.php');
}

class SEO_Booster_Rocket_Sitemap {
        private $db;
        function __construct() {
                global $wpdb;
                $this->db = $wpdb;
                $server_scheme='http';
                if($_SERVER['HTTPS']=='on') {
                        $server_scheme='https';
                }
                $this->geo_path = $server_scheme.'://'.$_SERVER['SERVER_NAME'].get_option('booster-rocket-maps-uri');
        }
        private function cleanVariable($var) {
		return sanitize_text_field($var);
        }
        public function retCitiesShort() {
                $results = Array();
                $result = $this->db->get_results("SELECT DISTINCT city,county,state_short FROM wsg_seo_booster_rocket_geo ORDER BY city");
                foreach($result as $res) {
                        array_push($results,array('name'=>$res->city,'url'=>$this->geo_path.$res->state_short."/".$res->county."/".$res->city));
                }
                return $results;
        }
        public function retCities() {
                $results = Array();
                $result = $this->db->get_results("SELECT DISTINCT city,county,state_full FROM wsg_seo_booster_rocket_geo ORDER BY city");
                foreach($result as $res) {
                        array_push($results,array('name'=>$res->city,'url'=>$this->geo_path.$res->state_full."/".$res->county."/".$res->city));
                }
                return $results;
        }
        public function retSiteMap() {
                $retval='<?xml version="1.0" encoding="UTF-8"'."?".'><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

                foreach($this->retCities() as $city) {
                        $retval.='<url><loc>'.$city['url'].'</loc><changefreq>weekly</changefreq><priority>0.69</priority></url>'."\n";
                }
                foreach($this->retCitiesShort() as $city) {
                        $retval.='<url><loc>'.$city['url'].'</loc><changefreq>weekly</changefreq><priority>0.69</priority></url>'."\n";
                }
                $retval.='</urlset>';
                return $retval;
        }
}
function display_rocket_booster_sitemap() {
	if(isset($_GET['seo-booster-rocket-sitemap'])) {
		$sitemap = new SEO_Booster_Rocket_Sitemap;
		header("Content-type: text/xml");
		print $sitemap->retSiteMap();
		exit;
	}
}
add_action( 'init', 'display_rocket_booster_sitemap' );

class SEO_Booster_Rocket_HTMLify {
	private $url_path;
	private $smarty;
	function __construct() {
		$this->url_path = '';
		$this->smarty = new Smarty();
		$this->smarty->setTemplateDir(__DIR__.'/templates/');
		$this->smarty->setCompileDir(__DIR__.'/templates_c');
	}
	public function process($title,$results,$cells_per_row = 4) {
		if(is_array($results)) {
			$this->smarty->assign('title',$title);
			$this->smarty->assign('mod',intval($cells_per_row));
			$this->smarty->assign('results',array_filter($results));
		        if(get_option('booster-rocket-powered-by')==1) {
				$this->smarty->assign('powered_by','Powered by <a href="https://wordpress.org/plugins/seo-booster-rocket/" target="_blank">SEO Booster Rocket</a>, developed by <a href="https://websourcegroup.com/" target="_blank"><img src="'.plugin_dir_url(__FILE__).'/images/Web-Source-Group-Logo.png" alt="Web Source Group - We Build Businesses with Technology" /></a><br />');
			}
			if(get_option('booster-rocket-search-term')) {
				$this->smarty->assign('search_term',get_option('booster-rocket-search-term'));
			}
			return $this->smarty->fetch(__DIR__.'/templates/htmlify.tpl');
		}
		return '';
	}
}

class SEO_Booster_Rocket_Geography {
	private $db;
	private $geo_path;
	private $seo_db;
	function __construct() {
		global $wpdb;
                $this->db = $wpdb;
		$this->geo_path = get_option('booster-rocket-maps-uri');
		$this->seo_db = new SEO_Booster_Rocket_DB();
	}
        private function cleanVariable($var) {
		return sanitize_text_field($var);
        }

	public function retStateList() {
		return array_merge($this->retStatesShort(),$this->retStatesFull());
	}

	public function retCityList() {
		$results = Array();
                $result = $this->seo_db->db->get_results("SELECT DISTINCT city FROM ".$this->seo_db->ret_geo_table()." ORDER BY city");
                foreach($result as $res) {
                        array_push($results,array('name'=>$res->city));
                }
                return $results;
        }

	public function retStateShortName($state_long) {
		$state_long = $this->cleanVariable($state_long);
		$result = $this->seo_db->db->get_results("SELECT DISTINCT state_short FROM ".$this->seo_db->ret_geo_table()." WHERE state_full = '$state_long'");
		if(isset($result[0]->state_short)) {
			return $result[0]->state_short;
		}else{
			print mysql_error();
		}
		return FALSE;
	}
	public function retStatesFull() {
		$results = Array();
		$result = $this->seo_db->db->get_results("SELECT DISTINCT (state_full) FROM ".$this->seo_db->ret_geo_table()." ORDER BY state_full");	
		foreach($result as $res) {
			array_push($results,array('name'=>$res->state_full,'url'=>$this->geo_path.$res->state_full));
		}
		return $results;
	}
	public function retStatesShort() {
		$results = Array();
		$result = $this->seo_db->db->get_results("SELECT DISTINCT (state_short) FROM ".$this->seo_db->ret_geo_table()." ORDER BY state_short");	
		foreach($result as $res) {
			array_push($results,array('name'=>$res->state_short,'url'=>$this->geo_path.$res->state_short));
		}
		return $results;
	}
	public function retCounties() {
		$results = Array();
		$result = $this->seo_db->db->get_results("SELECT DISTINCT county,state_full FROM ".$this->seo_db->ret_geo_table()." ORDER By county");
		foreach($result as $res) {
			$res->county=ucwords(strtolower($res->county));
			array_push($results,array('name'=>$res->county,'url'=>$this->geo_path.$res->state_full."/".$res->county));
		}
		return $results;
	}
	public function retCountiesByStateFull($state) {
		$results = Array();
		$state = $this->cleanVariable($state);
		$result = $this->seo_db->db->get_results("SELECT DISTINCT county,state_full FROM ".$this->seo_db->ret_geo_table()." WHERE state_full = '$state' ORDER BY county");
		foreach($result as $res) {
			$res->county=ucwords(strtolower($res->county));
			array_push($results,array('name'=>$res->county,'url'=>$this->geo_path.$res->state_full."/".$res->county));
		}
		return $results;
	}
	public function retCountiesByStateShort($state) {
		$results = Array();
		$state = $this->cleanVariable($state);
		$result = $this->seo_db->db->get_results("SELECT DISTINCT county,state_short FROM ".$this->seo_db->ret_geo_table()." WHERE state_short = '$state' ORDER BY county");
		foreach($result as $res) {
			$res->county=ucwords(strtolower($res->county));
			array_push($results,array('name'=>$res->county,'url'=>$this->geo_path.$res->state_short."/".$res->county));
		}
		return $results;
	}
	public function retCities() {
		$results = Array();
		$result = $this->seo_db->db->get_results("SELECT DISTINCT city,county,state_full FROM ".$this->seo_db->ret_geo_table()." ORDER BY city");
		foreach($result as $res) {
			array_push($results,array('name'=>$res->city,'url'=>$this->geo_path.$res->state_full."/".$res->county."/".$res->city));
		}
		return $results;
	}
	public function retCitiesByStateFull($state) {
		$results = Array();
		$state = $this->cleanVariable($state);
		$result = $this->seo_db->db->get_results("SELECT DISTINCT city,county,state_full FROM ".$this->seo_db->ret_geo_table()." WHERE state_full = '$state' ORDER BY city");
		foreach($result as $res) {
			array_push($results,array('name'=>$res->city,'url'=>$this->geo_path.$res->state_full."/".$res->county."/".$res->city));
		}
		return $results;
	}
	public function retCitiesByStateShort($state) {
		$results = Array();
		$state = $this->cleanVariable($state);
		$result = $this->seo_db->db->get_results("SELECT DISTINCT city,county,state_short FROM ".$this->seo_db->ret_geo_table()." WHERE state_short = '$state' ORDER BY city");
		foreach($result as $res) {
			array_push($results,array('name'=>$res->city,'url'=>$this->geo_path.$res->state_short."/".$res->county."/".$res->city));
		}
		return $results;
	}
	public function retCitiesByCounty($county) {
		$results = Array();
		$county = $this->cleanVariable($county);
		$result = $this->seo_db->db->get_results("SELECT DISTINCT city,county,state_full FROM ".$this->seo_db->ret_geo_table()." WHERE county = '$county' ORDER BY city");
		foreach($result as $res) {
			array_push($results,array('name'=>$res->city,'url'=>$this->geo_path.$res->state_full."/".$res->county."/".$res->city));
		}
		return $results;
	}
	public function retCitiesByCountyShortState($county,$state) {
		$results=Array();
		$county=$this->cleanVariable($county);
		$state=$this->cleanVariable($state);
		$result = $this->seo_db->db->get_results("SELECT DISTINCT city,county,state_short FROM ".$this->seo_db->ret_geo_table()." WHERE county = '$county' AND state_short = '$state' ORDER BY city");
		foreach($result as $res) {
			array_push($results,array('name'=>$res->city,'url'=>$this->geo_path.$res->state_short."/".$res->county."/".$res->city));
		}
		return $results;
	}
	public function retCitiesByCountyFullState($county,$state) {
		$results=Array();
		$county=$this->cleanVariable($county);
		$state=$this->cleanVariable($state);
		$result = $this->seo_db->db->get_results("SELECT DISTINCT city,county,state_full FROM ".$this->seo_db->ret_geo_table()." WHERE county = '$county' AND state_full = '$state' ORDER BY city");
		foreach($result as $res) {		
			array_push($results,array('name'=>$res->city,'url'=>$this->geo_path.$res->state_full."/".$res->county."/".$res->city));
		}
		return $results;
	}
}

function ret_seo_cities( $atts ) {
	$geo = new SEO_Booster_Rocket_Geography();
	$htmlify = new SEO_Booster_Rocket_HTMLify();
	if(isset($atts['state']) && isset($atts['county']) && strlen($atts['state']) >= 2) {
		if(strlen($atts['state']) == 2) {
			return $htmlify->process("City Filtered by Short State & County: ".ucwords(strtolower(htmlspecialchars($atts['county']))).", ".htmlspecialchars($atts['state']),$geo->retCitiesByCountyShortState($atts['county'],$atts['state']));
		}else{
			return $htmlify->process("City Filtered by Full State & County: ".ucwords(strtolower(htmlspecialchars($atts['county']))).", ".htmlspecialchars($atts['state']),$geo->retCitiesByCountyFullState($atts['county'],$atts['state']));
		}
	}elseif(isset($atts['state']) && strlen($atts['state']) >= 2) {
		if(strlen($atts['state']) == 2) {
			if(isset($atts['array']) && $atts['array']==TRUE) {
				return $geo->retCitiesByStateShort($atts['state']);
			}else{
				return $htmlify->process("City Filtered by Short State Name: ".htmlspecialchars($atts['state']),$geo->retCitiesByStateShort($atts['state']));
			}
		}else{
			if(isset($atts['array']) && $atts['array']==TRUE) {
				$geo->retCitiesByStateFull($atts['state']);
			}else{
				return $htmlify->process("City Filtered by Full State Name: ".ucwords(strtolower(htmlspecialchars($atts['state']))),$geo->retCitiesByStateFull($atts['state']));
			}
		}
	}elseif(isset($atts['county'])) {
		if(isset($atts['array']) && $atts['array']==TRUE) {
			return $geo->retCitiesByCounty($atts['county']);
		}else{
			return $htmlify->process("City Filtered by County: ".ucwords(strtolower(htmlspecialchars($atts['county']))),$geo->retCitiesByCounty($atts['county']));
		}
	}
	if(isset($atts['array']) && $atts['array']==TRUE) {
		return $geo->retCities();
	}else{
		return $htmlify->process("Cities",$geo->retCities());
	}
}
function ret_seo_counties( $atts ) {
	$geo = new SEO_Booster_Rocket_Geography();
	$htmlify = new SEO_Booster_Rocket_HTMLify();
	if(isset($atts['state']) && strlen($atts['state']) >= 2) {
		if(strlen($atts['state']) == 2) {
			return $htmlify->process("Counties Filtered by Short State Name: ".htmlspecialchars($atts['state']),$geo->retCountiesByStateShort($atts['state']));
		}else{
			return $htmlify->process("Counties Filtered by Full State Name: ".ucwords(strtolower(htmlspecialchars($atts['state']))),$geo->retCountiesByStateFull($atts['state']));
		}
	}
	return $htmlify->process("Counties",$geo->retCounties());
}
function ret_seo_state( $atts ) {
	$geo = new SEO_Booster_Rocket_Geography();
	$htmlify = new SEO_Booster_Rocket_HTMLify();
	if(isset($atts['full']) && $atts['full'] == "true") {
		return $htmlify->process("Full State Name",$geo->retStatesFull());
	}
	return $htmlify->process("Short State Name",$geo->retStatesShort(),10);
}
function ret_seo_long_state_to_short($state) {
	$geo = new SEO_Booster_Rocket_Geography();
	return $geo->retStateShortName($state);
}

add_filter('query_vars', 'register_query_vars');
function register_query_vars($public_query_vars) {
	$public_query_vars[] = "state";
	$public_query_vars[] = "county";
	$public_query_vars[] = "city";
	$public_query_vars[] = "town";
	return $public_query_vars;
}

add_action('init','seo_booster_rocket_rewrite_rule');
function seo_booster_rocket_rewrite_rule() {
	if($_SERVER['REQUEST_URI'] != esc_attr(get_option('booster-rocket-maps-uri'))) { return false; }
	global $wp_rewrite;
	$wp_rewrite->flush_rules( false );

	$post_id=url_to_postid(esc_attr(get_option('booster-rocket-maps-uri')));
	$post_page='index.php?page_id='.intval($post_id);

	add_rewrite_tag('%state%','([^&]+)');
	add_rewrite_tag('%county%','([^&]+)');
	add_rewrite_tag('%city%','([^&]+)');

	//STATE
		$state_match=ltrim(esc_attr(get_option('booster-rocket-maps-uri')),'/').'([A-Za-z\ \+]+)/?$';
		$state_target=$post_page.'&state=$matches[1]';
		add_rewrite_rule($state_match,$state_target,'top');

	//COUNTY
		$county_match=ltrim(esc_attr(get_option('booster-rocket-maps-uri')),'/').'([A-Za-z\ \+]+)/([A-Za-z\ \+]+)/?$';
		$county_target=$post_page.'&state=$matches[1]&county=$matches[2]';
		add_rewrite_rule($county_match,$county_target,'top');


	//CITY
		$city_match=ltrim(esc_attr(get_option('booster-rocket-maps-uri')),'/').'([A-Za-z\ \+]+)/([A-Za-z\ \+]+)/([A-Za-z\ \+]+)/?$';
		$city_target=$post_page.'&state=$matches[1]&county=$matches[2]&city=$matches[3]';
		add_rewrite_rule($city_match,$city_target,'top');

	//print $state_match.' '.$state_target."<br />";
	//print $county_match.' '.$county_target."<br />";
	//print $city_match.' '.$city_target."<br />";
	#print "AAAAAAA";
}

//add_action('template_redirect','disable_wpseo');
function disable_wpseo() { //yoast seo conflicts with updating title. This doesn't work to correct this.
	$uri=esc_attr(get_option('booster-rocket-maps-uri'));
	if(substr($_SERVER['REQUEST_URI'],0,strlen($uri)) == $uri) {
		global $wpseo_front;
		if(defined($wpseo_front)){
			remove_action('wp_head',array($wpseo_front,'head'),1);
		}else{
			$wp_thing = WPSEO_Frontend::get_instance();
			remove_action('wp_head',array($wp_thing,'head'),1);
		}
	}
}

add_filter('pre_get_document_title','update_page_title');
function update_page_title() {
	global $wp_query;
	$uri=esc_attr(get_option('booster-rocket-maps-uri'));
	if(substr($_SERVER['REQUEST_URI'],0,strlen($uri)) == $uri) {
		$term=esc_attr(get_option('booster-rocket-search-term'));
		if(isset($wp_query->query_vars['city']) && isset($wp_query->query_vars['county']) && isset($wp_query->query_vars['state'])) {
			return "Find a ".$term." in ".htmlspecialchars($wp_query->query_vars['city']).", ".htmlspecialchars($wp_query->query_vars['state']); //add a place for county.
		}elseif(isset($wp_query->query_vars['state']) && isset($wp_query->query_vars['town'])) {
			return "Find a ".$term." in ".htmlspecialchars($wp_query->query_vars['town']).", ".htmlspecialchars($wp_query->query_vars['state']);
		}elseif(isset($wp_query->query_vars['county']) && isset($wp_query->query_vars['state'])) {
			return "Find a ".$term." in ".htmlspecialchars($wp_query->query_vars['county'])." County, ".htmlspecialchars($wp_query->query_vars['state']);
		}elseif(isset($wp_query->query_vars['state'])) {
			return "Find a ".$term." in ".htmlspecialchars($wp_query->query_vars['state']);
		}
		return $wp_query->post->post_title;
	}
}

add_shortcode('seo_booster_rocket_process_requests','seo_booster_rocket_process_requests');
function seo_booster_rocket_process_requests() {
	global $wp_query;
	if(isset($wp_query->query_vars['city']) && isset($wp_query->query_vars['county']) && isset($wp_query->query_vars['state'])) {
		return seo_booster_rocket_map(array());
	}elseif(isset($wp_query->query_vars['county']) && isset($wp_query->query_vars['state'])) {
		return ret_seo_cities(array('state'=>htmlspecialchars($wp_query->query_vars['state']),'county'=>htmlspecialchars($wp_query->query_vars['county'])));
	}elseif(isset($wp_query->query_vars['state']) && isset($wp_query->query_vars['town'])) {
		return seo_booster_rocket_map(array());
	}elseif(isset($wp_query->query_vars['state'])) {
		return ret_seo_counties(array('state'=>htmlspecialchars($wp_query->query_vars['state'])));
	}else{
		return $retval.ret_seo_state(array('full'=>"true")).ret_seo_state(array());
	}
}

class SEO_Booster_Rocket_Places {
	private $places_api_key;
	private $maps_api_key;
	private $yelp_api_key;
	private $facebook_api_key;
	private $seo_db;
	private $max_yelp_distance;
	private $text_similarity_tolerance;
	private $geography_tolerance;
	private $base_facebook_url;
	private $place_facebook_url;
	private $facebook_place_fields;
	private $max_facebook_distance;

	function __construct($town="New York",$state="NY") {
		$this->seo_db = new SEO_Booster_Rocket_DB();
		$this->smarty = new Smarty();
		$this->smarty->setTemplateDir(__DIR__.'/templates/');
		$this->smarty->setCompileDir(__DIR__.'/templates_c');
		$this->town = $town;
		$this->state = $state;
		$this->places_api_key = get_option('booster-rocket-places-api-key');
		$this->maps_api_key = get_option('booster-rocket-maps-api-key');
		$this->yelp_api_key = get_option('booster-rocket-yelp-api-key');
		$this->facebook_api_key = get_option('booster-rocket-facebook-api-key');
		$this->max_yelp_distance=10000;
		$this->max_facebook_distance=10000;
		$this->base_google_url = "https://maps.googleapis.com/maps/api/place/textsearch/json?key=".$this->places_api_key."&";
		$this->base_yelp_url = "https://api.yelp.com/v3/businesses/search?";
		$this->base_facebook_url = "https://graph.facebook.com/v2.11/search?type=place&distance=".$this->max_facebook_distance."&access_token=".urlencode(esc_attr($this->facebook_api_key))."&";
		$this->place_facebook_url = "https://graph.facebook.com/v2.11/";//{place ID}?fields={place information}";
		$this->facebook_place_fields = "name,hours,location,overall_star_rating,rating_count,phone,picture,website,link";
		$this->text_similarity_tolerance=12;
		$this->geography_tolerance=0.0005;
		$this->location_names=Array();
		if(is_int(get_option( 'booster-rocket-cache-age' ))) {
			$this->cache_age=intval(get_option( 'booster-rocket-cache-age' )); //UPDATE
		}else{
			$this->cache_age=7;
		}
	}

	public function ret_maps_api_key() {
		if($this->maps_api_key==FALSE || strlen($this->maps_api_key)==0) {
			return FALSE;
		}
		return $this->maps_api_key;
	}
	public function retLocationCount() {
		return count($this->location_types);
	}
        private function cleanVariable($var) {
		return sanitize_text_field($var);
        }
	private function ret_yelp_api_key() {
		return $this->yelp_api_key;
	}

	private function retYelpSearchCache($state,$city) {
		$state=$this->cleanVariable($state);
		$city=$this->cleanVariable($city);
		if(strlen($state) >= 2 && strlen($city) > 2) {
			if(strlen($state) > 2) {
				$state = $this->retStateShortName($state);
			}
			$result = $this->seo_db->db->get_results("SELECT json_response FROM ".$this->seo_db->ret_yelp_cache_table()." WHERE state = '$state' AND city = '$city' AND DATEDIFF(date,NOW()) < ".$this->cache_age); // add cache expiration mechanism.

			if(isset($result[0])) {
				return $result[0]->json_response;
			}
		}
		return FALSE;
	}

	private function retFacebookSearchCache($state,$city) {
		$state=$this->cleanVariable($state);
		$city=$this->cleanVariable($city);
		if(strlen($state) >= 2 && strlen($city) > 2) {
			if(strlen($state) > 2) {
				$state = $this->retStateShortName($state);
			}
			$result = $this->seo_db->db->get_results("SELECT json_response FROM ".$this->seo_db->ret_facebook_cache_table()." WHERE state = '$state' AND city = '$city' AND DATEDIFF(date,NOW()) < ".$this->cache_age); // add cache expiration mechanism.

			if(isset($result[0])) {
				return $result[0]->json_response;
			}
		}
		return FALSE;
	}

	private function retGoogleSearchCache($state,$city) {
		$state=$this->cleanVariable($state);
		$city=$this->cleanVariable($city);
		if(strlen($state) >= 2 && strlen($city) > 2) {
			if(strlen($state) > 2) {
				$state = $this->retStateShortName($state);
			}
			$result = $this->seo_db->db->get_results("SELECT json_response FROM ".$this->seo_db->ret_google_cache_table()." WHERE state = '$state' AND city = '$city' AND DATEDIFF(date,NOW()) < ".$this->cache_age); // add cache expiration mechanism.

			if(isset($result[0])) {
				return $result[0]->json_response;
			}
		}
		return FALSE;
	}
	
	/*
	private function delGoogleSearchCache($state,$city) {
		$state=$this->cleanVariable($state);
		$city=$this->cleanVariable($city);
		if($this->seo_db->db->query("DELETE FROM ".$this->seo_db->ret_google_cache_table()." WHERE state = '$state' AND city = '$city'")) {
			return TRUE;
		}
		return FALSE;
	}
	*/ // this function isn't used at this time

	private function addYelpSearchCache($state,$city,$json_response) {
		$state=$this->cleanVariable($state);
		$city=$this->cleanVariable($city);
		$json_response=$this->seo_db->db->_real_escape($json_response);
		if($this->seo_db->db->query("INSERT INTO ".$this->seo_db->ret_yelp_cache_table()." VALUES('$state','$city','$json_response',NOW())")) {
			return TRUE;
		}else{
			print mysql_error();
		}
		return FALSE;
	}
	private function addFacebookSearchCache($state,$city,$json_response) {
		$state=$this->cleanVariable($state);
		$city=$this->cleanVariable($city);
		$json_response=$this->seo_db->db->_real_escape($json_response);
		if($this->seo_db->db->query("INSERT INTO ".$this->seo_db->ret_facebook_cache_table()." VALUES('$state','$city','$json_response',NOW())")) {
			return TRUE;
		}else{
			print mysql_error();
		}
		return FALSE;
	}
	private function addGoogleSearchCache($state,$city,$json_response) {
		$state=$this->cleanVariable($state);
		$city=$this->cleanVariable($city);
		$json_response=$this->seo_db->db->_real_escape($json_response);
		if($this->seo_db->db->query("INSERT INTO ".$this->seo_db->ret_google_cache_table()." VALUES('$state','$city','$json_response',NOW())")) {
			return TRUE;
		}else{
			print mysql_error();
		}
		return FALSE;
	}

	public function fetch_yelp_json() {
		if(strlen($this->yelp_api_key)==0) { return FALSE; }

		$results=Array();
			if(strlen($this->state) > 2) {
				$this->state = ret_seo_long_state_to_short($this->state);
			}
		$query="term=".urlencode(esc_attr( get_option('booster-rocket-search-term')))."&location=".str_replace(" ","+",$this->town).",+".$this->state;
		$res = $this->retYelpSearchCache($this->state,$this->town);
		if($res == FALSE) {
			$is_cached=0;
			$opts = [
				"http" => [
					"method" => "GET",
			        	"header" => "Authorization: Bearer ".$this->yelp_api_key."\r\n"
				]
			];
			$context = stream_context_create($opts);
			$res = file_get_contents($this->base_yelp_url.str_replace('&amp;','&',$query),false,$context);
		}else{
			$is_cached=1;
			$this->smarty->assign('notice_yelp',"Using Cached Results");
		}
		$json_results = json_decode($res,1);
		if(isset($json_results['error_message'])) {
			$this->smarty->assign('error',"Yelp Fusion API: ".$json_results['error_message']);
			return $this->smarty->fetch(__DIR__.'/templates/error.tpl');
		}elseif(!$is_cached) {
			$this->addYelpSearchCache($this->state,$this->town,$res);
			$this->smarty->assign('notice_yelp',"Using Live Results");
		}
		$is_records=0;
		foreach($json_results['businesses'] as $record) {
			$is_records=1;
			if(!in_array($record['name'],$this->location_names)) {
				if($record['distance'] <= $this->max_yelp_distance) {
					array_push($results,$record);
					array_push($this->location_names,$record['name']);
				}
			}
		}
		//print "<!-- "; print_r($results); print "-->";
		return $results;
	}

	public function fetch_facebook_json() {
		if(strlen($this->facebook_api_key)==0) { return FALSE; }

			$results=Array();
			if(strlen($this->state) > 2) {
				$this->state = ret_seo_long_state_to_short($this->state);
			}
			
			$geography=$this->ret_town_geo();
			if($geography == FALSE) { return FALSE; }
			$query="q=".urlencode(esc_attr( get_option('booster-rocket-search-term')))."&center=".$geography[0].','.$geography[1];
			$res = $this->retFacebookSearchCache($this->state,$this->town);
			if($res == FALSE) {
				$is_cached=0;
				$res = file_get_contents($this->base_facebook_url.$query);
				$res = json_decode($res,1);
				if(isset($json_results['error_message'])) {
					$this->smarty->assign('error',"Facebook Graph API: ".$json_results['error_message']);
					return $this->smarty->fetch(__DIR__.'/templates/error.tpl');
				}
				$facebook_json='[';
				for($i=0; $i<count($res['data']); $i++ ) {
					$place_query = $res['data'][$i]['id']."?&access_token=".urlencode(esc_attr($this->facebook_api_key))."&fields=".$this->facebook_place_fields;
//print $this->place_facebook_url.str_replace('&amp;','&',$place_query);
					$place = file_get_contents($this->place_facebook_url.str_replace('&amp;','&',$place_query));
					$facebook_json.=$place;
					if($i<(count($res['data'])-1)) {
						$facebook_json.=',';
					}
				}
				$facebook_json.=']';
				$res = $facebook_json;
			}else{
				$is_cached=1;
				$this->smarty->assign('notice_facebook',"Using Cached Results");
			}
			$json_results = json_decode($res,1);
			if(isset($json_results['error_message'])) {
				$this->smarty->assign('error',"Google Places API: ".$json_results['error_message']);
				return $this->smarty->fetch(__DIR__.'/templates/error.tpl');
			}elseif(!$is_cached) {
				$this->addFacebookSearchCache($this->state,$this->town,$res);
				$this->smarty->assign('notice_facebook',"Using Live Results");
			}
			$is_records=0;
			foreach($json_results as $record) {
				$is_records=1;
				//if(!in_array($record['name'],$this->location_names)) {
					array_push($results,$record);
					array_push($this->location_names,$record['name']);
				//}
			}
		//print "<!-- "; print_r($results); print " -->";
		return $results;
	}

	public function fetch_google_json($next_page='') {
		if(strlen($this->places_api_key)==0) { return FALSE; }

		$results=Array();
			if(strlen($this->state) > 2) {
				$this->state = ret_seo_long_state_to_short($this->state);
			}
			$query="query=".urlencode(esc_attr( get_option('booster-rocket-search-term')))."+near+".str_replace(" ","+",$this->town).",+".$this->state; //UPDATE
			$res = $this->retGoogleSearchCache($this->state,$this->town);
			if($res == FALSE) {
				$is_cached=0;
				$res = file_get_contents($this->base_google_url.str_replace('&amp;','&',$query));
			}else{
				$is_cached=1;
				$this->smarty->assign('notice_google',"Using Cached Results");
			}
			//print "Results Length: ".strlen($res)."<br />";
			$json_results = json_decode($res,1);
			if(isset($json_results['error_message'])) {
				$this->smarty->assign('error',"Google Places API: ".$json_results['error_message']);
				return $this->smarty->fetch(__DIR__.'/templates/error.tpl');
			}elseif(!$is_cached) {
				$this->addGoogleSearchCache($this->state,$this->town,$res);
				$this->smarty->assign('notice_google',"Using Live Results");
			}
			$is_records=0;
			foreach($json_results['results'] as $record) {
				$is_records=1;
				if(!in_array($record['name'],$this->location_names)) {
					array_push($results,$record);
					array_push($this->location_names,$record['name']);
				}
			}
		return $results;
	}

	public function ret_town_geo() {
		//$res = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".str_replace(' ','+',$this->town).",+".str_replace(' ','+',$this->state)."&key=".$this->places_api_key); //Doesn't seem to need an api key anymore?
		$res = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".str_replace(' ','+',$this->town).",+".str_replace(' ','+',$this->state));
		$json_result = json_decode($res,1);
		if(isset($json_result['results'][0]['geometry']['location'])) {
			return Array($json_result['results'][0]['geometry']['location']['lat'],$json_result['results'][0]['geometry']['location']['lng']);
		}else{
			#return to suffolk county ny
			return FALSE;
			//return Array('40.792240','-73.138260');
		}
	}

	private function text_similarity($txt1,$txt2) {
		return levenshtein($txt1,$txt2);
	}
	private function coordinate_similarity($place1,$place2) {
		if(isset($place1['latitude']) && isset($place2['latitude']) && abs($place1['latitude']-$place2['latitude'])<$this->geography_tolerance) {
	                if(abs($place1['longitude']-$place2['longitude'])<$this->geography_tolerance) {
				return TRUE;
				//print abs($place1['latitude']-$place2['latitude'])." DIFF <br />";
			}
		}
		return FALSE;
	}
	private function is_duplicate($places,$place) {
		for($i=0; $i<=count($places);$i++) {
			$duplicate=0;
			if(isset($places[$i]['name']) && $this->text_similarity($place['name'],$places[$i]['name']) == 0) {
				$duplicate+=2;
				//print "EXACT NAME MATCH FOUND - ".$place['name']."<br />";
			}elseif(isset($places[$i]['name']) && $this->text_similarity($place['name'],$places[$i]['name']) <= ($this->text_similarity_tolerance/2)) {
				//print $place['name']." - ".$places[$i]['name']." - ".$this->text_similarity($place['name'],$places[$i]['name'])."<br />";
				$duplicate++;
			}
			if(isset($places[$i]['address']) && $this->text_similarity($place['address'],$places[$i]['address']) == 0) {
				$duplicate+=2;
			}elseif(isset($places[$i]['address']) && $this->text_similarity($place['address'],$places[$i]['address']) <= $this->text_similarity_tolerance) {
				//print $place['address']." - ".$places[$i]['address']." - ".$this->text_similarity($place['address'],$places[$i]['address'])."<br />";
				$duplicate++;
			}
			if(isset($places[$i]['phone_formatted']) && strlen($places[$i]['phone_formatted']) > 0 && $this->text_similarity($place['phone_formatted'],$places[$i]['phone_formatted']) == 0) {
				$duplicate++;
				//print "EXACT PHONE NUMBER FOUND - ".$place['phone_formatted']."<br />";
			}
			if(isset($places[$i]) && $this->coordinate_similarity($place,$places[$i])) {
				//print "GEO SIMILARITY - ".$place['name']." - ".$places[$i]['name']."<br />";
				$duplicate+=2;
			}
			//also check coordinates
			if($duplicate>=3) {
				//print "Dupe Score: $duplicate<br />";
				//print $place['name']."<br /><br />";
				return $i;
			}
		}
		return FALSE;
	}
	private function format_phone_number($phone) {
		$phone = preg_replace("/[^0-9,.]/", "", $phone);
		if(strlen($phone)==10 || strlen($phone)==11) {
			return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "($1)$2-$3", $phone);
		}
		return FALSE;
	}
	private function merge_records($array_index,$places_array,$place) {
		if(strlen($place['rating']) > 0 && is_int(intval($place['rating'])) && $place['rating'] <= 5 && $place['rating'] >= 0) {
			if(isset($place['review_count'])) {
				//print $place['review_count'].' B ';
			}
			array_push($places_array[$array_index]['rating'],$place['rating']);
		}
		if(strlen($places_array[$array_index]['phone'])==0) {
			$places_array[$array_index]['phone']=$place['phone'];
		}
		$places_array[$array_index]['photos'].="<br />".$place['photos'];
		
		return $places_array;
	}
	public function ret_combined_json_results() {
		$results = Array();
		$google = $this->fetch_google_json();
		$yelp = $this->fetch_yelp_json();
		$facebook = $this->fetch_facebook_json();
		$merged_array = Array();

		if(isset($google) && is_array($google)) { $merged_array=array_merge($merged_array,$google); }elseif(gettype($google)=='string') { print $google; }
		if(isset($yelp) && is_array($yelp)) { $merged_array=array_merge($merged_array,$yelp); }elseif(gettype($yelp)=='string') { print $yelp; }
		if(isset($facebook) && is_array($facebook)) { $merged_array=array_merge($merged_array,$facebook); }elseif(gettype($facebook)=='string') { print $facebook; }

		foreach($merged_array as $item) {
			if(isset($item['id'])) {
				$tmp_item=Array();
				$tmp_item['id']=$item['id'];
				$tmp_item['name']=$item['name'];

				if(isset($item['formatted_address'])) { //google
					$tmp = preg_replace('/, United States/','',$item['formatted_address']);
					$tmp_item['address'] = $tmp;
				}elseif(isset($item['location'])) { //yelp && facebook
					if(isset($item['location']['address1'])) { //yelp
						if(strlen($item['location']['address2'])>0) {
							$tmp_item['address']=$item['location']['address1'].' '.$item['location']['address2'].', '.$item['location']['city'].', '.$item['location']['state'].' '.$item['location']['zip_code'];
						}elseif(strlen($item['location']['address1'])>0) {
							$tmp_item['address']=$item['location']['address1'].', '.$item['location']['city'].', '.$item['location']['state'].' '.$item['location']['zip_code'];
						}else{
							$tmp_item['address']=$item['location']['city'].', '.$item['location']['state'].' '.$item['location']['zip_code'];
						}

					}elseif(isset($item['location']['street'])) { //facebook
						if(isset($item['location']['street']) && strlen($item['location']['street']) > 0) {
							$tmp_item['address']=$item['location']['street'].', '.$item['location']['city'].', '.$item['location']['state'].' '.$item['location']['zip'];
						}else{
							$tmp_item['address']=$item['location']['city'].', '.$item['location']['state'].' '.$item['location']['zip'];

						}
					}
				}else{ //no address
					$tmp_item['address']='';
				}
			//open now?
			//	if(isset($item['opening_hours'])) { //google
			//		$tmp_item['is_open']='';
			//	}else{ //have no method for yelp atm
			//		$tmp_item['is_open']='';
			//	}
				if(isset($item['rating'])) { //google, yelp
					$tmp_item['rating']=array($item['rating']);
				}elseif(isset($item['overall_star_rating'])) { //facebook
					$tmp_item['rating']=array($item['overall_star_rating']);
				}
				if(isset($item['rating_count'])) { //facebook
					$tmp_item['review_count']=$item['rating_count'];
				}elseif(isset($item['review_count'])) { //yelp
					$tmp_item['review_count']=$item['review_count'];
				}

				if(isset($item['photos'])) { //google
					if(isset($item['photos'][0]['html_attributions'][0])) { //google
						$tmp_item['photos']=preg_replace("/<a href=/","<a target='_blank' href=",$item['photos'][0]['html_attributions'][0]);
					}
				}elseif(isset($item['image_url'])){ //yelp
					$tmp_item['photos']='<img src="'.$item['image_url'].'" />';
				}elseif(isset($item['picture'])) { //facebook
					$tmp_item['photos']='<img src="'.$item['picture']['data']['url'].'" />';
				}
				if(isset($item['coordinates'])) { //yelp
					$tmp_item['latitude']=$item['coordinates']['latitude'];
					$tmp_item['longitude']=$item['coordinates']['longitude'];
					$tmp_item['icon']='https://maps.gstatic.com/mapfiles/place_api/icons/generic_business-71.png';
				}elseif(isset($item['geometry'])) { //google
					$tmp_item['latitude']=$item['geometry']['location']['lat'];
					$tmp_item['longitude']=$item['geometry']['location']['lng'];
					$tmp_item['icon']=$item['icon'];
				}elseif(isset($item['location'])) {
					$tmp_item['latitude']=$item['location']['latitude'];
					$tmp_item['longitude']=$item['location']['longitude'];
					$tmp_item['icon']='https://maps.gstatic.com/mapfiles/place_api/icons/generic_business-71.png';
				}
				if(isset($item['phone'])) { //yelp && facebook
					//$this->format_phone_number($item['phone']);
					$tmp_item['phone']="<a href='tel:".$item['phone']."'>".$this->format_phone_number($item['phone']).'</a>';
					$tmp_item['phone_formatted']=$this->format_phone_number($item['phone']);
				}else{
					$tmp_item['phone']='';
				}
				if(isset($item['website'])) {
					if(preg_match('//',$item['website'])) {
						$tmp_item['website']="<a href='".$item['website']."/' target='_blank'>Visit ".$tmp_item['name']."'s Website</a><br />";
					}else{
						$tmp_item['website']="<a href='http://".$item['website']."/' target='_blank'>Visit ".$tmp_item['name']."'s Website</a><br />";
					}
				}
				if(isset($item['url'])) {
					$tmp_item['url']=$item['url']; // yelp
				}elseif(isset($item['link'])) {
					$tmp_item['url']=$item['link']; // facebook
				}

				$dupe_val=$this->is_duplicate($results,$tmp_item);
				if($dupe_val != FALSE) {
					//print $dupe_val." <br />";
					$results = $this->merge_records($dupe_val,$results,$tmp_item);
					//print "Potential Duplicate Found<br />";
				}else{
					array_push($results,$tmp_item);
				}
			}
		}
		return $results;
	}
}


add_shortcode('seo_booster_rocket_map','seo_booster_rocket_map');
function seo_booster_rocket_map( $atts ) {
	global $wp_query;
	$places = new SEO_Booster_Rocket_Places();
	$geo = new SEO_Booster_Rocket_Geography();
	$retval=$places->smarty->fetch(__DIR__.'/templates/css.tpl');
	
	$town='';
	$state='';

	if(isset($_GET['town']) && strlen($_GET['town']) > 0) { $town = htmlentities($_GET['town']); }
	if(isset($_GET['city']) && strlen($_GET['city']) > 0) { $town = htmlentities($_GET['city']); }
	if(isset($_GET['state']) && strlen($_GET['state']) > 0) { $state = htmlentities($_GET['state']); }

	if(isset($wp_query->query_vars['town']) && strlen($wp_query->query_vars['town']) > 0) { $town = htmlentities($wp_query->query_vars['town']); }
	if(isset($wp_query->query_vars['city']) && strlen($wp_query->query_vars['city']) > 0) { $town = htmlentities($wp_query->query_vars['city']); }
	if(isset($wp_query->query_vars['state']) && strlen($wp_query->query_vars['state']) > 0) { $state = htmlentities($wp_query->query_vars['state']); }

	if(get_option('booster-rocket-powered-by')==1) {
		$places->smarty->assign('powered_by','Powered by <a href="https://wordpress.org/plugins/seo-booster-rocket/" target="_blank">SEO Booster Rocket</a>, developed by <a href="https://websourcegroup.com/" target="_blank"><img src="'.plugin_dir_url(__FILE__).'/images/Web-Source-Group-Logo.png" alt="Web Source Group - We Build Businesses with Technology" /></a><br />');
	}

	if(strlen($town) > 0 && strlen($state) > 0) {
		$places = new SEO_Booster_Rocket_Places($town,$state);
		if(get_option('booster-rocket-powered-by')==1) {
			$places->smarty->assign('powered_by','Powered by <a href="https://wordpress.org/plugins/seo-booster-rocket/" target="_blank">SEO Booster Rocket</a>, developed by <a href="https://websourcegroup.com/" target="_blank"><img src="'.plugin_dir_url(__FILE__).'/images/Web-Source-Group-Logo.png" alt="Web Source Group - We Build Businesses with Technology" /></a><br />');
		}
		$places->smarty->assign("search_term",htmlspecialchars($town).", ".htmlspecialchars($state));

		$places->smarty->assign('town',$town);
		$places->smarty->assign('state',$state);

		$places->smarty->assign('results_combined',$places->ret_combined_json_results());

		$places->smarty->assign('geolocation',$places->ret_town_geo());
		if(get_option('booster-rocket-search-term')) {
			$places->smarty->assign('search_term',get_option('booster-rocket-search-term'));
		}
		if(isset($atts['results']) && $atts['results'] == "false") {
			$places->smarty->assign('state_list',$geo->retStateList());
			$places->smarty->assign('city_list',$geo->retCityList());
			$places->smarty->assign('search_uri',esc_attr(get_option('booster-rocket-maps-uri' )));
			$retval.=$places->smarty->fetch(__DIR__.'/templates/search.tpl');
		}else{
			if($places->ret_maps_api_key()) {
				$places->smarty->assign('maps_api_key',$places->ret_maps_api_key());
			}
			$places->smarty->assign('search_uri',esc_attr(get_option('booster-rocket-maps-uri' )));
			$retval.=$places->smarty->fetch(__DIR__."/templates/results.tpl");
		}
	}else{
		if(get_option('booster-rocket-search-term')) {
			$places->smarty->assign('search_term',get_option('booster-rocket-search-term'));
		}
		$places->smarty->assign('state_list',$geo->retStateList());
		$places->smarty->assign('city_list',$geo->retCityList());
		$places->smarty->assign('search_uri',esc_attr(get_option('booster-rocket-maps-uri' )));
		$places->smarty->display(__DIR__.'/templates/search.tpl');
	}
	return $retval;
}

function menu_seo_booster_rocket_admin_places_maps() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );		
	}else{
		$seo_db = new SEO_Booster_Rocket_DB();
		settings_fields( 'seo-booster-rocket-places-maps' );
		do_settings_sections( 'seo-booster-rocket-places-maps' );

		if(isset($_POST)) {
			$msg="";
			$success=FALSE;
			if(isset($_POST['booster-rocket-facebook-api-key']) && wp_verify_nonce($_REQUEST['seo-booster-rocket-update-config'],'seo-booster-rocket-update-config')==1) {
				update_option('booster-rocket-facebook-api-key',$seo_db->cleanVariable($_POST['booster-rocket-facebook-api-key']));
				$msg="SEO Booster Rocket Options Saved.";
				$success=TRUE;
			}
			if(isset($_POST['booster-rocket-yelp-api-key']) && wp_verify_nonce($_REQUEST['seo-booster-rocket-update-config'],'seo-booster-rocket-update-config')==1) {
				update_option('booster-rocket-yelp-api-key',$seo_db->cleanVariable($_POST['booster-rocket-yelp-api-key']));
				$msg="SEO Booster Rocket Options Saved.";
				$success=TRUE;
			}
			if(isset($_POST['booster-rocket-places-api-key']) && wp_verify_nonce($_REQUEST['seo-booster-rocket-update-config'],'seo-booster-rocket-update-config')==1) {
				update_option('booster-rocket-places-api-key',$seo_db->cleanVariable($_POST['booster-rocket-places-api-key']));
				$msg="SEO Booster Rocket Options Saved.";
				$success=TRUE;
			}
			if(isset($_POST['booster-rocket-maps-api-key']) && wp_verify_nonce($_REQUEST['seo-booster-rocket-update-config'],'seo-booster-rocket-update-config')==1) {
				update_option('booster-rocket-maps-api-key',$seo_db->cleanVariable($_POST['booster-rocket-maps-api-key']));
				$msg="SEO Booster Rocket Options Saved.";
				$success=TRUE;
			}
			if(isset($_POST['booster-rocket-maps-uri']) && wp_verify_nonce($_REQUEST['seo-booster-rocket-update-config'],'seo-booster-rocket-update-config')==1) {
				update_option('booster-rocket-maps-uri',$seo_db->cleanVariable($_POST['booster-rocket-maps-uri']));
				$msg="SEO Booster Rocket Options Saved.";
				$success=TRUE;
			}
			if(isset($_POST['booster-rocket-search-term']) && wp_verify_nonce($_REQUEST['seo-booster-rocket-update-config'],'seo-booster-rocket-update-config')==1) {
				update_option('booster-rocket-search-term',$seo_db->cleanVariable($_POST['booster-rocket-search-term']));
				$msg="SEO Booster Rocket Options Saved.";
				$success=TRUE;
			}
			if(isset($_POST['booster-rocket-cache-age']) && wp_verify_nonce($_REQUEST['seo-booster-rocket-update-config'],'seo-booster-rocket-update-config')==1) {
				update_option('booster-rocket-cache-age',$seo_db->cleanVariable($_POST['booster-rocket-cache-age']));
				$msg="SEO Booster Rocket Options Saved.";
				$success=TRUE;
			}
			if(isset($_POST['booster-rocket-powered-by']) && $_POST['booster-rocket-powered-by'] == 1 && wp_verify_nonce($_REQUEST['seo-booster-rocket-update-config'],'seo-booster-rocket-update-config')==1) {
				update_option('booster-rocket-powered-by',1);
				$msg="SEO Booster Rocket Options Saved.";
				$success=TRUE;
			}elseif(isset($_POST['booster-rocket-powered-by']) && $_POST['booster-rocket-powered-by'] == 0 && wp_verify_nonce($_REQUEST['seo-booster-rocket-update-config'],'seo-booster-rocket-update-config')==1) {
				update_option('booster-rocket-powered-by',0);
				$msg="SEO Booster Rocket Options Saved.";
                                $success=TRUE;
			}
			if(isset($_POST['seo_booster_rocket_install_tables']) && wp_verify_nonce($_REQUEST['seo-booster-rocket-install-tables'],'seo-booster-rocket-install-tables')==1) {
				if(!$seo_db->are_tables_installed()) {
					if($seo_db->install_tables()) {
						$msg="SEO Booster Rocket Tables Installed Successfully.";
						$success=TRUE;
					}else{
						$msg="SEO Booster Rocket Could Not Be Installed.";
						$failed=TRUE;
					}
				}
			}
			if(isset($_POST['seo_booster_rocket_install_geo_data']) && wp_verify_nonce($_REQUEST['seo-booster-rocket-install-geo'],'seo-booster-rocket-install-geo')==1) {
				if(!$seo_db->is_geography_data_installed()) {
					if($seo_db->install_geo_data()) {
						$msg="SEO Booster Rocket GEO Data Installed Successfully.";
						$success=TRUE;
					}else{
						$msg="SEO Booster Rocket GEO Data Could Not Be Installed.";
						$failed=TRUE;
					}
				}
			}
			if(isset($_POST['seo_booster_rocket_clear_geography']) && wp_verify_nonce($_REQUEST['seo-booster-rocket-clear-geo'],'seo-booster-rocket-clear-geo')==1) {
				if($seo_db->clear_geography()) {
					$msg="SEO Booster Rocket Geography Sucessfully Cleared.";
					$success=TRUE;
				}else{
					$msg="SEO Booster Rocket Geography Failed to Clear.";
					$failed=TRUE;
				}
			}
			if(isset($_POST['seo_booster_rocket_clear_google_cache']) && wp_verify_nonce($_REQUEST['seo-booster-rocket-clear-google-cache'],'seo-booster-rocket-clear-google-cache')==1) {
				if($seo_db->clear_google_geography_cache()) {
					$msg="SEO Booster Rocket Google Cached Searches Sucessfully Cleared.";
					$success=TRUE;
				}else{
					$msg="SEO Booster Rocket Google Cached Searches Failed to Clear.";
					$failed=TRUE;
				}
			}
			if(isset($_POST['seo_booster_rocket_clear_yelp_cache']) && wp_verify_nonce($_REQUEST['seo-booster-rocket-clear-yelp-cache'],'seo-booster-rocket-clear-yelp-cache')==1) {
				if($seo_db->clear_yelp_geography_cache()) {
					$msg="SEO Booster Rocket Yelp Cached Searches Sucessfully Cleared.";
					$success=TRUE;
				}else{
					$msg="SEO Booster Rocket Yelp Cached Searches Failed to Clear.";
					$failed=TRUE;
				}
			}
			if(isset($_POST['seo_booster_rocket_clear_facebook_cache']) && wp_verify_nonce($_REQUEST['seo-booster-rocket-clear-facebook-cache'],'seo-booster-rocket-clear-facebook-cache')==1) {
				if($seo_db->clear_facebook_geography_cache()) {
					$msg="SEO Booster Rocket Facebook Cached Searches Sucessfully Cleared.";
					$success=TRUE;
				}else{
					$msg="SEO Booster Rocket Facebook Cached Searches Failed to Clear.";
					$failed=TRUE;
				}
			}
			if(isset($success) && $success) {
				?><div class="updated notice"><p><? echo $msg; ?></p></div><?
			}
			if(isset($failed) && $failed) {
				?><div class="notice notice-failed"><p><? echo $msg; ?></p></div><?
			}
		}
		if(!$seo_db->are_tables_installed()) {
			?><div class="notice notice-error"><p>SEO Booster Rocket Database Tables Are Not Installed or Need to be Updated!</p><form method="POST" action=""><input type="hidden" name="seo_booster_rocket_install_tables" value="1" /><?php wp_nonce_field('seo-booster-rocket-install-tables','seo-booster-rocket-install-tables'); ?><input type="submit" value="Click Here to Install Tables Now" /></form></div><?
		}else{
			if(!$seo_db->is_geography_data_installed()) {
				?><div class="notice notice-error"><p>SEO Booster Rocket Geography Data Is Not Installed! </p><form method="POST" action=""><input type="hidden" name="seo_booster_rocket_install_geo_data" value="1" /><?php wp_nonce_field('seo-booster-rocket-install-geo','seo-booster-rocket-install-geo'); ?><input type="submit" value="Click Here to Install Geography Data Now" /></form><p>Downloading this data reqiures a connection to the Web Source Group Server. This communication is encrypted using HTTPS technologies. Please be patient as this process may take up to a minute.</p></div><?
			}
		}
		?>
		<div class="wrap">
			<h1>SEO Booster Rocket - Places & Maps</h1>
			<br />
			<form method="post" action=""> 
				<div valign="top">
					<th scope="col">Facebook Access Token/API Key:</th>
					<td><input autocomplete="off" type="text" name="booster-rocket-facebook-api-key" size="100" placeholder="<? echo str_repeat("X",39); ?>" value="<?php echo esc_attr( get_option('booster-rocket-facebook-api-key' ) ); ?>" /> <a target="_blank" href="https://websourcegroup.com/how-to-get-a-facebook-graph-access-token/">How do I get a Facebook Access Token/API Key?</a>
					</td>
				</div>
				<div valign="top">
					<th scope="col">Yelp Fusion API Key:</th>
					<td><input autocomplete="off" type="text" name="booster-rocket-yelp-api-key" size="100" placeholder="<? echo str_repeat("X",39); ?>" value="<?php echo esc_attr( get_option('booster-rocket-yelp-api-key' ) ); ?>" /> <a target="_blank" href="https://websourcegroup.com/how-to-get-a-yelp-fusion-api-key/">How do I get a Yelp API Key?</a>
					</td>
				</div>
				<div valign="top">
					<th scope="col">Google Places API Key:</th>
					<td><input autocomplete="off" type="text" name="booster-rocket-places-api-key" size="100" placeholder="<? echo str_repeat("X",39); ?>" value="<?php echo esc_attr( get_option('booster-rocket-places-api-key' ) ); ?>" /> <a target="_blank" href="https://websourcegroup.com/how-to-get-a-google-places-api-key/">How do I get a Places API Key?</a>
					</td>
				</div>
				<div valign="top">
					<th scope="col">Google Maps API Key:</th>
					<td><input autocomplete="off" type="text" name="booster-rocket-maps-api-key" size="100" placeholder="<? echo str_repeat("X",39); ?>" value="<?php echo esc_attr( get_option('booster-rocket-maps-api-key' ) ); ?>" /> <a target="_blank" href="https://websourcegroup.com/how-to-get-a-google-maps-api-key/">How do I get a Maps API Key?</a>
					</td>
				</div>
				<div valign="top">
					<th scope="col">Booster Rocket Maps URI:</th>
					<td><input autocomplete="off" type="text" name="booster-rocket-maps-uri" size="100" placeholder="/search-for-convinience-stores/" value="<?php echo esc_attr( get_option('booster-rocket-maps-uri' ) ); ?>" /><span class="dashicons dashicons-info" data-toggle="tooltip" title="This option works in conjunction with the '[seo_booster_rocket_process_requests]' shortcode and the resulting SiteMap. It tells the plugin what URL to use for form processing. This shoudl match the page/post URL whcih uses, or holds, the seo_booster_rocket_process_requests shortcode."></span></td>
				</div>
				<div valign="top">
					<th scope="col">Places Search Term:</th>
					<td><input type="text" name="booster-rocket-search-term" size="100" placeholder="Convinience Stores" value="<?php echo esc_attr( get_option('booster-rocket-search-term' ) ); ?>" /><span class="dashicons dashicons-info" data-toggle="tooltip" title="This option determines what search term is used with the Places API. This search term shoudl be relevant to the topic of your website. ex: If your site discusses Pool Halls then put in 'Pool Halls'."></span></td>
				</div>
				<div valign="top">
					<th scope="col">Maximum Search Cache Age in Days:</th>
					<td><input type="text" name="booster-rocket-cache-age" size="100" placeholder="7" value="<?php echo esc_attr( get_option('booster-rocket-cache-age' ) ); ?>" /><span class="dashicons dashicons-info" data-toggle="tooltip" title="Searches are Cached to preserve your API Usage. This value determines the Expiration of this Cache to ensure you have up to date search results."></span></td>
				</div>
				<div valign="top">
					<th scope="col">Add a Powered By Link to Support this Plugin (Is Much Appreciated):</th>
					<td>Yes: <input type="radio" name="booster-rocket-powered-by" value="1"<?php if(get_option('booster-rocket-powered-by')==1) echo " checked"; ?> /> - No: <input type="radio" name="booster-rocket-powered-by" value="0"<?php if(get_option('booster-rocket-powered-by')==0) echo " checked"; ?> /><span class="dashicons dashicons-info" data-toggle="tooltip" title="We hope that you'll consider enabling this option. It places attribution text below your results that shows you support this plugin."></span></td>
				</div>
				<?php wp_nonce_field('seo-booster-rocket-update-config','seo-booster-rocket-update-config'); ?>
				<?php submit_button(); ?>
			</form>
				<div valign="top">
					<th scope="col">Number of Geographic Entities:</th>
					<td><? echo $seo_db->geography_data_count(); if($seo_db->geography_data_count() != 0) { ?> <form method="POST" action=""><input type="hidden" name="seo_booster_rocket_clear_geography" value="1" /><?php wp_nonce_field('seo-booster-rocket-clear-geo','seo-booster-rocket-clear-geo'); ?><input type="submit" value="Clear Geography Data" /></form> <? } ?></td>
				</div><br />
				<div valign="top">
					<th scope="col">Number of Cached Google Searches:</th>
					<td><? echo $seo_db->cached_google_geography_data_count(); if($seo_db->cached_google_geography_data_count() != 0) { ?> <form method="POST" action=""><input type="hidden" name="seo_booster_rocket_clear_google_cache" value="1" /><?php wp_nonce_field('seo-booster-rocket-clear-google-cache','seo-booster-rocket-clear-google-cache'); ?><input type="submit" value="Clear Google Search Cache" /></form> <? } ?></td>
				</div><br />
				<div valign="top">
					<th scope="col">Number of Cached Yelp Searches:</th>
					<td><? echo $seo_db->cached_yelp_geography_data_count(); if($seo_db->cached_yelp_geography_data_count() != 0) { ?> <form method="POST" action=""><input type="hidden" name="seo_booster_rocket_clear_yelp_cache" value="1" /><?php wp_nonce_field('seo-booster-rocket-clear-yelp-cache','seo-booster-rocket-clear-yelp-cache'); ?><input type="submit" value="Clear Yelp Search Cache" /></form> <? } ?></td>
				</div><br />
				<div valign="top">
					<th scope="col">Number of Cached Facebook Searches:</th>
					<td><? echo $seo_db->cached_facebook_geography_data_count(); if($seo_db->cached_facebook_geography_data_count() != 0) { ?> <form method="POST" action=""><input type="hidden" name="seo_booster_rocket_clear_facebook_cache" value="1" /><?php wp_nonce_field('seo-booster-rocket-clear-facebook-cache','seo-booster-rocket-clear-facebook-cache'); ?><input type="submit" value="Clear Facebook Search Cache" /></form> <? } ?></td>
				</div><br />

				<p>* We recommend restricting the Places API Key to your server address. This has been detected as: <b><? echo $_SERVER['SERVER_NAME']; ?></b> using the IP Address <b><? echo gethostbyname($_SERVER['SERVER_NAME']); ?></b></p>
				<p>* We recommend restricting the Maps API Key to your server referral address. This has been detected as: <b><? echo $_SERVER['SERVER_NAME']; ?></b></p>
				<p>* This plugin supports two short codes: [seo_booster_rocket_process_requests] &amp; [seo_booster_rocket_map].</p>
				<p>* One you have confirmed that this plugin is configured properly you can submit the following XML sitemap to your prefered Search Engines: <a target="_blank" href="<? echo home_url(); ?>?seo-booster-rocket-sitemap">SiteMap</a></p>
		</div>
		<style>
			form div th {
				width: 400px !important;
				min-width: 30% !important;
				max-width: 40% !important;
			}
		</style>
		<?
	}
}

function menu_seo_booster_rocket() {
	?>
                <div class="wrap">
                        <h1><?php echo esc_html( get_admin_page_title() ); ?> - Main Plugin Page</h1>
                        <br />
			<div class="updated notice header_notice">
				<div>
					<div class="header_left_div"><span class="dashicons dashicons-yes"></span>This plugin was developed by <a href="https://websourcegroup.com/" target="_blank">Web Source Group</a>.<br />
					If you like SEO Rocket Booster, please consider either <a href="https://tinyurl.com/ydaadbdy" target="_blank">Donating to the project</a> or refer <a href="https://websourcegroup.com/contact-web-source-group/request-a-free-business-consultation/" target="_blank">Web Source Group to your Business &amp; your Clients</a>!</div>
					<div class="header_right_div"><a href="https://websourcegroup.com/" target="_blank"><img src="<? echo plugin_dir_url(__FILE__); ?>/images/Web-Source-Group-Logo.png" alt="Web Source Group" /></a></div>
				</div>
			</div>
			<div class="header_left_div">
				<p>This plugin was developed with the sole purpose of increasing the indexable footprint of your Wordpress Website while providing a unique Geographic Search Experience. This plugin can rapidly transform a 5 page web site into a 50,000+ page website in minutes!</p>
				<p>The data that is used by this plugin only supports US Based States, Counties &amp; Towns. If you have access to i18n geographic data then please <a href="https://websourcegroup.com/contact-web-source-group/" target="_blank">contact us to determine integration strategies</a>.</p>
				<p>A demo is available for viewing <a href="https://usayo.ga/search-for-a-yoga-studio/" target="_blank">Here</a> and <a href="https://usayo.ga/find-yoga-studio-by-geography/" target="_blank">Here</a>.</p>
				<h3>Configure this Plugin</h3>
				<h5><a href="admin.php?page=seo-booster-rocket-places-maps">Places & Maps</a></h5>
				<br />
				<p>* This plugin supports two short codes: [seo_booster_rocket_process_requests] &amp; [seo_booster_rocket_map].</p>
			</div>
                </div>
		<style>
			.header_notice {
				padding: 20px !important;
				border-radius: 15px;
			}
			.header_left_div {
				padding: 5px 20px 20px 20px;
				float: left;
				max-width: 60%;
				width: auto;
				font-size: 125%;
			}
		</style>
	<?
}

function add_seo_booster_rocket_menu() {
	add_menu_page( __('SEO Booster Rocket','seo-booster-rocket'),__('SEO Booster Rocket','seo-booster-rocket'),'administrator','seo-booster-rocket','menu_seo_booster_rocket','dashicons-chart-area',81);
	add_submenu_page('seo-booster-rocket','SEO Booster Rocket - Places & Maps','Places & Maps','administrator','seo-booster-rocket-places-maps','menu_seo_booster_rocket_admin_places_maps');
	register_setting( 'seo-booster-rocket-places-maps', 'booster-rocket-facebook-api-key' );
	register_setting( 'seo-booster-rocket-places-maps', 'booster-rocket-yelp-api-key' );
	register_setting( 'seo-booster-rocket-places-maps', 'booster-rocket-places-api-key' );
	register_setting( 'seo-booster-rocket-places-maps', 'booster-rocket-maps-api-key' );
	register_setting( 'seo-booster-rocket-places-maps', 'booster-rocket-maps-uri' );
	register_setting( 'seo-booster-rocket-places-maps', 'booster-rocket-search-term' );
	register_setting( 'seo-booster-rocket-places-maps', 'booster-rocket-cache-age' );
	register_setting( 'seo-booster-rocket-places-maps', 'booster-rocket-powered-by' );
}


add_action( 'admin_menu', 'add_seo_booster_rocket_menu' );



class SEO_Booster_Rocket_DB {
	private $geo_table;
	private $google_cache_table;
	private $charset;
	public $db;
	private $geo_data_url;

	function __construct() {
		global $wpdb;
		$this->db = $wpdb;
		$this->geo_table = $this->db->prefix."seo_booster_rocket_geo";
		$this->google_cache_table = $this->db->prefix."seo_booster_rocket_google_cache";
		$this->yelp_cache_table = $this->db->prefix."set_booster_rocket_yelp_cache";
		$this->facebook_cache_table = $this->db->prefix."set_booster_rocket_facebook_cache";
		$this->charset = $this->db->get_charset_collate();
		$this->geo_data_url="https://websourcegroup.com/download/seo-booster-rocket-geographic-data-backup/";
	}
        public function cleanVariable($var) {
		return sanitize_text_field($var);
        }
	public function ret_geo_table() {
		return $this->geo_table;
	}
	public function ret_yelp_cache_table() {
		return $this->yelp_cache_table;
	}
	public function ret_facebook_cache_table() {
		return $this->facebook_cache_table;
	}
	public function ret_google_cache_table() {
		return $this->google_cache_table;
	}
	public function install_tables() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		if($this->db->get_var("SHOW TABLES LIKE '".$this->geo_table."'") != $this->geo_table) {
			$sql="CREATE TABLE ".$this->geo_table." (city VARCHAR(50) NOT NULL, state_short VARCHAR(2) NOT NULL, state_full VARCHAR(50) NOT NULL, county VARCHAR(50) NOT NULL, city_alias VARCHAR(50) NOT NULL) ENGINE=InnoDB ".$this->charset.";";
			dbDelta( $sql );
		}
		if($this->db->get_var("SHOW TABLES LIKE '".$this->google_cache_table."'") != $this->google_cache_table) {
			$sql="CREATE TABLE ".$this->google_cache_table." (state VARCHAR(2) NOT NULL, city VARCHAR(50) NOT NULL, json_response MEDIUMTEXT NOT NULL, date DATE NOT NULL) ENGINE=InnoDB ".$this->charset.";";
			dbDelta( $sql );
		}
		if($this->db->get_var("SHOW TABLES LIKE '".$this->yelp_cache_table."'") != $this->yelp_cache_table) {
			$sql="CREATE TABLE ".$this->yelp_cache_table." (state VARCHAR(2) NOT NULL, city VARCHAR(50) NOT NULL, json_response MEDIUMTEXT NOT NULL, date DATE NOT NULL) ENGINE=InnoDB ".$this->charset.";";
			dbDelta( $sql );
		}
		if($this->db->get_var("SHOW TABLES LIKE '".$this->facebook_cache_table."'") != $this->facebook_cache_table) {
			$sql="CREATE TABLE ".$this->facebook_cache_table." (state VARCHAR(2) NOT NULL, city VARCHAR(50) NOT NULL, json_response MEDIUMTEXT NOT NULL, date DATE NOT NULL) ENGINE=InnoDB ".$this->charset.";";
			dbDelta( $sql );
		}
		return $this->are_tables_installed();
	}
	public function are_tables_installed() {
		if($this->db->get_var("SHOW TABLES LIKE '".$this->geo_table."'") != $this->geo_table || $this->db->get_var("SHOW TABLES LIKE '".$this->google_cache_table."'") != $this->google_cache_table || $this->db->get_var("SHOW TABLES LIKE '".$this->yelp_cache_table."'") != $this->yelp_cache_table || $this->db->get_var("SHOW TABLES LIKE '".$this->facebook_cache_table."'") != $this->facebook_cache_table) {
			return FALSE;
		}
		return TRUE;
	}
	public function is_geography_data_installed() {
		if($this->are_tables_installed()) {
			if($this->geography_data_count() > 0) {
				return TRUE;
			}
		}
		return FALSE;
	}
	public function geography_data_count() {
		if($this->are_tables_installed()) {
                        return $this->db->get_var("SELECT COUNT(*) FROM ".$this->geo_table);
                }
                return 0;
	}
	public function cached_facebook_geography_data_count() {
		if($this->are_tables_installed()) {
                        return $this->db->get_var("SELECT COUNT(*) FROM ".$this->facebook_cache_table);
                }
                return 0;
	}
	public function cached_google_geography_data_count() {
		if($this->are_tables_installed()) {
                        return $this->db->get_var("SELECT COUNT(*) FROM ".$this->google_cache_table);
                }
                return 0;
	}
	public function cached_yelp_geography_data_count() {
		if($this->are_tables_installed()) {
                        return $this->db->get_var("SELECT COUNT(*) FROM ".$this->yelp_cache_table);
                }
                return 0;
	}
	public function clear_geography() {
		if($this->are_tables_installed()) {
			$this->db->query("DELETE FROM ".$this->geo_table);
			if($this->geography_data_count() == 0) {
				return TRUE;
			}
		}
		return FALSE;
	}
	public function clear_yelp_geography_cache() {
		if($this->are_tables_installed()) {
			$this->db->query("DELETE FROM ".$this->yelp_cache_table);
			if($this->cached_yelp_geography_data_count() == 0) {
				return TRUE;
			}
		}
		return FALSE;
	}
	public function clear_facebook_geography_cache() {
		if($this->are_tables_installed()) {
			$this->db->query("DELETE FROM ".$this->facebook_cache_table);
			if($this->cached_facebook_geography_data_count() == 0) {
				return TRUE;
			}
		}
		return FALSE;
	}
	public function clear_google_geography_cache() {
		if($this->are_tables_installed()) {
			$this->db->query("DELETE FROM ".$this->google_cache_table);
			if($this->cached_google_geography_data_count() == 0) {
				return TRUE;
			}
		}
		return FALSE;
	}
	public function install_geo_data() {
		$geo_data = file_get_contents($this->geo_data_url);
		if($geo_data != FALSE) {
			//require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta($geo_data);
			return $this->is_geography_data_installed();
		}
		return FALSE;
	}
}

class SEO_Booster_Rocket_AJAX {
        function __construct() {

	}
}


?>
