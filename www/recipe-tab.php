<?php
	require('lib.inc.php');

	$sort_fields = array("name", "requestskilllv");
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

	$r = db_query_single("SELECT COUNT(ci.guid) AS total FROM craftable_item ci,sys_name sn,recipe r WHERE ci.guid=sn.id AND r.guid=ci.recipe");
	$total_recipes = $r['total'];

	$recipes = db_query_multi("SELECT ci.recipe AS guid,sn.content AS name,r.requestskilllv,ai.imageid FROM craftable_item ci,sys_name sn,recipe r,all_imageid ai WHERE ci.guid=sn.id AND r.guid=ci.recipe AND r.requestcraftobjtype=:limit AND ai.guid=ci.guid ORDER BY $sort $order LIMIT " . RESULTS_PER_PAGE . " OFFSET " . ($page - 1) * RESULTS_PER_PAGE, array("limit" => $limit));

	// Per recipe, grab what it will create
	foreach ($recipes as &$r) {
		$r['items'] = db_query_multi("SELECT i.guid,i.rate,i.count,sn.content AS name,ai.imageid FROM recipe_itemslot i,sys_name sn,all_imageid ai WHERE i.guid=:guid AND i.item=sn.id AND ai.guid=i.item ORDER BY i.ordernum", array("guid" => $r['guid']));
	}

	$t = $GLOBALS['smarty'];
	$t->assign('recipes', is_array($recipes) ? $recipes : array());

	$first = ($page - 1) * RESULTS_PER_PAGE + 1;
	$last = $first + count($recipes);

	$t->assign('info', array(
		'first' => $first,
		'last' => $last,
		'next' => $last < $total_recipes,
		'prev' => $first > 1,
		'total' => $total_recipes,
	));
	$t->display('recipe-tab.tpl');

	/* vim:set ts=2 sw=2: */
 ?>
