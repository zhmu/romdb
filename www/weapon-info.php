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
	$refinement = isset($_REQUEST['refinement']) && is_numeric($_REQUEST['refinement']) && $_REQUEST['refinement'] >= 0 && $_REQUEST['refinement'] <= 20 ? $_REQUEST['refinement'] : 0;

	// Fetch the weapon's base information
	$weapon = db_query_single("SELECT sn.content AS name,w.rare,w.limitlv,wt.content AS type,wp.content AS position,w.attackspeed,w.cost,w.refinetableid FROM weapon w,sys_name sn,sys_weapontype wt,sys_weaponpos wp WHERE w.guid=:guid AND sn.id=w.guid AND w.weapontype=wt.id AND w.weaponpos=wp.id", array("guid" => $guid));
	if ($weapon === false) {
		Header("404 Not found");
		exit;
	}
	$weapon['guid'] = $guid;
	$weapon['refinement'] = $refinement;
	$weapon['attackspeed'] = sprintf("%.1f", $weapon['attackspeed'] / 10);
	$weapon['cost'] = ceil($weapon['cost'] / 10);

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

	// Fetch refinements, if we have any (plussing)
	$weapon['refine'] = isset($weapon['refinetableid']) ? get_refinements($weapon['refinetableid']) : array();

	// If any refinement is activated, append it to our base stats
	if ($refinement > 0) {
		foreach ($weapon['refine'][$refinement] as $v) {
			// Check if we already have this as base stat
			$found = false;
			foreach ($weapon['attrs'] as &$a) {
				if ($a['id'] == $v['id']) {
					$a['value'] += $v['value'];
					$found = true;
					break;
				}
			}
			if (!$found) {
				// Maybe in the base attributes?
				$vname = isset($weapon_var[$v['id']]) ? $weapon_var[$v['id']] : null;
				if (!is_null($vname)) {
					$weapon[$vname] += $v['value'];
				} else {
					array_push($weapon['attrs'], $v); // what a coincidence - same format :-)
				}
			}
		}
	}

	// Fetch drop rates
	$weapon['drop'] = db_query_multi("SELECT dl.guid,sn.content AS name,dl.rate*100 AS rate FROM npc_droplist dl,sys_name sn WHERE dl.item=:guid AND sn.id=dl.guid ORDER BY dl.rate DESC", array("guid" => $guid));

	$t = $GLOBALS['smarty'];
	$t->assign('i', $weapon);
	$t->display('weapon-info.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
