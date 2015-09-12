<?php
	require('lib.inc.php');

	$sort_fields = array("name", "limitlv", "pos");
	$order_fields = array("asc", "desc");
	// Split 'filter' on ',' and throw away anything that isn't numeric
	$filter = array_filter(explode(",", isset($_REQUEST['filter']) ? $_REQUEST['filter'] : ""), 'is_numeric');
	// And convert it to '{1,2,...}' for use in ...=ANY($filter)
	$filter = '{' . implode(',', $filter) . '}';

	$order = array_by_index($order_fields, isset($_REQUEST['order']) ? $_REQUEST['order'] : null);
	$sort = array_by_index($sort_fields, isset($_REQUEST['sort']) ? $_REQUEST['sort'] : null);
	$page = isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) && $_REQUEST['page'] >= 1 && $_REQUEST['page'] <= 1000 ? $_REQUEST['page'] : 1;
	$limit = isset($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : 0;
	$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';

	$r = db_query_single("SELECT COUNT(a.guid) AS total FROM armor a,sys_name s WHERE a.guid=s.id AND a.armortype=:limit AND a.armorpos=ANY(:filter) AND s.content LIKE :search", array("limit" => $limit, "filter" => $filter, "search" => '%' . $search . '%'));
	$total_armors = $r['total'];

	$armors = db_query_multi("SELECT a.guid,a.rare,a.imageid,s.content AS name,a.limitlv,at.content AS type,ap.content AS pos FROM armor a,sys_name s,sys_armortype at,sys_armorpos ap WHERE a.guid=s.id AND a.armortype=at.id AND a.armortype=:limit AND a.armorpos=ap.id AND a.armorpos=ANY(:filter) AND s.content LIKE :search ORDER BY $sort $order,a.rare $order,a.guid $order LIMIT " . RESULTS_PER_PAGE . " OFFSET " . ($page - 1) * RESULTS_PER_PAGE, array("limit" => $limit, "filter" => $filter, "search" => '%' . $search . '%'));

	$t = $GLOBALS['smarty'];
	$t->assign('armors', is_array($armors) ? $armors : array());

	$first = ($page - 1) * RESULTS_PER_PAGE + 1;
	$last = $first + count($armors);

	$t->assign('info', array(
		'first' => $first,
		'last' => $last,
		'next' => $last < $total_armors,
		'prev' => $first > 1,
		'total' => $total_armors,
	));
	$t->display('armor-tab.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
