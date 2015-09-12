<?php
	require('lib.inc.php');

	$sort_fields = array("name", "limitlv", "type");
	$order_fields = array("asc", "desc");
	$limit = isset($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : 0;
	$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';

	$order = array_by_index($order_fields, isset($_REQUEST['order']) ? $_REQUEST['order'] : null);
	$sort = array_by_index($sort_fields, isset($_REQUEST['sort']) ? $_REQUEST['sort'] : null);
	$page = isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) && $_REQUEST['page'] >= 1 && $_REQUEST['page'] <= 1000 ? $_REQUEST['page'] : 1;

	$r = db_query_single("SELECT COUNT(w.guid) AS total FROM weapon w,sys_name s WHERE w.guid=s.id AND w.weapontype=:limit AND s.content LIKE :search", array("limit" => $limit, "search" => '%' . $search . '%'));
	$total_weapons = $r['total'];

	$weapons = db_query_multi("SELECT w.guid,w.rare,w.imageid,s.content AS name,w.limitlv,wt.content AS type FROM weapon w,sys_name s,sys_weapontype wt WHERE w.guid=s.id AND w.weapontype=wt.id AND w.weapontype=:limit AND s.content LIKE :search ORDER BY $sort $order,w.rare $order,w.guid $order LIMIT " . RESULTS_PER_PAGE . " OFFSET " . ($page - 1) * RESULTS_PER_PAGE, array("limit" => $limit, "search" => '%' . $search . '%'));

	$t = $GLOBALS['smarty'];
	$t->assign('weapons', is_array($weapons) ? $weapons : array());

	$first = ($page - 1) * RESULTS_PER_PAGE + 1;
	$last = $first + count($weapons);

	$t->assign('info', array(
		'first' => $first,
		'last' => $last,
		'next' => $last < $total_weapons,
		'prev' => $first > 1,
		'total' => $total_weapons,
	));
	$t->display('weapon-tab.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
