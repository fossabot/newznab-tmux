<?php

namespace App\Http\Controllers;

use Blacklight\Contents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContentController extends BasePageController
{
    public function show(Request $request)
    {
        $this->setPrefs();
        $contents = new Contents();

        $role = 0;
        if (! empty($this->userdata) && Auth::check()) {
            $role = $this->userdata['role'];
        }

        /* The role column in the content table values are :
         * 1 = logged in users
         * 2 = admins
         *
         * The user role values are:
         * 1 = user
         * 2 = admin
         * 3 = disabled
         * 4 = moderator
         *
         * Admins and mods should be the only ones to see admin content.
         */
        $this->smarty->assign('admin', (($role === 2 || $role === 4) ? 'true' : 'false'));

        $contentId = 0;
        if ($request->has('id')) {
            $contentId = $request->input('id');
        }

        $contentPage = false;
        if ($request->has('page')) {
            $contentPage = $request->input('page');
        }

        if ($contentId === 0 && $contentPage === 'content') {
            $content = $contents->getAllButFront();
            $this->smarty->assign('front', false);
            $meta_title = 'Contents page';
            $meta_keywords = 'contents';
            $meta_description = 'This is the contents page.';
        } elseif ($contentId !== 0 && $contentPage !== false) {
            $content = [$contents->getByID($contentId, $role)];
            $this->smarty->assign('front', false);
            $meta_title = 'Contents page';
            $meta_keywords = 'contents';
            $meta_description = 'This is the contents page.';
        } else {
            $content = $contents->getFrontPage();
            $index = $contents->getIndex();
            $this->smarty->assign('front', true);
            $meta_title = $index->title;
            $meta_keywords = $index->metakeywords;
            $meta_description = $index->metadescription;
        }

        if (empty($content)) {
            $this->show404();
        }

        $this->smarty->assign('content', $content);

        $content = $this->smarty->fetch('content.tpl');
        $this->smarty->assign(
            [
                'content' => $content,
                'meta_title' => $meta_title,
                'meta_keywords' => $meta_keywords,
                'meta_description' => $meta_description,
            ]
        );
        $this->pagerender();
    }
}
