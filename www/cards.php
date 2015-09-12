<?php
	require('lib.inc.php');

	$t = $GLOBALS['smarty'];
	$t->assign('tab', array(
		'url' => 'card-tab.php',
		'hoverurl' => 'card-hover.php',
		'infourl' => 'card-info.php',
		'columns' => array('Name'),
		'searchbox' => true,
		'defaultsort' => 0,
		'defaultorder' => 1,
	));
	$t->display('cards.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
