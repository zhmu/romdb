<?php
	require('lib.inc.php');

	$recipe_limits = array(
		array("id" => 1, "name" => "Blacksmithing"),
		array("id" => 2, "name" => "Carpentry"),
		array("id" => 3, "name" => "Armor Crafting"),
		array("id" => 4, "name" => "Tailoring"),
		array("id" => 5, "name" => "Alchemy"),
		array("id" => 6, "name" => "Cooking"),
	);

	$t = $GLOBALS['smarty'];
	$t->assign('limits', $recipe_limits);
	$t->assign('tab', array(
		'url' => 'recipe-tab.php',
		'hoverurl' => 'recipe-hover.php',
		'hoverurl2' => 'weapon-hover.php',
		'linkurl' => 'recipe-info.php',
		'columns' => array('Name', 'Level', 'Result'),
		'searchbox' => true,
		'defaultsort' => 1,
		'defaultorder' => 0,
	));
	$t->display('recipes.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
