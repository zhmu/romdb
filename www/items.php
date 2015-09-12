<?php
	require('lib.inc.php');

	$t = $GLOBALS['smarty'];
	$t->assign('tab', array(
		'url' => 'item-tab.php',
		'columns' => array('Name', 'Level')
	));
	$t->display('items.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
