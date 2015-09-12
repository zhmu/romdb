<?php
	require('lib.inc.php');

	$guid = isset($_REQUEST['guid']) ? $_REQUEST['guid'] : null;
	if (is_null($guid) || !is_numeric($guid)) {
		Header("404 Not found");
		exit;
	}
	$type_fields = array("small", "large");
	$type = array_by_index($type_fields, isset($_REQUEST['type']) ? $_REQUEST['type'] : null);

	$r = db_query_single("SELECT NULL FROM image WHERE guid=:guid", array("guid" => $guid));
	if ($r === false) {
		Header("404 Not found");
		exit;
	}
	$fname = "img/$type/$guid.png";
	if (!file_exists($fname)) {
		Header("404 Not found");
		exit;
	}

	Header("Content-Type: image/png");
	$fp = fopen($fname, "rb") or die;
	echo fread($fp, filesize($fname));
	fclose($fp);

	/* vim:set ts=2 sw=2: */
 ?>
