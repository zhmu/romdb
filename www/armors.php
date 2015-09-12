<?php
	require('lib.inc.php');

	$armor_limits = db_query_multi("SELECT at.id,at.content AS name FROM sys_armortype at ORDER BY name ASC");

	$t = $GLOBALS['smarty'];
	$t->assign('limits', $armor_limits);
	$t->assign('tab', array(
		'url' => 'armor-tab.php',
		'hoverurl' => 'armor-hover.php',
		'filterurl' => 'armor-filter.php',
		'linkurl' => 'armor-info.php',
		'columns' => array('Name', 'Level', 'Position'),
		'searchbox' => true,
		'defaultsort' => 1,
		'defaultorder' => 0,
	));
	$t->display('armors.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
