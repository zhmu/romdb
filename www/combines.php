<?php
	require('lib.inc.php');

	$t = $GLOBALS['smarty'];
	$t->assign('tab', array(
		'url' => 'combines-tab.php',
		'hoverurl' => 'combine-hover.php',
		'infourl' => 'combine-info.php',
		'columns' => array('Name'),
		'searchbox' => true,
		'defaultsort' => 0,
		'defaultorder' => 1,
	));
	$t->display('combines.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
