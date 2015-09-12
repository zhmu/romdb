<?php
	require('lib.inc.php');

	$sort_fields = array("name", "rarity");
	$order_fields = array("asc", "desc");
	$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';

	$order = array_by_index($order_fields, isset($_REQUEST['order']) ? $_REQUEST['order'] : null);
	$sort = array_by_index($sort_fields, isset($_REQUEST['sort']) ? $_REQUEST['sort'] : null);
	$page = isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) && $_REQUEST['page'] >= 1 && $_REQUEST['page'] <= 1000 ? $_REQUEST['page'] : 1;

	$r = db_query_single("SELECT COUNT(ap.guid) AS total FROM addpower ap,sys_name s WHERE ap.guid=s.id AND s.content LIKE :search", array("search" => '%' . $search . '%'));
	$total_stats = $r['total'];

	$stats = db_query_multi("SELECT ap.guid,ap.imageid,ap.rarity,s.content AS name FROM addpower ap,sys_name s WHERE ap.guid=s.id AND s.content LIKE :search ORDER BY $sort $order,ap.guid $order LIMIT " . RESULTS_PER_PAGE . " OFFSET " . ($page - 1) * RESULTS_PER_PAGE, array("search" => '%' . $search . '%'));

	# 0..2 - white, 3 = gold
	foreach ($stats as &$r) {
		$r['rare'] = $r['rarity'] < 3 ? 1 : 4;
	}

	$t = $GLOBALS['smarty'];
	$t->assign('stats', is_array($stats) ? $stats : array());

	$first = ($page - 1) * RESULTS_PER_PAGE + 1;
	$last = $first + count($stats);

	$t->assign('info', array(
		'first' => $first,
		'last' => $last,
		'next' => $last < $total_stats,
		'prev' => $first > 1,
		'total' => $total_stats,
	));
	$t->display('stats-tab.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
