<?php
	require('lib.inc.php');

	$guid = isset($_REQUEST['guid']) ? $_REQUEST['guid'] : null;
	if (is_null($guid) || !is_numeric($guid)) {
		Header("404 Not found");
		exit;
	}

	// Fetch the recipe's base information
	$recipe = db_query_single("SELECT r.cost FROM recipe r WHERE r.guid=:guid", array("guid" => $guid));
	if ($recipe === false) {
		Header("404 Not found");
		exit;
	}

	// Fetch all required elements
	$recipe['source'] = db_query_multi("SELECT rs.source,rs.count,sn.content AS name,ai.imageid FROM recipe_source rs,sys_name sn,all_imageid ai WHERE rs.guid=:guid AND rs.source=sn.id AND ai.guid=rs.source ORDER BY rs.ordernum", array("guid" => $guid));

	$t = $GLOBALS['smarty'];
	$t->assign('i', $recipe);
	$t->display('recipe-hover.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
