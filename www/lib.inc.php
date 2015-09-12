<?php
  ini_set('display_errors', 'off'); // do not show any errors

	require('config.inc.php');
	require(SMARTY_DIR . '/Smarty.class.php');

	/*
	 * Feeds a query to the database while binding parameters to it. $params must
	 * be an array of 'key' => 'value', and query should reference to each key
	 * as :key.
	 *
	 * Returns result handle on success (dies on failure)
	 */
	function db_query($query, $params = array()) {
		// Replace all :foo values with numbers
		$n = 1; $param_map = array();
		foreach ($params as $k => $v) {
			$count = 0;
			$query = preg_replace("/:$k([^a-zA-Z0-9_]|$)/", "\\$$n\$1", $query, -1, $count);
			if ($count == 0) {
				die("Bind variable '$k' not present in query '$query'");
			}
			array_push($param_map, $v);
			$n++;
		}

		// Die if we still lack bound variables
		if (preg_match("/:[a-zA-Z0-9]/", $query, $m) === 1) {
			die("Not all variables bound in [$query]");
		}

		$r = pg_query_params($GLOBALS['db'], $query, $param_map);
		if ($r === FALSE) {
		echo($query);
			die("Query error");
		}
		return $r;
	}

	// Invokes db_query() to fetch a single row, returned as array($key => $value, ...)
	function db_query_single($query, $params = array()) {
		return pg_fetch_assoc(db_query($query, $params));
	}

	// Invokes db_query() to fetch a single row, returned as an array of array($key => $value, ...)
	function db_query_multi($query, $params = array()) {
		return pg_fetch_all(db_query($query, $params));
	}

	// Retrieves array index value of $a[$v] if it exists, otherwise the first value
	function array_by_index($a, $v) {
		if (!is_null($v) && is_numeric($v) && $v >= 0 && $v < count($a)) {
			return $a[$v];
		}
		return $a[0];
	}

	// Calculates the tier of a given item
	function calc_tier($level, $q) {
		// Modifiers for white, green, blue, purple. Anything > purple is to be
		// treated as purple
		$modifier = array(-1, 2, 6, 12);
		if ($q < 0) {
			$q = 0;
		}
		if ($q >= count($modifier)) {
			$q = count($modifier) - 1;
		}
		return floor(($level + $modifier[$q]) / 20) + 1;
	}

	// Fetches refinement level for a given refinement ID
	function get_refinements($refinetableid) {
		$r = array();
		for ($level = 1; $level <= 20; $level++) {
			$r[$level] = db_query_multi("SELECT swet.content AS name,rp.value,rp.typeid AS id FROM refine_prop rp,sys_weareqtype swet WHERE rp.guid=:tableid AND rp.typeid=swet.id ORDER BY rp.ordernum ASC", array("tableid" => $refinetableid + $level - 1));
		}
		return $r;
	}

	// Helper function to replace a single [..] to a string
	function resolve_textstring_id($id) {
		$c = db_query_single("SELECT content FROM sys_name sn WHERE sn.id=:id", array("id" => $id));
		if ($c === false)
			return "?";
		return '<strong>' . $c['content'] . '</strong>';
	}

	// Helper function to replace a single [SC_..] to a string
	function resolve_textstring_sc($sc) {
		$c = db_query_single("SELECT content FROM sys_sc sc WHERE sc.id=:id", array("id" => substr($sc, 3)));
		if ($c === false)
			return "?";
		return $c['content'];
	}

	// Helper function to replace a single [ZONE_..] to a string
	function resolve_textstring_zone($sc) {
		$c = db_query_single("SELECT content FROM sys_zone sz WHERE sz.id=:id", array("id" => substr($sc, 5)));
		if ($c === false)
			return "?";
		return '<strong>' . $c['content'] . '</strong>';
	}

	// Helper function to replace a single [..] to a string
	function resolve_textstring_single($s) {
		if (is_numeric($s)) { return resolve_textstring(resolve_textstring_id($s)); }
		if (substr($s, 0, 3) == "SC_") { return resolve_textstring(resolve_textstring_sc($s)); }
		if (substr($s, 0, 5) == "ZONE_") { return resolve_textstring(resolve_textstring_zone($s)); }
		return "?";
	}

	// Resolves all [...] references within a text string
	function resolve_textstring($s) {
		return preg_replace("/\[([^\]]+)\]/e", "resolve_textstring_single('$1')", $s);
	}

	// Establish a database connection; we'll need it sooner or later
	$GLOBALS['db'] = pg_connect(PG_DB);
	if (!$GLOBALS['db']) {
		die("Cannot establish database connection");
	}

	// Initialize Smarty; most pages use this
	$GLOBALS['smarty'] = new Smarty;
	$GLOBALS['smarty']
		->setTemplateDir(TEMPLATE_DIR)
		->setCompileDir(CTEMPLATE_DIR);

	// XXX
	$GLOBALS['smarty']->caching = false;
	$GLOBALS['smarty']->clearAllCache();

	/* vim:set ts=2 sw=2: */
 ?>
