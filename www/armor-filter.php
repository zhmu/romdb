<?php
	require('lib.inc.php');

	$limit = isset($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : 0;

	$armor_filter = db_query_multi("SELECT ap.id,ap.content AS name FROM sys_armorpos ap ORDER BY name ASC");
	foreach ($armor_filter as &$a) {
		$have_items = db_query_single("SELECT NULL FROM armor WHERE armorpos=:armorpos AND armortype=:type", array("armorpos" => $a['id'], "type" => $limit));
		$a['empty'] = $have_items === false;
	}

	$t = $GLOBALS['smarty'];
	$t->assign('types', $armor_filter);
	$t->display('filter.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
