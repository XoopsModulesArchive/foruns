<?php

/***************************************************************************
 * admin_priv_forums.php  -  description
 * -------------------
 * begin                : Thu 12 Jan 2001
 * copyright            : (C) 2001 The phpBB Group
 * email                : support@phpbb.com
 *
 * $Id: admin_priv_forums.php,v 1.2 2006/02/11 17:03:17 mikhail Exp $
 ***************************************************************************/

/***************************************************************************
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 ***************************************************************************/
require dirname(__DIR__, 3) . '/include/cp_header.php';
require dirname(__DIR__) . '/functions.php';
require dirname(__DIR__) . '/config.php';
$myts = MyTextSanitizer::getInstance();
xoops_cp_header();
echo "<table width='100%' border='0' cellspacing='1' class='outer'>" . '<tr><td class="odd">';
echo "<a href='./index.php'><h4>" . _MD_A_FORUMCONF . '</h4></a>';
extract($_GET);
if (isset($_POST)) {
    foreach ($_POST as $key => $value) {
        ${$key} = $value;
    }
}

if (!$op) {
    // No opcode passed. Show list of private forums.?>

    <form action="<?php echo $HTTP_SERVER_VARS['PHP_SELF']; ?>" method="post">
    <table border="0" cellpadding="1" cellspacing="0" align="center" width="95%">
        <tr>
            <td class='bg2'>
                <table border="0" cellpadding="1" cellspacing="1" width="100%">
                    <tr class='bg3' align='left'>
                        <td align="center" colspan="2">
								<span class='fg2'>
									<b><?php _MD_A_SAFTE; ?></b>
								</span>
                        </td>
                    </tr>
                    <tr class='bg1' align='left'>
                        <td align="center" colspan="2">
                            <select name="forum" size="0">
                                <?php

                                $sql = 'SELECT forum_name, forum_id FROM ' . $xoopsDB->prefix('bb_forums') . ' WHERE forum_type = 1 ORDER BY forum_id';

    if (!$result = $xoopsDB->query($sql)) {
        echo '</td></tr></table>';

        xoops_cp_footer();

        exit();
    }

    if ($myrow = $xoopsDB->fetchArray($result)) {
        do {
            $name = htmlspecialchars($myrow['forum_name'], ENT_QUOTES | ENT_HTML5);

            echo '<option value="' . $myrow['forum_id'] . "\">$name</option>\n";
        } while (false !== ($myrow = $xoopsDB->fetchArray($result)));
    } else {
        echo '<option value="-1">' . _MD_A_NFID . "</option>\n";
    } ?>
                            </select>
                        </td>
                    </tr>
                    <tr class='bg3' Align="left">
                        <td align="center" colspan="2">
                            <input type="hidden" name="op" value="showform">
                            <input type="submit" name="submit" value="<?php echo _MD_A_EDIT; ?>">&nbsp;&nbsp;
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>


    <?php
} else {
        // Opcode exists. See what it is, do stuff.

        if ('adduser' == $op) {
            // Add user(s) to the list for this forum.

            if ($userids) {
                while (list($null, $curr_userid) = each($_POST['userids'])) {
                    $sql = 'INSERT INTO ' . $xoopsDB->prefix('bb_forum_access') . " (forum_id, user_id, can_post) VALUES ($forum, $curr_userid, 0)";

                    if (!$result = $xoopsDB->query($sql)) {
                        echo '</td></tr></table>';

                        xoops_cp_footer();

                        exit();
                    }
                }
            }

            $op = 'showform';
        } elseif ('deluser' == $op) {
            // Remove a user from the list for this forum.

            $sql = sprintf('DELETE FROM %s WHERE forum_id = %u AND user_id = %u', $xoopsDB->prefix('bb_forum_access'), $forum, $op_userid);

            if (!$result = $xoopsDB->query($sql)) {
                echo '</td></tr></table>';

                xoops_cp_footer();

                exit();
            }

            $op = 'showform';
        } elseif ('clearusers' == $op) {
            // Remove all users from the list for this forum.

            $sql = sprintf('DELETE FROM %s WHERE forum_id = %u', $xoopsDB->prefix('bb_forum_access'), $forum);

            if (!$result = $xoopsDB->query($sql)) {
                echo '</td></tr></table>';

                xoops_cp_footer();

                exit();
            }

            $op = 'showform';
        } elseif ('grantuserpost' == $op) {
            // Add posting rights for this user in this forum.

            $sql = sprintf('UPDATE %s SET can_post=1 WHERE forum_id = %u AND user_id = %u', $xoopsDB->prefix('bb_forum_access'), $forum, $op_userid);

            if (!$result = $xoopsDB->query($sql)) {
                echo '</td></tr></table>';

                xoops_cp_footer();

                exit();
            }

            $op = 'showform';
        } elseif ('revokeuserpost' == $op) {
            // Revoke posting rights for this user in this forum.

            $sql = 'UPDATE ' . $xoopsDB->prefix('bb_forum_access') . " SET can_post=0 WHERE forum_id = $forum AND user_id = $op_userid";

            if (!$result = $xoopsDB->query($sql)) {
                echo '</td></tr></table>';

                xoops_cp_footer();

                exit();
            }

            $op = 'showform';
        }

        // We want this one to be available even after one of the above blocks has executed.

        // The above blocks will set $op to "showform" on success, so it goes right back to the form.

        // Neato. This is really slick.

        if ('showform' == $op) {
            // Show the form for the given forum.

            $sql = 'SELECT forum_name FROM ' . $xoopsDB->prefix('bb_forums') . " WHERE forum_id = $forum";

            if ((!$result = $xoopsDB->query($sql)) || (-1 == $forum)) {
                echo '</td></tr></table>';

                xoops_cp_footer();

                exit();
            }

            $forum_name = '';

            if ($row = $xoopsDB->fetchArray($result)) {
                $forum_name = htmlspecialchars($row['forum_name'], ENT_QUOTES | ENT_HTML5);
            } ?>
        <br>&nbsp;
        <table border="0" cellpadding="1" cellspacing="1" align="center" width="95%">
            <tr>
                <td class='bg2'>
                    <table border="0" cellpadding="3" cellspacing="0" width="100%">
                        <tr class='bg3' align="left">
                            <td colspan="3" align="center"><?php printf(_MD_A_EFPF, $forum_name); ?></td>
                        </tr>
                        <tr>
                            <td class='bg3' align='center' width='40%'>
                                <form action="<?php echo $HTTP_SERVER_VARS['PHP_SELF']; ?>" method="post">
                                    <b><?php echo _MD_A_UWOA; ?></b>
                            </td>
                            <td class='bg3' align='center' width='20%'>
                                &nbsp;
                            </td>
                            <td class='bg3' align='center'>
                                <b><?php echo _MD_A_UWA; ?></b>
                            </td>
                        </tr>

                        <tr>
                            <td valign="top" class='bg1' align='center' width='40%'>
                                <select name="userids[]" size="10" multiple='multiple' style="width: 100px;">
                                    <?php
                                    $sql = 'SELECT u.uid FROM ' . $xoopsDB->prefix('users') . ' u, ' . $xoopsDB->prefix('bb_forum_access') . " f WHERE u.uid = f.user_id AND f.forum_id = $forum";

            if (!$result = $xoopsDB->query($sql)) {
                echo '</td></tr></table>';

                xoops_cp_footer();

                exit();
            }

            $current_users = [];

            while (false !== ($row = $xoopsDB->fetchArray($result))) {
                $current_users[] = $row[uid];
            }

            $sql = 'SELECT uid, uname FROM ' . $xoopsDB->prefix('users') . ' WHERE (uid > 0 AND level > 0)';

            while (list($null, $curr_userid) = each($current_users)) {
                $sql .= "AND (uid != $curr_userid) ";
            }

            $sql .= 'ORDER BY uname ASC';

            if (!$result = $xoopsDB->query($sql)) {
                echo '</td></tr></table>';

                xoops_cp_footer();

                exit();
            }

            while (false !== ($row = $xoopsDB->fetchArray($result))) {
                ?>
                                        <option value="<?php echo $row['uid'] ?>"> <?php echo $row['uname'] ?> </option>
                                        <?php
            } ?>
                                </select>
                            </td>
                            <td class='bg1' align='center' valign="top">

                                <input type="hidden" name="op" value="adduser">
                                <input type="hidden" name="forum" value="<?php echo $forum ?>">
                                <input type="submit" name="submit" value="<?php echo _MD_A_ADDUSERS; ?>">
        </form><br>
        <?php
        $link = $HTTP_SERVER_VARS['PHP_SELF'] . '?forum=' . $forum . '&amp;op=clearusers';

            echo myTextForm($link, _MD_A_CLEARALLUSERS); ?>
        </td>
        <td valign="top" class='bg1' align='center'>
            <?php
            $sql = 'SELECT u.uname, u.uid, f.can_post FROM ' . $xoopsDB->prefix('users') . ' u, ' . $xoopsDB->prefix('bb_forum_access') . " f WHERE u.uid = f.user_id AND f.forum_id = $forum ORDER BY u.uid ASC";

            if (!$result = $xoopsDB->query($sql)) {
                echo '</td></tr></table>';

                xoops_cp_footer();

                exit();
            } ?>
            <table border="0" cellpadding="10" cellspacing="0">

                <?php
                while (false !== ($row = $xoopsDB->fetchArray($result))) {
                    $post_text = ($row['can_post']) ? 'can' : "can't";

                    $post_text .= ' post';

                    //$post_toggle_link = "<a href=\"".$HTTP_SERVER_VARS['PHP_SELF']."?forum=$forum&op_userid=".$row['uid']."&op=";

                    $post_toggle_link = XOOPS_URL . "/modules/foruns/admin/admin_priv_forums.php?forum=$forum&op_userid=" . $row['uid'] . '&op=';

                    if ($row['can_post']) {
                        $post_toggle_link .= 'revokeuserpost';

                        $post_toggle_link = myTextForm($post_toggle_link, _MD_A_REVOKEPOSTING);
                    } else {
                        $post_toggle_link .= 'grantuserpost';

                        $post_toggle_link = myTextForm($post_toggle_link, _MD_A_GRANTPOSTING);
                    }

                    $remove_link = myTextForm(XOOPS_URL . "/modules/foruns/admin/admin_priv_forums.php?forum=$forum&amp;op=deluser&amp;op_userid=" . $row['uid'], _MD_A_REMOVE); ?>
                    <tr>
                        <td><b><?php echo $row['uname'] ?></b>
                            (<?php echo $post_text ?>)
                            <?php echo $post_toggle_link ?>
                            <?php echo $remove_link ?></td>
                    </tr>
                    <?php
                } ?>
            </table>
        </td>
        </tr>
        </table>
        </form>
        </td></tr></table>
        <?php
        } // end of big opcode if/else block.
    }

//}

echo '</td></tr></table>';
xoops_cp_footer();
?>
