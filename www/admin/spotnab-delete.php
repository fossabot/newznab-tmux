<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'smarty.php';


use nntmux\SpotNab;

$page = new AdminPage();

if (isset($_GET['id']))
{
	$spotnab = new SpotNab();
	$spotnab->deleteSource($_GET['id']);
}

$referrer = $_SERVER['HTTP_REFERER'];
header("Location: " . $referrer);

