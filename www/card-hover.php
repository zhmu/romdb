<?php
	require('lib.inc.php');

	$guid = isset($_REQUEST['guid']) ? $_REQUEST['guid'] : null;
	if (is_null($guid) || !is_numeric($guid)) {
		Header("404 Not found");
		exit;
	}

	// Fetch the card's base information
	$card = db_query_single("SELECT sn.content AS name,c.cardaddpower,c.rare FROM card c,sys_name sn WHERE c.guid=:guid AND c.cardorgobjid=sn.id", array("guid" => $guid));
	if ($title === false) {
		Header("404 Not found");
		exit;
	}

	// Fetch all abilities (damage, attribute bonus etc)
	$card['attrs'] = db_query_multi("SELECT swet.id,swet.content AS name,aw.value FROM addpower_weareq aw,sys_weareqtype swet WHERE aw.guid=:guid AND aw.typeid=swet.id ORDER BY aw.ordernum ASC", array("guid" => $card['cardaddpower']));

	$t = $GLOBALS['smarty'];
	$t->assign('i', $card);
	$t->display('card-hover.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
