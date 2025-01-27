<?php
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr8
 *
 *  License: MIT
 *
 *  Delete post page
 */

if (!$user->isLoggedIn()) {
    Redirect::to(URL::build('/forum'));
}

// Always define page name
const PAGE = 'forum';

$forum = new Forum();

// Check params are set
if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    Redirect::to(URL::build('/forum'));
}

// Get post and forum ID
$post = $queries->getWhere('posts', ['id', '=', $_GET['pid']]);
if (!count($post)) {
    Redirect::to(URL::build('/forum'));
}
$post = $post[0];

$forum_id = $post->forum_id;

if ($forum->canModerateForum($forum_id, $user->getAllGroupIds())) {
    if (Input::exists()) {
        if (Token::check()) {
            if (isset($_POST['tid'])) {
                // Is it the OP?
                if (isset($_POST['number']) && Input::get('number') == 10) {

                    $queries->update('topics', Input::get('tid'), [
                        'deleted' => 1
                    ]);

                    Log::getInstance()->log(Log::Action('forums/post/delete'), Input::get('tid'));
                    $opening_post = 1;

                    $redirect = URL::build('/forum'); // Create a redirect string
                } else {
                    $redirect = URL::build('/forum/topic/' . urlencode(Input::get('tid')));
                }
            } else {
                $redirect = URL::build('/forum/search/', 'p=1&s=' . urlencode($_POST['search_string']));
            }

            $queries->update('posts', Input::get('pid'), [
                'deleted' => 1
            ]);

            if (isset($opening_post)) {
                $posts = $queries->getWhere('posts', ['topic_id', '=', $_POST['tid']]);

                if (count($posts)) {
                    foreach ($posts as $post) {
                        $queries->update('posts', $post->id, [
                            'deleted' => 1
                        ]);
                        Log::getInstance()->log(Log::Action('forums/post/delete'), $post->id);
                    }
                }
            }

            // Update latest posts in categories
            $forum->updateForumLatestPosts();
            $forum->updateTopicLatestPosts();

            Redirect::to($redirect);

        } else {
            Redirect::to(URL::build('/forum/topic/' . urlencode(Input::get('tid'))));
        }
    } else {
        echo 'No post selected';
    }
} else {
    Redirect::to(URL::build('/forum'));
}
die();
