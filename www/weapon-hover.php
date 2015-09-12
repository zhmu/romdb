<?php
	require('lib.inc.php');

	// sys_weareqtype.id -> w.var to place the value in (if it exists)
	$weapon_var = array(
		25 => "pdmg",
		191 => "mdmg"
	);

	$guid = isset($_REQUEST['guid']) ? $_REQUEST['guid'] : null;
	if (is_null($guid) || !is_numeric($guid)) {
		Header("404 Not found");
		exit;
	}

	// Fetch the weapon's base information
	$weapon = db_query_single("SELECT sn.content AS name,w.rare,w.limitlv,wt.content AS type,wp.content AS position,w.attackspeed FROM weapon w,sys_name sn,sys_weapontype wt,sys_weaponpos wp WHERE w.guid=:guid AND sn.id=w.guid AND w.weapontype=wt.id AND w.weaponpos=wp.id", array("guid" => $guid));
	if ($weapon === false) {
		Header("404 Not found");
		exit;
	}
	$weapon['attackspeed'] = sprintf("%.1f", $weapon['attackspeed'] / 10);

	// Fetch all abilities (damage, attribute bonus etc)
	$r = db_query_multi("SELECT swet.id,swet.content AS name,ww.value FROM weapon_weareq ww,sys_weareqtype swet WHERE ww.guid=:guid AND ww.typeid=swet.id ORDER BY ww.ordernum ASC", array("guid" => $guid));
	$attr = array();
	foreach ($r as $v) {
		$vname = isset($weapon_var[$v['id']]) ? $weapon_var[$v['id']] : null;
		if (!is_null($vname)) {
			$weapon[$vname] = $v['value'];
		} else {
			array_push($attr, $v);
		}
	}
	$weapon['attrs'] = $attr;
	$weapon['tier'] = calc_tier($weapon['limitlv'], $weapon['rare']);

	$t = $GLOBALS['smarty'];
	$t->assign('i', $weapon);
	$t->display('weapon-hover.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
