<?php
	require('lib.inc.php');

	$guid = isset($_REQUEST['guid']) ? $_REQUEST['guid'] : null;
	if (is_null($guid) || !is_numeric($guid)) {
		Header("404 Not found");
		exit;
	}

	// Fetch the combine's base information
	$combine = db_query_single("SELECT sn.content AS name FROM item_combine ic,sys_name sn WHERE ic.guid=:guid AND ic.dstitem=sn.id", array("guid" => $guid));
	if ($title === false) {
		Header("404 Not found");
		exit;
	}

	// Fetch all source items
	$sources = db_query_multi("SELECT sn.content AS name,ics.amount FROM item_combine_src ics,sys_name sn WHERE ics.guid=:guid AND ics.item=sn.id ORDER BY ics.ordernum ASC", array("guid" => $guid));
	$combine['sources'] = is_array($sources) ? $sources : array();

	$t = $GLOBALS['smarty'];
	$t->assign('i', $combine);
	$t->display('combine-hover.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
