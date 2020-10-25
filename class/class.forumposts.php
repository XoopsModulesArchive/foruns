<?php

// $Id: class.forumposts.php,v 1.5 2006/02/18 23:39:07 mikhail Exp $
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

require_once XOOPS_ROOT_PATH . '/class/xoopstree.php';

class ForumPosts
{
    public $post_id;

    public $topic_id;

    public $forum_id;

    public $post_time;

    public $poster_ip;

    public $order;

    public $subject;

    public $post_text;

    public $pid;

    public $nohtml = 0;

    public $nosmiley = 0;

    public $uid;

    public $icon;

    public $attachsig;

    public $prefix;

    public $db;

    public $istopic = false;

    public $islocked = false;

    public function __construct($id = null)
    {
        $this->db = XoopsDatabaseFactory::getDatabaseConnection();

        if (is_array($id)) {
            $this->makePost($id);
        } elseif (isset($id)) {
            $this->getPost((int)$id);
        }
    }

    public function setTopicId($value)
    {
        $this->topic_id = $value;
    }

    public function getTopicId()
    {
        return $this->topic_id ?? 0;
    }

    public function setOrder($value)
    {
        $this->order = $value;
    }

    // 2004-1-12 GIJOE <gij@peak.ne.jp> Added routine to move to the correct

    // starting position within a topic thread

    public function &getAllPosts($topic_id, $order, $perpage, &$start, $post_id = 0)
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();

        if ('DESC' == $order) {
            $operator_for_position = '>';
        } else {
            $order = 'ASC';

            $operator_for_position = '<';
        }

        if ($perpage <= 0) {
            $perpage = 10;
        }

        if (empty($start)) {
            $start = 0;
        }

        if (!empty($post_id)) {
            $result = $db->query('SELECT COUNT(post_id) FROM ' . $db->prefix('bb_posts') . " WHERE topic_id=$topic_id AND post_id $operator_for_position $post_id");

            [$position] = $db->fetchRow($result);

            $start = (int)($position / $perpage) * $perpage;
        }

        $sql = 'SELECT p.*, t.post_text FROM ' . $db->prefix('bb_posts') . ' p, ' . $db->prefix('bb_posts_text') . " t WHERE p.topic_id=$topic_id AND p.post_id = t.post_id ORDER BY p.post_id $order";

        $result = $db->query($sql, $perpage, $start);

        $ret = [];

        while (false !== ($myrow = $db->fetchArray($result))) {
            $ret[] = new self($myrow);
        }

