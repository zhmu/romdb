<?php
	require('lib.inc.php');

	$t = $GLOBALS['smarty'];
	$t->assign('tab', array(
		'url' => 'rune-tab.php',
		'hoverurl' => 'rune-hover.php',
		'infourl' => 'rune-info.php',
		'columns' => array('Name', 'Level'),
		'searchbox' => true,
		'defaultsort' => 0,
		'defaultorder' => 1,
	));
	$t->display('runes.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
