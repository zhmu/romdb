<?php
	require('lib.inc.php');

	$sort_fields = array("name");
	$order_fields = array("asc", "desc");
	$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';

	$order = array_by_index($order_fields, isset($_REQUEST['order']) ? $_REQUEST['order'] : null);
	$sort = array_by_index($sort_fields, isset($_REQUEST['sort']) ? $_REQUEST['sort'] : null);
	$page = isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) && $_REQUEST['page'] >= 1 && $_REQUEST['page'] <= 1000 ? $_REQUEST['page'] : 1;

	$r = db_query_single("SELECT COUNT(ic.guid) AS total FROM item_combine ic,sys_name s WHERE ic.dstitem=s.id AND s.content LIKE :search", array("search" => '%' . $search . '%'));
	$total_combines = $r['total'];

	$combines  = db_query_multi("SELECT ic.guid,s.content AS name FROM item_combine ic,sys_name s WHERE ic.dstitem=s.id AND s.content LIKE :search ORDER BY $sort $order,ic.guid $order LIMIT " . RESULTS_PER_PAGE . " OFFSET " . ($page - 1) * RESULTS_PER_PAGE, array("search" => '%' . $search . '%'));

	$t = $GLOBALS['smarty'];
	$t->assign('combines', is_array($combines) ? $combines : array());

	$first = ($page - 1) * RESULTS_PER_PAGE + 1;
	$last = $first + count($combines);

	$t->assign('info', array(
		'first' => $first,
		'last' => $last,
		'next' => $last < $total_combines,
		'prev' => $first > 1,
		'total' => $total_combines,
	));
	$t->display('combines-tab.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
