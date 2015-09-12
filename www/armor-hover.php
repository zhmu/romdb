<?php
	require('lib.inc.php');

	// sys_weareqtype.id -> w.var to place the value in (if it exists)
	$armor_var = array(
		13 => "pdef",
		14 => "mdef"
	);

	$guid = isset($_REQUEST['guid']) ? $_REQUEST['guid'] : null;
	if (is_null($guid) || !is_numeric($guid)) {
		Header("404 Not found");
		exit;
	}

	// Fetch the armor's base information
	$armor = db_query_single("SELECT sn.content AS name,a.rare,a.limitlv,at.content AS type,ap.content AS position FROM armor a,sys_name sn,sys_armortype at,sys_armorpos ap WHERE a.guid=:guid AND sn.id=a.guid AND a.armortype=at.id AND a.armorpos=ap.id", array("guid" => $guid));
	if ($armor === false) {
		Header("404 Not found");
		exit;
	}

	// Fetch all abilities (defense, attribute bonus etc)
	$r = db_query_multi("SELECT swet.id,swet.content AS name,aw.value FROM armor_weareq aw,sys_weareqtype swet WHERE aw.guid=:guid AND aw.typeid=swet.id ORDER BY aw.ordernum ASC", array("guid" => $guid));
	$attr = array();
	foreach ($r as $v) {
		$vname = isset($armor_var[$v['id']]) ? $armor_var[$v['id']] : null;
		if (!is_null($vname)) {
			$armor[$vname] = $v['value'];
		} else {
			array_push($attr, $v);
		}
	}
	$armor['attrs'] = $attr;
	$armor['tier'] = calc_tier($armor['limitlv'], $armor['rare']);

	$t = $GLOBALS['smarty'];
	$t->assign('i', $armor);
	$t->display('armor-hover.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
