<?php

// $Id: index.php,v 1.6 2006/03/10 17:08:37 mikhail Exp $
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

include 'header.php';
// this page uses smarty template
// this must be set before including main header.php
$GLOBALS['xoopsOption']['template_main'] = 'foruns_index.html';
require XOOPS_ROOT_PATH . '/header.php';

$myts = MyTextSanitizer::getInstance();

$sql = 'SELECT c.* FROM ' . $xoopsDB->prefix('bb_categories') . ' c, ' . $xoopsDB->prefix('bb_forums') . ' f WHERE f.cat_id=c.cat_id GROUP BY c.cat_id, c.cat_title, c.cat_order ORDER BY c.cat_order';
if (!$result = $xoopsDB->query($sql)) {
    redirect_header(XOOPS_URL . '/', 1, _MD_ERROROCCURED);

    exit();
}

$xoopsTpl->assign(
    [
        'lang_welcomemsg' => sprintf(_MD_WELCOME, $xoopsConfig['sitename']),
'lang_tostart' => _MD_TOSTART,
'lang_totaltopics' => _MD_TOTALTOPICSC,
'lang_totalposts' => _MD_TOTALPOSTSC,
'total_topics' => get_total_topics(),
'total_posts' => get_total_posts(0, 'all'),
'lang_lastvisit' => sprintf(_MD_LASTVISIT, formatTimestamp($last_visit)),
'lang_currenttime' => sprintf(_MD_TIMENOW, formatTimestamp(time(), 'm')),
'lang_forum' => _MD_FORUM,
'lang_topics' => _MD_TOPICS,
'lang_posts' => _MD_POSTS,
'lang_lastpost' => _MD_LASTPOST,
'lang_moderators' => _MD_MODERATOR,
    ]
);

$viewcat = (!empty($_GET['cat'])) ? (int)$_GET['cat'] : 0;
$categories = [];
while (false !== ($cat_row = $xoopsDB->fetchArray($result))) {
    $categories[] = $cat_row;
}

