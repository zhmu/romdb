<?php
	require('lib.inc.php');

	$sort_fields = array("name", "level");
	$order_fields = array("asc", "desc");
	$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';

	$order = array_by_index($order_fields, isset($_REQUEST['order']) ? $_REQUEST['order'] : null);
	$sort = array_by_index($sort_fields, isset($_REQUEST['sort']) ? $_REQUEST['sort'] : null);
	$page = isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) && $_REQUEST['page'] >= 1 && $_REQUEST['page'] <= 1000 ? $_REQUEST['page'] : 1;

	$r = db_query_single("SELECT COUNT(r.guid) AS total FROM rune r,sys_name s WHERE r.guid=s.id AND s.content LIKE :search", array("search" => '%' . $search . '%'));
	$total_runes = $r['total'];

	$runes = db_query_multi("SELECT r.guid,r.imageid,s.content AS name,r.level+1 AS level FROM rune r,sys_name s WHERE r.guid=s.id AND s.content LIKE :search ORDER BY $sort $order,r.guid $order LIMIT " . RESULTS_PER_PAGE . " OFFSET " . ($page - 1) * RESULTS_PER_PAGE, array("search" => '%' . $search . '%'));

	$t = $GLOBALS['smarty'];
	$t->assign('runes', is_array($runes) ? $runes : array());

	$first = ($page - 1) * RESULTS_PER_PAGE + 1;
	$last = $first + count($runes);

	$t->assign('info', array(
		'first' => $first,
		'last' => $last,
		'next' => $last < $total_runes,
		'prev' => $first > 1,
		'total' => $total_runes,
	));
	$t->display('runes-tab.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
