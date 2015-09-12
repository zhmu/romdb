<?php
	require('lib.inc.php');

	$guid = isset($_REQUEST['guid']) ? $_REQUEST['guid'] : null;
	if (is_null($guid) || !is_numeric($guid)) {
		Header("404 Not found");
		exit;
	}

	// Fetch the title's base information
	$title = db_query_single("SELECT sn.content AS name,shn.content AS note FROM title t LEFT OUTER JOIN sys_shortnote shn ON shn.id=t.guid JOIN sys_name sn ON sn.id=t.guid WHERE t.guid=:guid AND t.guid=sn.id", array("guid" => $guid));
	if ($title === false) {
		Header("404 Not found");
		exit;
	}

	// Fetch all abilities (damage, attribute bonus etc)
	$attrs = db_query_multi("SELECT swet.id,swet.content AS name,tw.value FROM title_weareq tw,sys_weareqtype swet WHERE tw.guid=:guid AND tw.typeid=swet.id ORDER BY tw.ordernum ASC", array("guid" => $guid));
	$title['attrs'] = is_array($attrs) ? $attrs : array();
	$title['note'] = isset($title['note']) ? resolve_textstring($title['note']) : "";

	$t = $GLOBALS['smarty'];
	$t->assign('i', $title);
	$t->display('title-hover.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
