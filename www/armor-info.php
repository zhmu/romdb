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
	$refinement = isset($_REQUEST['refinement']) && is_numeric($_REQUEST['refinement']) && $_REQUEST['refinement'] >= 0 && $_REQUEST['refinement'] <= 20 ? $_REQUEST['refinement'] : 0;

	// Fetch the armor's base information
	$armor = db_query_single("SELECT sn.content AS name,a.rare,a.limitlv,at.content AS type,ap.content AS position,a.refinetableid,a.cost FROM armor a,sys_name sn,sys_armortype at,sys_armorpos ap WHERE a.guid=:guid AND sn.id=a.guid AND a.armortype=at.id AND a.armorpos=ap.id", array("guid" => $guid));
	if ($armor === false) {
		Header("404 Not found");
		exit;
	}
	$armor['guid'] = $guid;
	$armor['refinement'] = $refinement;
	$armor['cost'] = ceil($armor['cost'] / 10);

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
	// Fetch refinements, if we have any (plussing)
	$armor['refine'] = isset($armor['refinetableid']) ? get_refinements($armor['refinetableid']) : array();

	// If any refinement is activated, append it to our base stats
	if ($refinement > 0) {
		foreach ($armor['refine'][$refinement] as $v) {
			// Check if we already have this as base stat
			$found = false;
			foreach ($armor['attrs'] as &$a) {
				if ($a['id'] == $v['id']) {
					$a['value'] += $v['value'];
					$found = true;
					break;
				}
			}
			if (!$found) {
				// Maybe in the base attributes?
				$vname = isset($armor_var[$v['id']]) ? $armor_var[$v['id']] : null;
				if (!is_null($vname)) {
					$armor[$vname] += $v['value'];
				} else {
					array_push($armor['attrs'], $v); // what a coincidence - same format :-)
				}
			}
		}
	}

	// Fetch drop rates
	$armor['drop'] = db_query_multi("SELECT dl.guid,sn.content AS name,dl.rate*100 AS rate FROM npc_droplist dl,sys_name sn WHERE dl.item=:guid AND sn.id=dl.guid ORDER BY dl.rate DESC", array("guid" => $guid));

	$t = $GLOBALS['smarty'];
	$t->assign('i', $armor);
	$t->display('armor-info.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
