<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace local_chuspasocial\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_tag_tag;

/**
 * Privacy provider for the ChuspaSocial plugin.
 *
 * @package    local_chuspasocial
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Describes the personal data that the plugin stores.
     *
     * @param collection $collection The collection to add the metadata to.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_chuspasocial_post', [
            'userid' => 'privacy:metadata:post:userid',
            'courseid' => 'privacy:metadata:post:courseid',
            'type' => 'privacy:metadata:post:type',
            'content' => 'privacy:metadata:post:content',
            'hidden' => 'privacy:metadata:post:hidden',
            'timecreated' => 'privacy:metadata:post:timecreated',
            'timemodified' => 'privacy:metadata:post:timemodified',
        ], 'privacy:metadata:local_chuspasocial_post');

        $collection->add_database_table('local_chuspasocial_comment', [
            'postid' => 'privacy:metadata:comment:postid',
            'userid' => 'privacy:metadata:comment:userid',
            'content' => 'privacy:metadata:comment:content',
            'timecreated' => 'privacy:metadata:comment:timecreated',
            'timemodified' => 'privacy:metadata:comment:timemodified',
        ], 'privacy:metadata:local_chuspasocial_comment');

        $collection->add_database_table('local_chuspasocial_reaction', [
            'postid' => 'privacy:metadata:reaction:postid',
            'userid' => 'privacy:metadata:reaction:userid',
            'type' => 'privacy:metadata:reaction:type',
            'timecreated' => 'privacy:metadata:reaction:timecreated',
        ], 'privacy:metadata:local_chuspasocial_reaction');

        $collection->add_database_table('local_chuspasocial_report', [
            'itemtype' => 'privacy:metadata:report:itemtype',
            'itemid' => 'privacy:metadata:report:itemid',
            'reporterid' => 'privacy:metadata:report:reporterid',
            'reason' => 'privacy:metadata:report:reason',
            'status' => 'privacy:metadata:report:status',
            'resolvedby' => 'privacy:metadata:report:resolvedby',
            'timecreated' => 'privacy:metadata:report:timecreated',
            'timeresolved' => 'privacy:metadata:report:timeresolved',
        ], 'privacy:metadata:local_chuspasocial_report');

        $collection->add_database_table('local_chuspasocial_follow', [
            'userid' => 'privacy:metadata:follow:userid',
            'itemtype' => 'privacy:metadata:follow:itemtype',
            'itemid' => 'privacy:metadata:follow:itemid',
            'timecreated' => 'privacy:metadata:follow:timecreated',
        ], 'privacy:metadata:local_chuspasocial_follow');

        $collection->add_database_table('local_chuspasocial_listing', [
            'userid' => 'privacy:metadata:listing:userid',
            'title' => 'privacy:metadata:listing:title',
            'author' => 'privacy:metadata:listing:author',
            'description' => 'privacy:metadata:listing:description',
            'price' => 'privacy:metadata:listing:price',
            'externalcontact' => 'privacy:metadata:listing:externalcontact',
            'status' => 'privacy:metadata:listing:status',
            'timecreated' => 'privacy:metadata:listing:timecreated',
            'timemodified' => 'privacy:metadata:listing:timemodified',
        ], 'privacy:metadata:local_chuspasocial_listing');

        $collection->add_subsystem_link('core_tag', [], 'privacy:metadata:core_tag');
        $collection->add_subsystem_link('core_files', [], 'privacy:metadata:core_files');

        return $collection;
    }

    /**
     * Gets the contexts that contain data of a user.
     *
     * A course context is returned when the user has posts, comments, reactions or reports in the
     * course. The system context is returned when the user has data in the general wall, follows,
     * book listings or reports about listings.
     *
     * @param int $userid The id of the user.
     * @return contextlist The list of contexts with data of the user.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // Course contexts.
        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {local_chuspasocial_post} p ON p.courseid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextcourse
                   AND (p.userid = :postuser
                        OR EXISTS (SELECT 1
                                     FROM {local_chuspasocial_comment} c
                                    WHERE c.postid = p.id AND c.userid = :commentuser)
                        OR EXISTS (SELECT 1
                                     FROM {local_chuspasocial_reaction} r
                                    WHERE r.postid = p.id AND r.userid = :reactionuser)
                        OR EXISTS (SELECT 1
                                     FROM {local_chuspasocial_report} rp
                                    WHERE rp.itemtype = 'post' AND rp.itemid = p.id
                                          AND rp.reporterid = :postreporter)
                        OR EXISTS (SELECT 1
                                     FROM {local_chuspasocial_report} rc
                                     JOIN {local_chuspasocial_comment} cc ON cc.id = rc.itemid
                                    WHERE rc.itemtype = 'comment' AND cc.postid = p.id
                                          AND rc.reporterid = :commentreporter))";
        $contextlist->add_from_sql($sql, [
            'contextcourse' => CONTEXT_COURSE,
            'postuser' => $userid,
            'commentuser' => $userid,
            'reactionuser' => $userid,
            'postreporter' => $userid,
            'commentreporter' => $userid,
        ]);

        // System context.
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                 WHERE ctx.contextlevel = :contextsystem
                   AND (EXISTS (SELECT 1
                                  FROM {local_chuspasocial_post} p
                                 WHERE p.courseid = 0 AND p.userid = :postuser)
                        OR EXISTS (SELECT 1
                                     FROM {local_chuspasocial_comment} c
                                     JOIN {local_chuspasocial_post} cp ON cp.id = c.postid
                                    WHERE cp.courseid = 0 AND c.userid = :commentuser)
                        OR EXISTS (SELECT 1
                                     FROM {local_chuspasocial_reaction} r
                                     JOIN {local_chuspasocial_post} rpost ON rpost.id = r.postid
                                    WHERE rpost.courseid = 0 AND r.userid = :reactionuser)
                        OR EXISTS (SELECT 1
                                     FROM {local_chuspasocial_report} rp
                                     JOIN {local_chuspasocial_post} rppost ON rppost.id = rp.itemid
                                    WHERE rp.itemtype = 'post' AND rppost.courseid = 0
                                          AND rp.reporterid = :postreporter)
                        OR EXISTS (SELECT 1
                                     FROM {local_chuspasocial_report} rc
                                     JOIN {local_chuspasocial_comment} rcc ON rcc.id = rc.itemid
                                     JOIN {local_chuspasocial_post} rcpost ON rcpost.id = rcc.postid
                                    WHERE rc.itemtype = 'comment' AND rcpost.courseid = 0
                                          AND rc.reporterid = :commentreporter)
                        OR EXISTS (SELECT 1
                                     FROM {local_chuspasocial_report} rl
                                    WHERE rl.itemtype = 'listing' AND rl.reporterid = :listingreporter)
                        OR EXISTS (SELECT 1
                                     FROM {local_chuspasocial_follow} f
                                    WHERE f.userid = :followuser)
                        OR EXISTS (SELECT 1
                                     FROM {local_chuspasocial_follow} ff
                                    WHERE ff.itemtype = 'user' AND ff.itemid = :followeduser)
                        OR EXISTS (SELECT 1
                                     FROM {local_chuspasocial_listing} l
                                    WHERE l.userid = :listinguser))";
        $contextlist->add_from_sql($sql, [
            'contextsystem' => CONTEXT_SYSTEM,
            'postuser' => $userid,
            'commentuser' => $userid,
            'reactionuser' => $userid,
            'postreporter' => $userid,
            'commentreporter' => $userid,
            'listingreporter' => $userid,
            'followuser' => $userid,
            'followeduser' => $userid,
            'listinguser' => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Exports the data of a user in the approved contexts.
     *
     * In each context it exports the posts of the user with their tags and files, the comments,
     * the reactions and the reports made by the user. In the system context it also exports the
     * follows of the user and the book listings of the user with their photos.
     *
     * @param approved_contextlist $contextlist The approved contexts and the user.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        if ($contextlist->count() == 0) {
            return;
        }

        $userid = (int) $contextlist->get_user()->id;
        $base = [get_string('pluginname', 'local_chuspasocial')];

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_COURSE) {
                $courseid = (int) $context->instanceid;
            } else if ($context->contextlevel == CONTEXT_SYSTEM) {
                $courseid = 0;
            } else {
                continue;
            }

            $params = ['userid' => $userid, 'courseid' => $courseid];
            $writer = writer::with_context($context);

            // Export the posts of the user with their tags and files.
            $posts = $DB->get_records(
                'local_chuspasocial_post',
                ['userid' => $userid, 'courseid' => $courseid],
                'timecreated ASC'
            );
            foreach ($posts as $post) {
                $subcontext = array_merge($base, ['posts', (string) $post->id]);
                $writer->export_data($subcontext, (object) [
                    'type' => $post->type,
                    'content' => format_text($post->content, $post->contentformat, ['context' => $context]),
                    'hidden' => transform::yesno($post->hidden),
                    'timecreated' => transform::datetime($post->timecreated),
                    'timemodified' => transform::datetime($post->timemodified),
                ]);
                $writer->export_area_files($subcontext, 'local_chuspasocial', 'postimage', $post->id);
                $writer->export_area_files($subcontext, 'local_chuspasocial', 'post', $post->id);
                \core_tag\privacy\provider::export_item_tags(
                    $userid,
                    $context,
                    $subcontext,
                    'local_chuspasocial',
                    'local_chuspasocial_post',
                    $post->id
                );
            }

            // Export the comments of the user.
            $comments = $DB->get_records_sql(
                'SELECT c.*
                   FROM {local_chuspasocial_comment} c
                   JOIN {local_chuspasocial_post} p ON p.id = c.postid
                  WHERE p.courseid = :courseid AND c.userid = :userid
               ORDER BY c.timecreated ASC',
                $params
            );
            foreach ($comments as $comment) {
                $writer->export_data(array_merge($base, ['comments', (string) $comment->id]), (object) [
                    'postid' => $comment->postid,
                    'content' => format_text($comment->content, $comment->contentformat, ['context' => $context]),
                    'timecreated' => transform::datetime($comment->timecreated),
                    'timemodified' => transform::datetime($comment->timemodified),
                ]);
            }

            // Export the reactions of the user.
            $reactions = $DB->get_records_sql(
                'SELECT r.*
                   FROM {local_chuspasocial_reaction} r
                   JOIN {local_chuspasocial_post} p ON p.id = r.postid
                  WHERE p.courseid = :courseid AND r.userid = :userid
               ORDER BY r.timecreated ASC',
                $params
            );
            foreach ($reactions as $reaction) {
                $writer->export_data(array_merge($base, ['reactions', (string) $reaction->id]), (object) [
                    'postid' => $reaction->postid,
                    'type' => $reaction->type,
                    'timecreated' => transform::datetime($reaction->timecreated),
                ]);
            }

            // Export the reports made by the user about content of this context.
            $reports = $DB->get_records_sql(
                "SELECT rp.*
                   FROM {local_chuspasocial_report} rp
                   JOIN {local_chuspasocial_post} p ON p.id = rp.itemid
                  WHERE rp.itemtype = 'post' AND p.courseid = :courseid AND rp.reporterid = :userid",
                $params
            );
            $commentreports = $DB->get_records_sql(
                "SELECT rc.*
                   FROM {local_chuspasocial_report} rc
                   JOIN {local_chuspasocial_comment} c ON c.id = rc.itemid
                   JOIN {local_chuspasocial_post} p ON p.id = c.postid
                  WHERE rc.itemtype = 'comment' AND p.courseid = :courseid AND rc.reporterid = :userid",
                $params
            );
            $reports = $reports + $commentreports;

            if ($courseid == 0) {
                $listingreports = $DB->get_records(
                    'local_chuspasocial_report',
                    ['reporterid' => $userid, 'itemtype' => 'listing']
                );
                $reports = $reports + $listingreports;
            }

            foreach ($reports as $report) {
                $writer->export_data(array_merge($base, ['reports', (string) $report->id]), (object) [
                    'itemtype' => $report->itemtype,
                    'itemid' => $report->itemid,
                    'reason' => $report->reason,
                    'status' => $report->status,
                    'timecreated' => transform::datetime($report->timecreated),
                    'timeresolved' => empty($report->timeresolved) ? null : transform::datetime($report->timeresolved),
                ]);
            }

            if ($courseid == 0) {
                // Export the follows of the user.
                $follows = $DB->get_records('local_chuspasocial_follow', ['userid' => $userid], 'timecreated ASC');
                foreach ($follows as $follow) {
                    $writer->export_data(array_merge($base, ['follows', (string) $follow->id]), (object) [
                        'itemtype' => $follow->itemtype,
                        'itemid' => $follow->itemid,
                        'timecreated' => transform::datetime($follow->timecreated),
                    ]);
                }

                // Export the book listings of the user with their photos.
                $listings = $DB->get_records('local_chuspasocial_listing', ['userid' => $userid], 'timecreated ASC');
                foreach ($listings as $listing) {
                    $subcontext = array_merge($base, ['listings', (string) $listing->id]);
                    $writer->export_data($subcontext, (object) [
                        'title' => $listing->title,
                        'author' => $listing->author,
                        'description' => format_text(
                            (string) $listing->description,
                            $listing->descriptionformat,
                            ['context' => $context]
                        ),
                        'price' => $listing->price,
                        'externalcontact' => $listing->externalcontact,
                        'status' => $listing->status,
                        'timecreated' => transform::datetime($listing->timecreated),
                        'timemodified' => transform::datetime($listing->timemodified),
                    ]);
                    $writer->export_area_files($subcontext, 'local_chuspasocial', 'photos', $listing->id);
                }
            }
        }
    }

    /**
     * Deletes all the data of the plugin that is stored in a context.
     *
     * In a course context it deletes the posts of the course with their comments, reactions,
     * tags, reports and files, and the links between the book listings and the course.
     * In the system context it deletes the posts of the general wall in the same way,
     * and also the follows, the book listings and the reports.
     *
     * @param \context $context The context to delete the data from.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if ($context->contextlevel == CONTEXT_COURSE) {
            $courseid = (int) $context->instanceid;
        } else if ($context->contextlevel == CONTEXT_SYSTEM) {
            $courseid = 0;
        } else {
            return;
        }

        $params = ['courseid' => $courseid];
        $postselect = 'SELECT id FROM {local_chuspasocial_post} WHERE courseid = :courseid';
        $commentselect = "SELECT id FROM {local_chuspasocial_comment} WHERE postid IN ($postselect)";

        $transaction = $DB->start_delegated_transaction();

        try {
            // Delete the tags of the posts.
            $postids = $DB->get_fieldset_select('local_chuspasocial_post', 'id', 'courseid = :courseid', $params);
            foreach ($postids as $postid) {
                core_tag_tag::remove_all_item_tags('local_chuspasocial', 'local_chuspasocial_post', $postid);
            }

            // Delete the reports about the posts and their comments.
            $DB->delete_records_select(
                'local_chuspasocial_report',
                "itemtype = 'comment' AND itemid IN ($commentselect)",
                $params
            );
            $DB->delete_records_select(
                'local_chuspasocial_report',
                "itemtype = 'post' AND itemid IN ($postselect)",
                $params
            );

            // Delete the reactions, the comments and the posts.
            $DB->delete_records_select('local_chuspasocial_reaction', "postid IN ($postselect)", $params);
            $DB->delete_records_select('local_chuspasocial_comment', "postid IN ($postselect)", $params);
            $DB->delete_records('local_chuspasocial_post', $params);

            // Delete the files of the posts.
            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'local_chuspasocial', 'postimage');
            $fs->delete_area_files($context->id, 'local_chuspasocial', 'post');

            if ($courseid > 0) {
                // Delete the links between the book listings and the course.
                $DB->delete_records('local_chuspasocial_lstcourse', $params);
            } else {
                // Delete the data that belongs to the system context.
                $DB->delete_records('local_chuspasocial_follow');
                $DB->delete_records('local_chuspasocial_lstcourse');
                $DB->delete_records('local_chuspasocial_listing');
                $DB->delete_records('local_chuspasocial_report');
            }

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
        }
    }

    /**
     * Deletes the data of one user in the approved contexts.
     *
     * In a course context it deletes the posts of the user in the course with their comments,
     * reactions, tags, reports and files, the comments and reactions of the user on other posts
     * of the course, and the reports made by the user about content of the course.
     * In the system context it does the same for the general wall, and also deletes the follows
     * of the user, the follows other users have of the user, the book listings of the user with
     * their photos and course links, and the reports made by the user about listings.
     *
     * @param approved_contextlist $contextlist The approved contexts and the user.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        if ($contextlist->count() == 0) {
            return;
        }

        $userid = (int) $contextlist->get_user()->id;
        $fs = get_file_storage();

        $transaction = $DB->start_delegated_transaction();

        try {
            foreach ($contextlist->get_contexts() as $context) {
                if ($context->contextlevel == CONTEXT_COURSE) {
                    $courseid = (int) $context->instanceid;
                } else if ($context->contextlevel == CONTEXT_SYSTEM) {
                    $courseid = 0;
                } else {
                    continue;
                }

                $params = ['userid' => $userid, 'courseid' => $courseid];
                $courseposts = 'SELECT id FROM {local_chuspasocial_post} WHERE courseid = :courseid';
                $ownposts = $courseposts . ' AND userid = :userid';
                $coursecomments = "SELECT id FROM {local_chuspasocial_comment} WHERE postid IN ($courseposts)";
                $owncomments = $coursecomments . ' AND userid = :userid';
                $postcomments = "SELECT id FROM {local_chuspasocial_comment} WHERE postid IN ($ownposts)";

                // Delete the tags and the files of the posts of the user.
                $postids = $DB->get_fieldset_select(
                    'local_chuspasocial_post',
                    'id',
                    'userid = :userid AND courseid = :courseid',
                    $params
                );
                foreach ($postids as $postid) {
                    core_tag_tag::remove_all_item_tags('local_chuspasocial', 'local_chuspasocial_post', $postid);
                    $fs->delete_area_files($context->id, 'local_chuspasocial', 'postimage', $postid);
                    $fs->delete_area_files($context->id, 'local_chuspasocial', 'post', $postid);
                }

                // Delete the reports about the posts of the user and their comments.
                $DB->delete_records_select(
                    'local_chuspasocial_report',
                    "itemtype = 'comment' AND itemid IN ($postcomments)",
                    $params
                );
                $DB->delete_records_select(
                    'local_chuspasocial_report',
                    "itemtype = 'post' AND itemid IN ($ownposts)",
                    $params
                );

                // Delete the reports about the comments of the user on other posts.
                $DB->delete_records_select(
                    'local_chuspasocial_report',
                    "itemtype = 'comment' AND itemid IN ($owncomments)",
                    $params
                );

                // Delete the reports made by the user about content of this context.
                $DB->delete_records_select(
                    'local_chuspasocial_report',
                    "reporterid = :userid AND itemtype = 'comment' AND itemid IN ($coursecomments)",
                    $params
                );
                $DB->delete_records_select(
                    'local_chuspasocial_report',
                    "reporterid = :userid AND itemtype = 'post' AND itemid IN ($courseposts)",
                    $params
                );

                // Delete the reactions and the comments on the posts of the user.
                $DB->delete_records_select('local_chuspasocial_reaction', "postid IN ($ownposts)", $params);
                $DB->delete_records_select('local_chuspasocial_comment', "postid IN ($ownposts)", $params);

                // Delete the reactions and the comments of the user on other posts.
                $DB->delete_records_select(
                    'local_chuspasocial_reaction',
                    "userid = :userid AND postid IN ($courseposts)",
                    $params
                );
                $DB->delete_records_select(
                    'local_chuspasocial_comment',
                    "userid = :userid AND postid IN ($courseposts)",
                    $params
                );

                // Delete the posts of the user.
                $DB->delete_records('local_chuspasocial_post', $params);

                if ($courseid == 0) {
                    // Delete the follows of the user and the follows other users have of the user.
                    $DB->delete_records('local_chuspasocial_follow', ['userid' => $userid]);
                    $DB->delete_records('local_chuspasocial_follow', ['itemtype' => 'user', 'itemid' => $userid]);

                    // Delete the book listings of the user with their photos and course links.
                    $listingids = $DB->get_fieldset_select('local_chuspasocial_listing', 'id', 'userid = :userid', $params);
                    foreach ($listingids as $listingid) {
                        $fs->delete_area_files($context->id, 'local_chuspasocial', 'photos', $listingid);
                    }
                    $ownlistings = 'SELECT id FROM {local_chuspasocial_listing} WHERE userid = :userid';
                    $DB->delete_records_select(
                        'local_chuspasocial_report',
                        "itemtype = 'listing' AND itemid IN ($ownlistings)",
                        $params
                    );
                    $DB->delete_records_select('local_chuspasocial_lstcourse', "listingid IN ($ownlistings)", $params);
                    $DB->delete_records('local_chuspasocial_listing', ['userid' => $userid]);

                    // Delete the reports made by the user about listings.
                    $DB->delete_records('local_chuspasocial_report', ['reporterid' => $userid, 'itemtype' => 'listing']);
                }
            }

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
        }
    }

    /**
     * Gets the users that have data stored in a context.
     *
     * In a course context it adds the authors of the posts, comments and reactions of the course,
     * and the users that reported posts or comments of the course.
     * In the system context it does the same for the general wall, and also adds the users that
     * follow or are followed, the owners of book listings and the users that reported listings.
     *
     * @param userlist $userlist The list to add the users to.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if ($context->contextlevel == CONTEXT_COURSE) {
            $courseid = (int) $context->instanceid;
        } else if ($context->contextlevel == CONTEXT_SYSTEM) {
            $courseid = 0;
        } else {
            return;
        }

        $params = ['courseid' => $courseid];
        $courseposts = 'SELECT id FROM {local_chuspasocial_post} WHERE courseid = :courseid';
        $coursecomments = "SELECT id FROM {local_chuspasocial_comment} WHERE postid IN ($courseposts)";

        // Authors of the posts, the comments and the reactions.
        $userlist->add_from_sql(
            'userid',
            'SELECT userid FROM {local_chuspasocial_post} WHERE courseid = :courseid',
            $params
        );
        $userlist->add_from_sql(
            'userid',
            "SELECT userid FROM {local_chuspasocial_comment} WHERE postid IN ($courseposts)",
            $params
        );
        $userlist->add_from_sql(
            'userid',
            "SELECT userid FROM {local_chuspasocial_reaction} WHERE postid IN ($courseposts)",
            $params
        );

        // Users that reported posts or comments.
        $userlist->add_from_sql(
            'userid',
            "SELECT reporterid AS userid FROM {local_chuspasocial_report}
              WHERE itemtype = 'post' AND itemid IN ($courseposts)",
            $params
        );
        $userlist->add_from_sql(
            'userid',
            "SELECT reporterid AS userid FROM {local_chuspasocial_report}
              WHERE itemtype = 'comment' AND itemid IN ($coursecomments)",
            $params
        );

        if ($courseid == 0) {
            // Users that follow and users that are followed.
            $userlist->add_from_sql('userid', 'SELECT userid FROM {local_chuspasocial_follow}', []);
            $userlist->add_from_sql(
                'userid',
                "SELECT itemid AS userid FROM {local_chuspasocial_follow} WHERE itemtype = 'user'",
                []
            );

            // Owners of book listings and users that reported listings.
            $userlist->add_from_sql('userid', 'SELECT userid FROM {local_chuspasocial_listing}', []);
            $userlist->add_from_sql(
                'userid',
                "SELECT reporterid AS userid FROM {local_chuspasocial_report} WHERE itemtype = 'listing'",
                []
            );
        }
    }

    /**
     * Deletes the data of several users in a context.
     *
     * It deletes the same data as delete_data_for_user(), but for all the users of the approved list.
     *
     * @param approved_userlist $userlist The approved context and the users.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        $context = $userlist->get_context();
        if ($context->contextlevel == CONTEXT_COURSE) {
            $courseid = (int) $context->instanceid;
        } else if ($context->contextlevel == CONTEXT_SYSTEM) {
            $courseid = 0;
        } else {
            return;
        }

        [$usersql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'usr');
        $params = ['courseid' => $courseid] + $userparams;
        $fs = get_file_storage();

        $courseposts = 'SELECT id FROM {local_chuspasocial_post} WHERE courseid = :courseid';
        $ownposts = $courseposts . " AND userid $usersql";
        $coursecomments = "SELECT id FROM {local_chuspasocial_comment} WHERE postid IN ($courseposts)";
        $owncomments = $coursecomments . " AND userid $usersql";
        $postcomments = "SELECT id FROM {local_chuspasocial_comment} WHERE postid IN ($ownposts)";

        $transaction = $DB->start_delegated_transaction();

        try {
            // Delete the tags and the files of the posts of the users.
            $postids = $DB->get_fieldset_select(
                'local_chuspasocial_post',
                'id',
                "courseid = :courseid AND userid $usersql",
                $params
            );
            foreach ($postids as $postid) {
                core_tag_tag::remove_all_item_tags('local_chuspasocial', 'local_chuspasocial_post', $postid);
                $fs->delete_area_files($context->id, 'local_chuspasocial', 'postimage', $postid);
                $fs->delete_area_files($context->id, 'local_chuspasocial', 'post', $postid);
            }

            // Delete the reports about the posts of the users and their comments.
            $DB->delete_records_select(
                'local_chuspasocial_report',
                "itemtype = 'comment' AND itemid IN ($postcomments)",
                $params
            );
            $DB->delete_records_select(
                'local_chuspasocial_report',
                "itemtype = 'post' AND itemid IN ($ownposts)",
                $params
            );

            // Delete the reports about the comments of the users on other posts.
            $DB->delete_records_select(
                'local_chuspasocial_report',
                "itemtype = 'comment' AND itemid IN ($owncomments)",
                $params
            );

            // Delete the reports made by the users about content of this context.
            $DB->delete_records_select(
                'local_chuspasocial_report',
                "reporterid $usersql AND itemtype = 'comment' AND itemid IN ($coursecomments)",
                $params
            );
            $DB->delete_records_select(
                'local_chuspasocial_report',
                "reporterid $usersql AND itemtype = 'post' AND itemid IN ($courseposts)",
                $params
            );

            // Delete the reactions and the comments on the posts of the users.
            $DB->delete_records_select('local_chuspasocial_reaction', "postid IN ($ownposts)", $params);
            $DB->delete_records_select('local_chuspasocial_comment', "postid IN ($ownposts)", $params);

            // Delete the reactions and the comments of the users on other posts.
            $DB->delete_records_select(
                'local_chuspasocial_reaction',
                "userid $usersql AND postid IN ($courseposts)",
                $params
            );
            $DB->delete_records_select(
                'local_chuspasocial_comment',
                "userid $usersql AND postid IN ($courseposts)",
                $params
            );

            // Delete the posts of the users.
            $DB->delete_records_select('local_chuspasocial_post', "courseid = :courseid AND userid $usersql", $params);

            if ($courseid == 0) {
                // Delete the follows of the users and the follows other users have of them.
                $DB->delete_records_select('local_chuspasocial_follow', "userid $usersql", $userparams);
                $DB->delete_records_select(
                    'local_chuspasocial_follow',
                    "itemtype = 'user' AND itemid $usersql",
                    $userparams
                );

                // Delete the book listings of the users with their photos and course links.
                $listingids = $DB->get_fieldset_select('local_chuspasocial_listing', 'id', "userid $usersql", $userparams);
                foreach ($listingids as $listingid) {
                    $fs->delete_area_files($context->id, 'local_chuspasocial', 'photos', $listingid);
                }
                $ownlistings = "SELECT id FROM {local_chuspasocial_listing} WHERE userid $usersql";
                $DB->delete_records_select(
                    'local_chuspasocial_report',
                    "itemtype = 'listing' AND itemid IN ($ownlistings)",
                    $userparams
                );
                $DB->delete_records_select('local_chuspasocial_lstcourse', "listingid IN ($ownlistings)", $userparams);
                $DB->delete_records_select('local_chuspasocial_listing', "userid $usersql", $userparams);

                // Delete the reports made by the users about listings.
                $DB->delete_records_select(
                    'local_chuspasocial_report',
                    "reporterid $usersql AND itemtype = 'listing'",
                    $userparams
                );
            }

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
        }
    }
}
