<?php

namespace MDBHC;

use \DateTime;

class Histograms {

	function __construct() {
	}

	public function check() {
	global $wpdb;
		$query = "select count(*) from mysql.table_stats where db_name = '" . DB_NAME . "' and table_name LIKE '" . $wpdb->prefix . "%';";
	$ret = $wpdb->get_var($query);
	if ($wpdb->last_error) {
		return -1;
		}

		if ($ret > 0) {
			return 1;
		}
		return 0;
	}

	public function last() {
	global $wpdb;
	$query = "select UPDATE_TIME from information_schema.tables where table_schema='mysql' and table_name='table_stats';";
	$result = $wpdb->get_var($query);
	return $result;
	}

	public function isReRunNeeded() {
		$lastRun = new DateTime($this->last());
		$now = new DateTime();

		$interval = $lastRun->diff($now);

		if(90 < (int) $interval->format('%a')){
			return true;
		}

		return false;
	}

	public function run() {
	global $wpdb;
		// TODO: check that we have permissions for mysql privileges tables
		foreach ($wpdb->tables as $value) {
			$query = "ANALYZE TABLE " . DB_NAME . "." . $wpdb->prefix . $value . " PERSISTENT FOR ALL;";
			// TODO: check errors
			$wpdb->query($query);
		}
	}
}
