<?php
	require('lib.inc.php');

	$guid = isset($_REQUEST['guid']) ? $_REQUEST['guid'] : null;
	if (is_null($guid) || !is_numeric($guid)) {
		Header("404 Not found");
		exit;
	}

	// Fetch the weapon's base information
	$rune = db_query_single("SELECT sn.content AS name,r.level+1 AS tier FROM rune r,sys_name sn WHERE r.guid=:guid AND sn.id=r.guid", array("guid" => $guid));
	if ($rune === false) {
		Header("404 Not found");
		exit;
	}

	// Fetch all abilities (damage, attribute bonus etc)
	$rune['attrs'] = db_query_multi("SELECT swet.id,swet.content AS name,rw.value FROM rune_weareq rw,sys_weareqtype swet WHERE rw.guid=:guid AND rw.typeid=swet.id ORDER BY rw.ordernum ASC", array("guid" => $guid));

	$t = $GLOBALS['smarty'];
	$t->assign('i', $rune);
	$t->display('rune-hover.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