$sql = 'SELECT f.*, u.uname, u.uid, p.topic_id, p.post_time, p.subject, p.icon FROM ' . $xoopsDB->prefix('bb_forums') . ' f LEFT JOIN ' . $xoopsDB->prefix('bb_posts') . ' p ON p.post_id = f.forum_last_post_id LEFT JOIN ' . $xoopsDB->prefix('users') . ' u ON u.uid = p.uid';
if (0 != $viewcat) {
    $sql .= ' WHERE f.cat_id = ' . $viewcat;

    $xoopsTpl->assign('forum_index_title', sprintf(_MD_FORUMINDEX, $xoopsConfig['sitename']));
} else {
    $xoopsTpl->assign('forum_index_title', '');
}
$sql .= ' ORDER BY f.cat_id, f.forum_id';
if (!$result = $xoopsDB->query($sql)) {
    exit('Error');
}
$forums = []; // RMV-FIX
while (false !== ($forum_data = $xoopsDB->fetchArray($result))) {
    $forums[] = $forum_data;
}
$cat_count = count($categories);
if ($cat_count > 0) {
    for ($i = 0; $i < $cat_count; $i++) {
        $categories[$i]['cat_title'] = htmlspecialchars($categories[$i]['cat_title'], ENT_QUOTES | ENT_HTML5);

        if (0 != $viewcat && $categories[$i]['cat_id'] != $viewcat) {
            $xoopsTpl->append('categories', $categories[$i]);

            continue;
        }

        // Read 'lastread' cookie, if exists

        $topic_lastread = !empty($HTTP_COOKIE_VARS['foruns_topic_lastread']) ? unserialize($HTTP_COOKIE_VARS['foruns_topic_lastread']) : [];

        foreach ($forums as $forum_row) {
            unset($last_post);

            if ($forum_row['cat_id'] == $categories[$i]['cat_id']) {
                if ($forum_row['post_time']) {
                    //$forum_row['subject'] = htmlspecialchars($forum_row['subject']);

                    $categories[$i]['forums']['forum_lastpost_time'][] = formatTimestamp($forum_row['post_time']);

                    $last_post_icon = '<a href="'
                                                                         . XOOPS_URL
                                                                         . '/modules/foruns/viewtopic.php?post_id='
                                                                         . $forum_row['forum_last_post_id']
                                                                         . '&amp;topic_id='
                                                                         . $forum_row['topic_id']
                                                                         . '&amp;forum='
                                                                         . $forum_row['forum_id']
                                                                         . '#forumpost'
                                                                         . $forum_row['forum_last_post_id']
                                                                         . '">';

                    if ($forum_row['icon']) {
                        $last_post_icon .= '<img src="' . XOOPS_URL . '/images/subject/' . $forum_row['icon'] . '" border="0" alt="">';
                    } else {
                        $last_post_icon .= '<img src="' . XOOPS_URL . '/images/subject/icon1.gif" width="15" height="15" border="0" alt="">';
                    }

                    $last_post_icon .= '</a>';

                    $categories[$i]['forums']['forum_lastpost_icon'][] = $last_post_icon;

                    if (0 != $forum_row['uid'] && $forum_row['uname']) {
                        $categories[$i]['forums']['forum_lastpost_user'][] = '<a href="' . XOOPS_URL . '/userinfo.php?uid=' . $forum_row['uid'] . '">' . htmlspecialchars($forum_row['uname'], ENT_QUOTES | ENT_HTML5) . '</a>';
                    } else {
                        $categories[$i]['forums']['forum_lastpost_user'][] = $xoopsConfig['anonymous'];
                    }

                    $forum_lastread = !empty($topic_lastread[$forum_row['topic_id']]) ? $topic_lastread[$forum_row['topic_id']] : false;

                    if (1 == $forum_row['forum_type']) {
                        $categories[$i]['forums']['forum_folder'][] = $bbImage['locked_forum'];
                    } elseif ($forum_row['post_time'] > $forum_lastread && !empty($forum_row['topic_id'])) {
                        $categories[$i]['forums']['forum_folder'][] = $bbImage['newposts_forum'];
                    } else {
                        $categories[$i]['forums']['forum_folder'][] = $bbImage['folder_forum'];
                    }
                } else {
                    // no forums, so put empty values

                    $categories[$i]['forums']['forum_lastpost_time'][] = '';

                    $categories[$i]['forums']['forum_lastpost_icon'][] = '';

                    $categories[$i]['forums']['forum_lastpost_user'][] = '';

                    if (1 == $forum_row['forum_type']) {
                        $categories[$i]['forums']['forum_folder'][] = $bbImage['locked_forum'];
                    } else {
                        $categories[$i]['forums']['forum_folder'][] = $bbImage['folder_forum'];
                    }
                }

                $categories[$i]['forums']['forum_id'][] = $forum_row['forum_id'];

                $categories[$i]['forums']['forum_name'][] = htmlspecialchars($forum_row['forum_name'], ENT_QUOTES | ENT_HTML5);

                $categories[$i]['forums']['forum_desc'][] = $myts->displayTarea($forum_row['forum_desc']);

                $categories[$i]['forums']['forum_topics'][] = $forum_row['forum_topics'];

                $categories[$i]['forums']['forum_posts'][] = $forum_row['forum_posts'];

                $all_moderators = get_moderators($forum_row['forum_id']);

                $count = 0;

                $forum_moderators = '';

                foreach ($all_moderators as $mods) {
                    foreach ($mods as $mod_id => $mod_name) {
                        if ($count > 0) {
                            $forum_moderators .= ', ';
                        }

                        $forum_moderators .= '<a href="' . XOOPS_URL . '/userinfo.php?uid=' . $mod_id . '">' . htmlspecialchars($mod_name, ENT_QUOTES | ENT_HTML5) . '</a>';

                        $count = 1;
                    }
                }

                $categories[$i]['forums']['forum_moderators'][] = $forum_moderators;
            }
        }

        $xoopsTpl->append('categories', $categories[$i]);
    }
} else {
    $xoopsTpl->append('categories', []);
}
$xoopsTpl->assign(
    [
        'img_hotfolder' => $bbImage['newposts_forum'],
'img_folder' => $bbImage['folder_forum'],
'img_locked' => $bbImage['locked_forum'],
'lang_newposts' => _MD_NEWPOSTS,
'lang_private' => _MD_PRIVATEFORUM,
'lang_nonewposts' => _MD_NONEWPOSTS,
'lang_search' => _MD_SEARCH,
'lang_advsearch' => _MD_ADVSEARCH,
    ]
);
require_once XOOPS_ROOT_PATH . '/footer.php';