        return $ret;
    }

    public function setParent($value)
    {
        $this->pid = $value;
    }

    public function setSubject($value)
    {
        $this->subject = $value;
    }

    public function setText($value)
    {
        $this->post_text = $value;
    }

    public function setUid($value)
    {
        $this->uid = $value;
    }

    public function setForum($value)
    {
        $this->forum_id = $value;
    }

    public function setIp($value)
    {
        $this->poster_ip = $value;
    }

    public function setNohtml($value = 0)
    {
        $this->nohtml = $value;
    }

    public function setNosmiley($value = 0)
    {
        $this->nosmiley = $value;
    }

    public function setIcon($value)
    {
        $this->icon = $value;
    }

    public function setAttachsig($value)
    {
        $this->attachsig = $value;
    }

    public function store()
    {
        $myts = MyTextSanitizer::getInstance();

        $subject = $myts->censorString($this->subject);

        $post_text = $myts->censorString($this->post_text);

        $subject = $myts->addSlashes($subject);

        $post_text = $myts->addSlashes($post_text);

        if (empty($this->post_id)) {
            if (empty($this->topic_id)) {
                $this->topic_id = $this->db->genId($this->db->prefix('bb_topics') . '_topic_id_seq');

                $datetime = time();

                $sql = 'INSERT INTO ' . $this->db->prefix('bb_topics') . ' (topic_id, topic_title, topic_poster, forum_id, topic_time) VALUES (' . $this->topic_id . ",'$subject', " . $this->uid . ', ' . $this->forum_id . ", $datetime)";

                if (!$result = $this->db->query($sql)) {
                    return false;
                }

                if (0 == $this->topic_id) {
                    $this->topic_id = $this->db->getInsertId();
                }
            }

            if (!isset($this->nohtml) || 1 != $this->nohtml) {
                $this->nohtml = 0;
            }

            if (!isset($this->nosmiley) || 1 != $this->nosmiley) {
                $this->nosmiley = 0;
            }

            if (!isset($this->attachsig) || 1 != $this->attachsig) {
                $this->attachsig = 0;
            }

            $this->post_id = $this->db->genId($this->db->prefix('bb_posts') . '_post_id_seq');

            $datetime = time();

            $sql = sprintf(
                "INSERT INTO %s (post_id, pid, topic_id, forum_id, post_time, uid, poster_ip, subject, nohtml, nosmiley, icon, attachsig) VALUES (%u, %u, %u, %u, %u, %u, '%s', '%s', %u, %u, '%s', %u)",
                $this->db->prefix('bb_posts'),
                $this->post_id,
                $this->pid,
                $this->topic_id,
                $this->forum_id,
                $datetime,
                $this->uid,
                $this->poster_ip,
                $subject,
                $this->nohtml,
                $this->nosmiley,
                $this->icon,
                $this->attachsig
            );

            if (!$result = $this->db->query($sql)) {
                return false;
            }

            if (0 == $this->post_id) {
                $this->post_id = $this->db->getInsertId();
            }

            $sql = sprintf("INSERT INTO %s (post_id, post_text) VALUES (%u, '%s')", $this->db->prefix('bb_posts_text'), $this->post_id, $post_text);

            if (!$result = $this->db->query($sql)) {
                $sql = sprintf('DELETE FROM %s WHERE post_id = %u', $this->db->prefix('bb_posts'), $this->post_id);

                $this->db->query($sql);

                return false;
            }

            if (0 == $this->pid) {
                $sql = sprintf('UPDATE %s SET topic_last_post_id = %u, topic_time = %u WHERE topic_id = %u', $this->db->prefix('bb_topics'), $this->post_id, $datetime, $this->topic_id);

                if (!$result = $this->db->query($sql)) {
                }

                $sql = sprintf('UPDATE %s SET forum_posts = forum_posts+1, forum_topics = forum_topics+1, forum_last_post_id = %u WHERE forum_id = %u', $this->db->prefix('bb_forums'), $this->post_id, $this->forum_id);

                $result = $this->db->query($sql);

                if (!$result) {
                }
            } else {
                $sql = 'UPDATE ' . $this->db->prefix('bb_topics') . ' SET topic_replies=topic_replies+1, topic_last_post_id = ' . $this->post_id . ", topic_time = $datetime WHERE topic_id =" . $this->topic_id . '';

                if (!$result = $this->db->query($sql)) {
                }

                $sql = 'UPDATE ' . $this->db->prefix('bb_forums') . ' SET forum_posts = forum_posts+1, forum_last_post_id = ' . $this->post_id . ' WHERE forum_id = ' . $this->forum_id . '';

                $result = $this->db->query($sql);

                if (!$result) {
                }
            }
        } else {
            if ($this->istopic()) {
                $sql = 'UPDATE ' . $this->db->prefix('bb_topics') . " SET topic_title = '$subject' WHERE topic_id = " . $this->topic_id . '';

                if (!$result = $this->db->query($sql)) {
                    return false;
                }
            }

            if (!isset($this->nohtml) || 1 != $this->nohtml) {
                $this->nohtml = 0;
            }

            if (!isset($this->nosmiley) || 1 != $this->nosmiley) {
                $this->nosmiley = 0;
            }

            if (!isset($this->attachsig) || 1 != $this->attachsig) {
                $this->attachsig = 0;
            }

            $sql = 'UPDATE ' . $this->db->prefix('bb_posts') . " SET subject='" . $subject . "', nohtml=" . $this->nohtml . ', nosmiley=' . $this->nosmiley . ", icon='" . $this->icon . "', attachsig=" . $this->attachsig . ' WHERE post_id=' . $this->post_id . '';

            $result = $this->db->query($sql);

            if (!$result) {
                return false;
            }

            $sql = 'UPDATE ' . $this->db->prefix('bb_posts_text') . " SET post_text = '" . $post_text . "' WHERE post_id =" . $this->post_id . '';

            $result = $this->db->query($sql);

            if (!$result) {
                return false;
            }
        }

        return $this->post_id;
    }

    public function getPost($id)
    {
        $sql = 'SELECT p.*, t.post_text, tp.topic_status FROM ' . $this->db->prefix('bb_posts') . ' p LEFT JOIN ' . $this->db->prefix('bb_posts_text') . ' t ON p.post_id=t.post_id LEFT JOIN ' . $this->db->prefix('bb_topics') . ' tp ON tp.topic_id=p.topic_id WHERE p.post_id=' . $id;

        $array = $this->db->fetchArray($this->db->query($sql));

        $this->post_id = $array['post_id'];

        $this->pid = $array['pid'];

        $this->topic_id = $array['topic_id'];

        $this->forum_id = $array['forum_id'];

        $this->post_time = $array['post_time'];

        $this->uid = $array['uid'];

        $this->poster_ip = $array['poster_ip'];

        $this->subject = $array['subject'];

        $this->nohtml = $array['nohtml'];

        $this->nosmiley = $array['nosmiley'];

        $this->icon = $array['icon'];

        $this->attachsig = $array['attachsig'];

        $this->post_text = $array['post_text'];

        if (0 == $array['pid']) {
            $this->istopic = true;
        }

        if (1 == $array['topic_status']) {
            $this->islocked = true;
        }
    }

    public function makePost($array)
    {
        foreach ($array as $key => $value) {
            $this->$key = $value;
        }
    }

    public function delete()
    {
        $sql = sprintf('DELETE FROM %s WHERE post_id = %u', $this->db->prefix('bb_posts'), $this->post_id);

        if (!$result = $this->db->query($sql)) {
            return false;
        }

        $sql = sprintf('DELETE FROM %s WHERE post_id = %u', $this->db->prefix('bb_posts_text'), $this->post_id);

        if (!$result = $this->db->query($sql)) {
            echo 'Could not remove posts text for Post ID:' . $this->post_id . '.<br>';
        }

        if (!empty($this->uid)) {
            $sql = sprintf('UPDATE %s SET posts=posts-1 WHERE uid = %u', $this->db->prefix('users'), $this->uid);

            if (!$result = $this->db->query($sql)) {
                //	echo "Could not update user posts.";
            }
        }

        if ($this->istopic()) {
            $sql = sprintf('DELETE FROM %s WHERE topic_id = %u', $this->db->prefix('bb_topics'), $this->topic_id);

            if (!$result = $this->db->query($sql)) {
                echo 'Could not delete topic.';
            }
        }

        $mytree = new XoopsTree($this->db->prefix('bb_posts'), 'post_id', 'pid');

        $arr = $mytree->getAllChild($this->post_id);

        $size = count($arr);

        if ($size > 0) {
            for ($i = 0; $i < $size; $i++) {
                $sql = sprintf('DELETE FROM %s WHERE post_id = %u', $this->db->prefix('bb_posts'), $arr[$i]['post_id']);

                if (!$result = $this->db->query($sql)) {
                    echo 'Could not delete post ' . $arr[$i]['post_id'] . '';
                }

                $sql = sprintf('DELETE FROM %s WHERE post_id = %u', $this->db->prefix('bb_posts_text'), $arr[$i]['post_id']);

                if (!$result = $this->db->query($sql)) {
                    echo 'Could not delete post text ' . $arr[$i]['post_id'] . '';
                }

                if (!empty($arr[$i]['uid'])) {
                    $sql = 'UPDATE ' . $this->db->prefix('users') . ' SET posts=posts-1 WHERE uid=' . $arr[$i]['uid'] . '';

                    if (!$result = $this->db->query($sql)) {
                        //	echo "Could not update user posts.";
                    }
                }
            }
        }
    }

    public function subject($format = 'Show')
    {
        $myts = MyTextSanitizer::getInstance();

        $smiley = 1;

        if ($this->nosmiley()) {
            $smiley = 0;
        }

        switch ($format) {
            case 'Show':
                $subject = htmlspecialchars($this->subject, $smiley);
                break;
            case 'Edit':
                $subject = htmlspecialchars($this->subject, ENT_QUOTES | ENT_HTML5);
                break;
            case 'Preview':
                $subject = htmlspecialchars($this->subject, $smiley);
                break;
            case 'InForm':
                $subject = htmlspecialchars($this->subject, ENT_QUOTES | ENT_HTML5);
                break;
        }

        return $subject;
    }

    public function text($format = 'Show')
    {
        $myts = MyTextSanitizer::getInstance();

        $smiley = 1;

        $html = 1;

        $bbcodes = 1;

        if ($this->nohtml()) {
            $html = 0;
        }

        if ($this->nosmiley()) {
            $smiley = 0;
        }

        switch ($format) {
            case 'Show':
                $text = $myts->displayTarea($this->post_text, $html, $smiley, $bbcodes);
                break;
            case 'Edit':
                $text = htmlspecialchars($this->post_text, ENT_QUOTES | ENT_HTML5);
                break;
            case 'Preview':
                $text = $myts->previewTarea($this->post_text, $html, $smiley, $bbcodes);
                break;
            case 'InForm':
                $text = htmlspecialchars($this->post_text, ENT_QUOTES | ENT_HTML5);
                break;
            case 'Quotes':
                $text = htmlspecialchars($this->post_text, ENT_QUOTES | ENT_HTML5);
                break;
        }

        return $text;
    }

    public function postid()
    {
        return $this->post_id;
    }

    public function posttime()
    {
        return $this->post_time;
    }

    public function uid()
    {
        return $this->uid;
    }

    public function uname()
    {
        return XoopsUser::getUnameFromId($this->uid);
    }

    public function posterip()
    {
        return $this->poster_ip;
    }

    public function parent()
    {
        return $this->pid;
    }

    public function topic()
    {
        return $this->topic_id;
    }

    public function nohtml()
    {
        return $this->nohtml;
    }

    public function nosmiley()
    {
        return $this->nosmiley;
    }

    public function icon()
    {
        return $this->icon;
    }

    public function forum()
    {
        return $this->forum_id;
    }

    public function attachsig()
    {
        return $this->attachsig;
    }

    public function prefix()
    {
        return $this->prefix;
    }

    public function istopic()
    {
        if ($this->istopic) {
            return true;
        }

        return false;
    }

    public function islocked()
    {
        if ($this->islocked) {
            return true;
        }

        return false;
    }
}
