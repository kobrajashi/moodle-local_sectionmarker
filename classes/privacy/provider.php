<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Privacy provider for local_sectionmarker.
 *
 * The plugin stores which user last modified each section marker (usermodified).
 * This constitutes personal data under GDPR.
 *
 * @package    local_sectionmarker
 * @copyright  2025 Tobias Sanders
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sectionmarker\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider implementation.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    // -------------------------------------------------------------------------
    // Metadata.
    // -------------------------------------------------------------------------

    /**
     * Describe what data this plugin stores.
     *
     * @param collection $collection The metadata collection to add to.
     * @return collection Updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_sectionmarker',
            [
                'courseid'     => 'privacy:metadata:local_sectionmarker:courseid',
                'cmid'         => 'privacy:metadata:local_sectionmarker:cmid',
                'marker'       => 'privacy:metadata:local_sectionmarker:marker',
                'timemodified' => 'privacy:metadata:local_sectionmarker:timemodified',
                'usermodified' => 'privacy:metadata:local_sectionmarker:usermodified',
            ],
            'privacy:metadata:local_sectionmarker'
        );

        return $collection;
    }

    // -------------------------------------------------------------------------
    // Contextlist (which contexts contain data for a given user).
    // -------------------------------------------------------------------------

    /**
     * Get contexts containing data for the given user.
     *
     * @param int $userid The user ID.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {local_sectionmarker} sm ON sm.courseid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextlevel
                   AND sm.usermodified  = :userid";

        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_COURSE,
            'userid'       => $userid,
        ]);

        return $contextlist;
    }

    // -------------------------------------------------------------------------
    // Userlist (which users have data in a given context).
    // -------------------------------------------------------------------------

    /**
     * Get users who have data within a given context.
     *
     * @param userlist $userlist The userlist to populate.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }

        $sql = "SELECT sm.usermodified AS userid
                  FROM {local_sectionmarker} sm
                 WHERE sm.courseid = :courseid";

        $userlist->add_from_sql('userid', $sql, ['courseid' => $context->instanceid]);
    }

    // -------------------------------------------------------------------------
    // Export user data.
    // -------------------------------------------------------------------------

    /**
     * Export personal data for a given user in a given set of contexts.
     *
     * @param approved_contextlist $contextlist Approved list of contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_COURSE) {
                continue;
            }

            $records = $DB->get_records_select(
                'local_sectionmarker',
                'courseid = :courseid AND usermodified = :userid',
                ['courseid' => $context->instanceid, 'userid' => $userid]
            );

            if (empty($records)) {
                continue;
            }

            $data = array_values(array_map(static function ($rec) {
                return [
                    'cmid'         => $rec->cmid,
                    'marker'       => $rec->marker,
                    'timemodified' => \core_privacy\local\request\transform::datetime($rec->timemodified),
                ];
            }, $records));

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'local_sectionmarker')],
                (object) ['markers' => $data]
            );
        }
    }

    // -------------------------------------------------------------------------
    // Delete user data.
    // -------------------------------------------------------------------------

    /**
     * Delete data for all users in a given context.
     *
     * Section markers are course-level data; deleting for a context deletes
     * all records for that course.
     *
     * @param \context $context The context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }

        $DB->delete_records('local_sectionmarker', ['courseid' => $context->instanceid]);
    }

    /**
     * Delete data for a specific user.
     *
     * Because section markers are course-level (not per-student), we only
     * anonymise the usermodified field rather than deleting the record.
     *
     * @param approved_contextlist $contextlist Approved list of contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_COURSE) {
                continue;
            }

            // Anonymise: reset usermodified to 0 (guest/deleted) rather than
            // removing the section marker, which is course-level configuration.
            $DB->set_field_select(
                'local_sectionmarker',
                'usermodified',
                0,
                'courseid = :courseid AND usermodified = :userid',
                ['courseid' => $context->instanceid, 'userid' => $userid]
            );
        }
    }

    /**
     * Delete data for a list of users within a single context.
     *
     * @param approved_userlist $userlist The approved userlist.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }

        [$insql, $params] = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params['courseid'] = $context->instanceid;

        $DB->set_field_select(
            'local_sectionmarker',
            'usermodified',
            0,
            "courseid = :courseid AND usermodified $insql",
            $params
        );
    }
}
