<?php
require_once './config.php';
require_once(WWW_DIR . "/lib/adminpage.php");
require_once(WWW_DIR . "/lib/Regexes.php");

$page = new AdminPage();

$page->title = "Collections Regex Test";

$tpg = $page->site->tablepergroup;
$page->smarty->assign('tpg', $tpg);

if ($tpg) {

	$group = trim(isset($_POST['group']) && !empty($_POST['group']) ? $_POST['group'] : '');
	$regex = trim(isset($_POST['regex']) && !empty($_POST['regex']) ? $_POST['regex'] : '');
	$limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) ? $_POST['limit'] : 50);
	$page->smarty->assign(['group' => $group, 'regex' => $regex, 'limit' => $limit]);

	if ($group && $regex) {
		$page->smarty->assign('data', (new Regexes(['Settings' => $page->settings, 'Table_Name' => 'collection_regexes']))->testCollectionRegex($group, $regex, $limit));
	}

}

$page->content = $page->smarty->fetch('collection_regexes-test.tpl');
$page->render();