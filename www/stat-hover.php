<?php
	require('lib.inc.php');

	$guid = isset($_REQUEST['guid']) ? $_REQUEST['guid'] : null;
	if (is_null($guid) || !is_numeric($guid)) {
		Header("404 Not found");
		exit;
	}

	// Fetch the weapon's base information
	$stat = db_query_single("SELECT sn.content AS name FROM addpower ap,sys_name sn WHERE ap.guid=:guid AND sn.id=ap.guid", array("guid" => $guid));
	if ($stat === false) {
		Header("404 Not found");
		exit;
	}

	// Fetch all abilities (damage, attribute bonus etc)
	$stat['attrs'] = db_query_multi("SELECT swet.id,swet.content AS name,aw.value FROM addpower_weareq aw,sys_weareqtype swet WHERE aw.guid=:guid AND aw.typeid=swet.id ORDER BY aw.ordernum ASC", array("guid" => $guid));

	$t = $GLOBALS['smarty'];
	$t->assign('i', $stat);
	$t->display('stat-hover.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
