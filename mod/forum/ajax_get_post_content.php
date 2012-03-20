<?php

require_once('../../config.php');
require_once('lib.php');
$id = required_param('id', PARAM_INT);

if ( ($post = forum_get_post_full($id)) === false )
{
    echo '<div style="padding:5px 0px 5px 40px;">This post does not exist or was deleted, please refresh the page.</div>';
    exit;
}

global $USER;

$discussion = $DB->get_record('forum_discussions', array('id' => $post->discussion), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $discussion->course), '*', MUST_EXIST);
$forum = $DB->get_record('forum', array('id' => $discussion->forum), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);

require_course_login($course, true, $cm);

$modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);
$canreply = forum_user_can_post($forum, $discussion, $USER, $cm, $course, $modcontext);
$canrate = has_capability('mod/forum:rate', $modcontext);
if (!$canreply and $forum->type !== 'news') {
    if (isguestuser() or !isloggedin()) {
        $canreply = true;
    }
    if (!is_enrolled($modcontext) and !is_viewing($modcontext)) {
        // allow guests and not-logged-in to see the link - they are prompted to log in after clicking the link
        // normal users with temporary guest access see this link too, they are asked to enrol instead
        $canreply = enrol_selfenrol_available($course->id);
    }
}

$ownpost = ($post->userid == $USER->id);

//forum_print_post($post, $discussion, $forum, $cm, $course, $ownpost, true);
forum_print_discussion($course, $cm, $forum, $discussion, $post, FORUM_MODE_THREADED, $canreply, $canrate, $showallposts=true);