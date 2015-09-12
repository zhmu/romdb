<?php
	require('lib.inc.php');

	$sort_fields = array("name");
	$order_fields = array("asc", "desc");
	$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';

	$order = array_by_index($order_fields, isset($_REQUEST['order']) ? $_REQUEST['order'] : null);
	$sort = array_by_index($sort_fields, isset($_REQUEST['sort']) ? $_REQUEST['sort'] : null);
	$page = isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) && $_REQUEST['page'] >= 1 && $_REQUEST['page'] <= 1000 ? $_REQUEST['page'] : 1;

	$t = db_query_single("SELECT COUNT(c.guid) AS total FROM card c,sys_name s WHERE c.cardorgobjid=s.id AND s.content LIKE :search", array("search" => '%' . $search . '%'));
	$total_cards = $t['total'];

	$cards  = db_query_multi("SELECT c.guid,c.imageid,s.content AS name,c.rare FROM card c,sys_name s WHERE c.cardorgobjid=s.id AND s.content LIKE :search ORDER BY $sort $order,c.guid $order LIMIT " . RESULTS_PER_PAGE . " OFFSET " . ($page - 1) * RESULTS_PER_PAGE, array("search" => '%' . $search . '%'));

	$t = $GLOBALS['smarty'];
	$t->assign('cards', is_array($cards) ? $cards : array());

	$first = ($page - 1) * RESULTS_PER_PAGE + 1;
	$last = $first + count($cards);

	$t->assign('info', array(
		'first' => $first,
		'last' => $last,
		'next' => $last < $total_cards,
		'prev' => $first > 1,
		'total' => $total_cards,
	));
	$t->display('card-tab.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
