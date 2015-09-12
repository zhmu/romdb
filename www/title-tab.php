<?php
	require('lib.inc.php');

	$sort_fields = array("name", "rarity");
	$order_fields = array("asc", "desc");
	$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';

	$order = array_by_index($order_fields, isset($_REQUEST['order']) ? $_REQUEST['order'] : null);
	$sort = array_by_index($sort_fields, isset($_REQUEST['sort']) ? $_REQUEST['sort'] : null);
	$page = isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) && $_REQUEST['page'] >= 1 && $_REQUEST['page'] <= 1000 ? $_REQUEST['page'] : 1;

	$t = db_query_single("SELECT COUNT(t.guid) AS total FROM title t,sys_name s WHERE t.guid=s.id AND s.content LIKE :search", array("search" => '%' . $search . '%'));
	$total_titles = $t['total'];

	$titles  = db_query_multi("SELECT t.guid,t.imageid,s.content AS name,t.rare FROM title t,sys_name s WHERE t.guid=s.id AND s.content LIKE :search ORDER BY $sort $order,t.guid $order LIMIT " . RESULTS_PER_PAGE . " OFFSET " . ($page - 1) * RESULTS_PER_PAGE, array("search" => '%' . $search . '%'));

	$t = $GLOBALS['smarty'];
	$t->assign('titles', is_array($titles) ? $titles : array());

	$first = ($page - 1) * RESULTS_PER_PAGE + 1;
	$last = $first + count($titles);

	$t->assign('info', array(
		'first' => $first,
		'last' => $last,
		'next' => $last < $total_titles,
		'prev' => $first > 1,
		'total' => $total_titles,
	));
	$t->display('title-tab.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
