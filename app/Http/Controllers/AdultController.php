<?php

namespace App\Http\Controllers;

use Blacklight\XXX;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdultController extends BasePageController
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Exception
     */
    public function show(Request $request, $id = '')
    {
        $this->setPrefs();
        $adult = new XXX();

        $moviecats = Category::getChildren(Category::XXX_ROOT);
        $mtmp = [];
        foreach ($moviecats as $mcat) {
            $mtmp[] =
                [
                    'id' => $mcat->id,
                    'title' => $mcat->title,
                ];
        }
        $category = Category::XXX_ROOT;
        if ($id && \in_array($id, array_pluck($mtmp, 'title'), false)) {
            $cat = Category::query()
                ->where('title', $id)
                ->where('parentid', '=', Category::XXX_ROOT)
                ->first(['id']);
            $category = $cat !== null ? $cat['id'] : Category::XXX_ROOT;
        }
        $catarray = [];
        $catarray[] = $category;

        $this->smarty->assign('catlist', $mtmp);
        $this->smarty->assign('category', $category);
        $this->smarty->assign('categorytitle', $id);

        $ordering = $adult->getXXXOrdering();
        $orderby = $request->has('ob') && \in_array($request->input('ob'), $ordering, false) ? $request->input('ob') : '';

        $movies = [];
        $page = $request->has('page') ? $request->input('page') : 1;
        $offset = ($page - 1) * config('nntmux.items_per_cover_page');
        $rslt = $adult->getXXXRange($page, $catarray, $offset, config('nntmux.items_per_cover_page'), $orderby, -1,  $this->userdata['categoryexclusions']);
        $results = new LengthAwarePaginator($rslt, $rslt['_totalcount'], config('nntmux.items_per_cover_page'), $page, ['path' => $request->url()]);
        foreach ($results as $result) {
            if (! empty($result->id)) {
                $result->genre = makeFieldLinks((array) $result, 'genre', 'movies');
                $result->actors = makeFieldLinks((array) $result, 'actors', 'movies');
                $result->director = makeFieldLinks((array) $result, 'director', 'movies');

                $movies[] = $result;
            }
        }
        $title = ($request->has('title') && ! empty($request->input('title'))) ? stripslashes($request->input('title')) : '';
        $this->smarty->assign('title', stripslashes($title));

        $actors = ($request->has('actors') && ! empty($request->input('actors'))) ? stripslashes($request->input('actors')) : '';
        $this->smarty->assign('actors', $actors);

        $director = ($request->has('director') && ! empty($request->input('director'))) ? stripslashes($request->input('director')) : '';
        $this->smarty->assign('director', $director);

        $genres = $adult->getAllGenres(true);
        $genre = ($request->has('genre') && \in_array($request->input('genre'), $genres, false)) ? $request->input('genre') : '';
        $this->smarty->assign('genres', $genres);
        $this->smarty->assign('genre', $genre);

        $browseby_link = '&amp;title='.$title.'&amp;actors='.$actors.'&amp;director='.$director.'&amp;genre='.$genre;

        if ((int) $category === -1) {
            $this->smarty->assign('catname', 'All');
        } else {
            $cdata = Category::find($category);
            if ($cdata) {
                $this->smarty->assign('catname', $cdata->parent !== null ? $cdata->parent->title.' > '.$cdata->title : $cdata->title);
            } else {
                $this->show404();
            }
        }

        foreach ($ordering as $ordertype) {
            $this->smarty->assign('orderby'.$ordertype, WWW_TOP.'/xxx?t='.$category.$browseby_link.'&amp;ob='.$ordertype.'&amp;offset=0');
        }

        $this->smarty->assign(
            [
                'resultsadd'=>  $movies,
                'results' => $results,
            ]
        );

        $meta_title = 'Browse XXX';
        $meta_keywords = 'browse,xxx,nzb,description,details';
        $meta_description = 'Browse for XXX Movies';

        if ($request->has('id')) {
            $content = $this->smarty->fetch('viewxxxfull.tpl');
        } else {
            $content = $this->smarty->fetch('xxx.tpl');
        }
        $this->smarty->assign([
            'content' => $content,
            'meta_title' => $meta_title,
            'meta_keywords' => $meta_keywords,
            'meta_description' => $meta_description,
        ]);
        $this->pagerender();
    }
}
