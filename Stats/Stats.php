<?php
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 *  Copyright (c) 2016 Samerton
 */
 
// Stats class
class Stats {
	private $_db,
			$_data,
			$_language,
			$_prefix;
	
	// Connect to database
	public function __construct($inf_db, $language) {
		$this->_db = DB_Custom::getInstance($inf_db['address'], $inf_db['name'], $inf_db['username'], $inf_db['password']);
		$this->_prefix = $inf_db['prefix'];
		$this->_language = $language;
	}
	
	// Params: $uuid (string), UUID of a user. If null, will list all infractions
	public function getAllStats($uuid = null) {
		if($uuid !== null){
			$field = "uuid";
			$symbol = "=";
			$equals = $uuid;
		} else {
			$field = "uuid";
			$symbol = "<>";
			$equals = "0";
		}
		$stats = $this->_db->get($this->_prefix . 'stats', array($field, $symbol, $equals))->results();
		
		$results = array();
		$i = 0;
		
		foreach($stats as $stat){
			$results[$i]["id"] = $stat->id;
			$results[$i]["uuid"] = $stat->UUID;
			$results[$i]["wins"] = $stat->wins;
			$results[$i]["kills"] = $stat->kills;
			$results[$i]["deaths"] = $stat->deaths;
			$results[$i]["kd"] = $stat->kd;
			$results[$i]["higheststreak"] = $stat->higheststreak;
			$results[$i]["arrowsshot"] = $stat->arrowsshot;
			$results[$i]["arrowshit"] = $stat->arrowshit;
			$results[$i]["goldenappleseaten"] = $stat->goldenappleseaten;
			$results[$i]["goldenheadseaten"] = $stat->goldenheadseaten;
			$results[$i]["heartshealed"] = $stat->heartshealed;
			$results[$i]["zombieskilled"] = $stat->zombieskilled;
			$results[$i]["creeperskilled"] = $stat->creeperskilled;
			$results[$i]["skeletonskilled"] = $stat->skeletonskilled;
			$results[$i]["cavespiderskilled"] = $stat->cavespiderskilled;
			$results[$i]["spiderskilled"] = $stat->spiderskilled;
			$results[$i]["blazeskilled"] = $stat->blazeskilled;
			$results[$i]["ghastskilled"] = $stat->ghastskilled;
			$results[$i]["cowskilled"] = $stat->cowskilled;
			$results[$i]["pigskilled"] = $stat->pigskilled;
			$results[$i]["chickenskilled"] = $stat->chickenskilled;
			$results[$i]["horseskilled"] = $stat->horseskilled;
			$results[$i]["witcheskilled"] = $stat->witcheskilled;
			$results[$i]["netherentrances"] = $stat->netherentrances;
			$results[$i]["horsestamed"] = $stat->horsestamed;
			$results[$i]["xplevelsearned"] = $stat->xplevelsearned;
			$results[$i]["diamondsmined"] = $stat->diamondsmined;
			$results[$i]["goldmined"] = $stat->goldmined;
			$results[$i]["redstonemined"] = $stat->redstonemined;
			$results[$i]["lapismined"] = $stat->lapismined;
			$results[$i]["ironmined"] = $stat->ironmined;
			$results[$i]["coalmined"] = $stat->coalmined;
			$results[$i]["quartzmined"] = $stat->quartzmined;
			$results[$i]["spawnersmined"] = $stat->spawnersmined;
			

			$i++;
		}
		
		// Sort by wins
		function wins_compare($a, $b)
		{
			$t1 = $a['wins'];
			$t2 = $b['wins'];
			return $t2 - $t1;
		}    
		usort($results, 'wins_compare');
		return $results;
	}
	
	// Params: $id (int), ID of infraction
	public function getStat($id) {
			$result = $this->_db->get($this->_prefix . 'stats', array("id", "=", $id))->results();
			return $result;
		return false;
	}
	
	// Get a username from a UUID
	// Params: $uuid (string) - UUID of user
	public function getUsernameFromUUID($uuid){
		// Query database
		$results = $this->_db->get($this->_prefix . 'stats', array('uuid', '=', $uuid))->results();
		return $results;
	}


}
