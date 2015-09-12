<?php
	require('lib.inc.php');

	$t = $GLOBALS['smarty'];
	$t->assign('tab', array(
		'url' => 'stat-tab.php',
		'hoverurl' => 'stat-hover.php',
		'infourl' => 'stat-info.php',
		'columns' => array('Name', 'Rarity'),
		'searchbox' => true,
		'defaultsort' => 0,
		'defaultorder' => 1,
	));
	$t->display('stats.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
