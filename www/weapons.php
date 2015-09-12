<?php
	require('lib.inc.php');

	$weapon_types = db_query_multi("SELECT wt.id,wt.content AS name FROM sys_weapontype wt ORDER BY name ASC");

	$t = $GLOBALS['smarty'];
	$t->assign('limits', $weapon_types);
	$t->assign('tab', array(
		'url' => 'weapon-tab.php',
		'hoverurl' => 'weapon-hover.php',
		'linkurl' => 'weapon-info.php',
		'columns' => array('Name', 'Level', 'Type'),
		'searchbox' => true,
		'defaultsort' => 1,
		'defaultorder' => 0,
	));
	$t->display('weapons.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
