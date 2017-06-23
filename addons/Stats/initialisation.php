<?php 
/*
 *	Made by relavis
 *
 *  License: MIT
 */

// Initialise the stats addon
// We've already checked to see if it's enabled

// Require language
require('addons/Stats/language.php');

// Enabled, add links to navbar
$c->setCache('statsaddon');
if($c->isCached('linklocation')){
	$link_location = $c->retrieve('linklocation');
} else {
	$c->store('linklocation', 'footer');
	$link_location = 'footer';
}

switch($link_location){
	case 'navbar':
		$navbar_array[] = array('stats' => $stats_language['stats_icon'] . $stats_language['stats']);
	break;
	
	case 'footer':
		$footer_nav_array['stats'] = $stats_language['stats_icon'] . $stats_language['stats'];
	break;
	
	case 'more':
		$nav_stats_object = new stdClass();
		$nav_stats_object->url = '/stats';
		$nav_stats_object->icon = $stats_language['stats_icon'];
		$nav_stats_object->title = $stats_language['stats'];
	
		$nav_more_dropdown[] = $nav_stats_object;
	break;
	
	case 'none':
	break;
	
	default:
		$navbar_array[] = array('stats' => $stats_language['stats_icon'] . $stats_language['stats']);
	break;
}