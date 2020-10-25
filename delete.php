<?php

// $Id: delete.php,v 1.5 2006/02/18 23:39:07 mikhail Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2006 xoopscube.org                       //
//                      <http://xoopscube.org>                           //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://xoopscube.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //

require __DIR__ . '/header.php';

$ok = 0;
$forum = isset($_GET['forum']) ? (int)$_GET['forum'] : 0;
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;
$order = isset($_GET['order']) ? (int)$_GET['order'] : 0;
$viewmode = (isset($_GET['viewmode']) && 'flat' != $_GET['viewmode']) ? 'thread' : 'flat';
extract($_POST, EXTR_OVERWRITE);
if (empty($forum)) {
    redirect_header('index.php', 2, _MD_ERRORFORUM);

    exit();
} elseif (empty($post_id)) {
    redirect_header("viewforum.php?forum=$forum", 2, _MD_ERRORPOST);

    exit();
}

if ($xoopsUser) {
    if (!$xoopsUser->isAdmin($xoopsModule->mid())) {
        if (!is_moderator($forum, $xoopsUser->uid())) {
            redirect_header("viewtopic.php?topic_id=$topic_id&order=$order&viewmode=$viewmode&pid=$pid&forum=$forum", 2, _MD_DELNOTALLOWED);

            exit();
        }
    }
} else {
    redirect_header("viewtopic.php?topic_id=$topic_id&order=$order&viewmode=$viewmode&pid=$pid&forum=$forum", 2, _MD_DELNOTALLOWED);

    exit();
}

require_once __DIR__ . '/class/class.forumposts.php';

if (!empty($ok)) {
    if (!empty($post_id)) {
        $post = new ForumPosts($post_id);

        $post->delete();

        sync($post->forum(), 'forum');

        sync($post->topic(), 'topic');
    }

    if ($post->istopic()) {
        redirect_header("viewforum.php?forum=$forum", 2, _MD_POSTSDELETED);

        exit();
    }

    redirect_header("viewtopic.php?topic_id=$topic_id&order=$order&viewmode=$viewmode&pid=$pid&forum=$forum", 2, _MD_POSTSDELETED);

    exit();
}
    require XOOPS_ROOT_PATH . '/header.php';
    xoops_confirm(['post_id' => $post_id, 'viewmode' => $viewmode, 'order' => $order, 'forum' => $forum, 'topic_id' => $topic_id, 'ok' => 1], 'delete.php', _MD_AREUSUREDEL);

require XOOPS_ROOT_PATH . '/footer.php';
