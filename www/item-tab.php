<?php
	require('lib.inc.php');

	$sort_fields = array("name", "limitlv");
	$order_fields = array("asc", "desc");

	$order = array_by_index($order_fields, isset($_REQUEST['order']) ? $_REQUEST['order'] : null);
	$sort = array_by_index($sort_fields, isset($_REQUEST['sort']) ? $_REQUEST['sort'] : null);
	$page = isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) && $_REQUEST['page'] >= 1 && $_REQUEST['page'] <= 1000 ? $_REQUEST['page'] : 1;

	$r = db_query_single("SELECT COUNT(i.guid) AS total FROM item i,sys_name s WHERE i.guid=s.id");
	$total_items = $r['total'];

	$items = db_query_multi("SELECT i.guid,i.limitlv,i.imageid,s.content AS name FROM item i,sys_name s WHERE i.guid=s.id ORDER BY $sort $order,i.guid $order LIMIT " . RESULTS_PER_PAGE . " OFFSET " . ($page - 1) * RESULTS_PER_PAGE);

	$t = $GLOBALS['smarty'];
	$t->assign('items', $items);

	$first = ($page - 1) * RESULTS_PER_PAGE + 1;
	$last = $first + count($items);

	$t->assign('info', array(
		'first' => $first,
		'last' => $last,
		'next' => $last < $total_items,
		'prev' => $first > 1,
		'total' => $total_items,
	));
	$t->display('items-tab.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
