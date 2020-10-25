#
# Table structure for table `bb_categories`
#

CREATE TABLE bb_categories (
    cat_id    SMALLINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
    cat_title VARCHAR(100)         NOT NULL DEFAULT '',
    cat_order VARCHAR(10)                   DEFAULT NULL,
    PRIMARY KEY (cat_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `bb_forum_access`
#

CREATE TABLE bb_forum_access (
    forum_id INT(4) UNSIGNED NOT NULL DEFAULT '0',
    user_id  INT(5) UNSIGNED NOT NULL DEFAULT '0',
    can_post TINYINT(1)      NOT NULL DEFAULT '0',
    PRIMARY KEY (forum_id, user_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `bb_forum_mods`
#

CREATE TABLE bb_forum_mods (
    forum_id INT(4) UNSIGNED NOT NULL DEFAULT '0',
    user_id  INT(5) UNSIGNED NOT NULL DEFAULT '0',
    KEY forum_user_id (forum_id, user_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `bb_forums`
#

CREATE TABLE bb_forums (
    forum_id           INT(4) UNSIGNED NOT NULL AUTO_INCREMENT,
    forum_name         VARCHAR(150)    NOT NULL DEFAULT '',
    forum_desc         TEXT,
    forum_access       TINYINT(2)      NOT NULL DEFAULT '1',
    forum_moderator    INT(2)                   DEFAULT NULL,
    forum_topics       INT(8)          NOT NULL DEFAULT '0',
    forum_posts        INT(8)          NOT NULL DEFAULT '0',
    forum_last_post_id INT(5) UNSIGNED NOT NULL DEFAULT '0',
    cat_id             INT(2)          NOT NULL DEFAULT '0',
    forum_type         INT(10)                  DEFAULT '0',
    allow_html         ENUM ('0','1')           DEFAULT '0' NOT NULL,
    allow_sig          ENUM ('0','1')           DEFAULT '0' NOT NULL,
    posts_per_page     TINYINT(3) UNSIGNED      DEFAULT '20' NOT NULL,
    hot_threshold      TINYINT(3) UNSIGNED      DEFAULT '10' NOT NULL,
    topics_per_page    TINYINT(3) UNSIGNED      DEFAULT '20' NOT NULL,
    PRIMARY KEY (forum_id),
    KEY forum_last_post_id (forum_last_post_id),
    KEY cat_id (cat_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `bb_posts`
#

CREATE TABLE bb_posts (
    post_id   INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    pid       INT(8)          NOT NULL DEFAULT '0',
    topic_id  INT(8)          NOT NULL DEFAULT '0',
    forum_id  INT(4)          NOT NULL DEFAULT '0',
    post_time INT(10)         NOT NULL DEFAULT '0',
    uid       INT(5) UNSIGNED NOT NULL DEFAULT '0',
    poster_ip VARCHAR(15)     NOT NULL DEFAULT '',
    subject   VARCHAR(255)    NOT NULL DEFAULT '',
    nohtml    TINYINT(1)      NOT NULL DEFAULT '0',
    nosmiley  TINYINT(1)      NOT NULL DEFAULT '0',
    icon      VARCHAR(25)     NOT NULL DEFAULT '',
    attachsig TINYINT(1)      NOT NULL DEFAULT '0',
    PRIMARY KEY (post_id),
    KEY uid (uid),
    KEY pid (pid),
    KEY subject (subject(40)),
    KEY forumid_uid (forum_id, uid),
    KEY topicid_uid (topic_id, uid),
    KEY topicid_postid_pid (topic_id, post_id, pid),
    FULLTEXT KEY search (subject)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `bb_posts_text`
#

CREATE TABLE bb_posts_text (
    post_id   INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    post_text TEXT,
    PRIMARY KEY (post_id),
    FULLTEXT KEY search (post_text)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `bb_topics`
#

CREATE TABLE bb_topics (
    topic_id           INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    topic_title        VARCHAR(255)             DEFAULT NULL,
    topic_poster       INT(5)          NOT NULL DEFAULT '0',
    topic_time         INT(10)         NOT NULL DEFAULT '0',
    topic_views        INT(5)          NOT NULL DEFAULT '0',
    topic_replies      INT(4)          NOT NULL DEFAULT '0',
    topic_last_post_id INT(8) UNSIGNED NOT NULL DEFAULT '0',
    forum_id           INT(4)          NOT NULL DEFAULT '0',
    topic_status       TINYINT(1)      NOT NULL DEFAULT '0',
    topic_sticky       TINYINT(1)      NOT NULL DEFAULT '0',
    PRIMARY KEY (topic_id),
    KEY forum_id (forum_id),
    KEY topic_last_post_id (topic_last_post_id),
    KEY topic_poster (topic_poster),
    KEY topic_forum (topic_id, forum_id),
    KEY topic_sticky (topic_sticky)
)
    ENGINE = ISAM;
