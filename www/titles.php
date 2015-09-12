<?php
	require('lib.inc.php');

	$t = $GLOBALS['smarty'];
	$t->assign('tab', array(
		'url' => 'title-tab.php',
		'hoverurl' => 'title-hover.php',
		'infourl' => 'title-info.php',
		'columns' => array('Name', 'Rarity'),
		'searchbox' => true,
		'defaultsort' => 0,
		'defaultorder' => 1,
	));
	$t->display('titles.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
