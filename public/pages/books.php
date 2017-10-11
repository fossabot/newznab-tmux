<?php

if (! $page->users->isLoggedIn()) {
    $page->show403();
}

use nntmux\Books;
use nntmux\Category;
use nntmux\DnzbFailures;

$book = new Books(['Settings' => $page->settings]);
$cat = new Category(['Settings' => $page->settings]);
$fail = new DnzbFailures(['Settings' => $page->settings]);

$boocats = $cat->getChildren(Category::BOOKS_ROOT);
$btmp = [];
foreach ($boocats as $bcat) {
    $btmp[$bcat['id']] = $bcat;
}
$category = Category::BOOKS_ROOT;
if (isset($_REQUEST['t']) && array_key_exists($_REQUEST['t'], $btmp)) {
    $category = $_REQUEST['t'] + 0;
}

$catarray = [];
$catarray[] = $category;

$page->smarty->assign('catlist', $btmp);
$page->smarty->assign('category', $category);

$offset = (isset($_REQUEST['offset']) && ctype_digit($_REQUEST['offset'])) ? $_REQUEST['offset'] : 0;
$ordering = $book->getBookOrdering();
$orderby = isset($_REQUEST['ob']) && in_array($_REQUEST['ob'], $ordering, false) ? $_REQUEST['ob'] : '';

$books = [];
$results = $book->getBookRange($catarray, $offset, ITEMS_PER_COVER_PAGE, $orderby, $page->userdata['categoryexclusions']);

$maxwords = 50;
foreach ($results as $result) {
    if (! empty($result['overview'])) {
        $words = explode(' ', $result['overview']);
        if (count($words) > $maxwords) {
            $newwords = array_slice($words, 0, $maxwords);
            $result['overview'] = implode(' ', $newwords).'...';
        }
    }
    $books[] = $result;
}

$author = (isset($_REQUEST['author']) && ! empty($_REQUEST['author'])) ? stripslashes($_REQUEST['author']) : '';
$page->smarty->assign('author', $author);

$title = (isset($_REQUEST['title']) && ! empty($_REQUEST['title'])) ? stripslashes($_REQUEST['title']) : '';
$page->smarty->assign('title', $title);

$browseby_link = '&amp;title='.$title.'&amp;author='.$author;

$page->smarty->assign('pagertotalitems', $results[0]['_totalcount'] ?? 0);
$page->smarty->assign('pageroffset', $offset);
$page->smarty->assign('pageritemsperpage', ITEMS_PER_COVER_PAGE);
$page->smarty->assign('pagerquerybase', WWW_TOP.'/books?t='.$category.$browseby_link.'&amp;ob='.$orderby.'&amp;offset=');
$page->smarty->assign('pagerquerysuffix', '#results');

$pager = $page->smarty->fetch('pager.tpl');
$page->smarty->assign('pager', $pager);

if ((int) $category === -1) {
    $page->smarty->assign('catname', 'All');
} else {
    $cdata = $cat->getById($category);
    if ($cdata) {
        $page->smarty->assign('catname', $cdata->parent !== null ? $cdata->parent->title.' > '.$cdata->title : $cdata->title);
    } else {
        $page->show404();
    }
}

foreach ($ordering as $ordertype) {
    $page->smarty->assign('orderby'.$ordertype, WWW_TOP.'/books?t='.$category.$browseby_link.'&amp;ob='.$ordertype.'&amp;offset=0');
}

$page->smarty->assign('results', $books);

$page->meta_title = 'Browse Books';
$page->meta_keywords = 'browse,nzb,books,description,details';
$page->meta_description = 'Browse for Books';

$page->content = $page->smarty->fetch('books.tpl');
$page->render();
