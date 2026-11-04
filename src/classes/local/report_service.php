<?php

// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Service to create content reports and notify the moderators.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local;

use core\message\message;
use core_user;
use invalid_parameter_exception;
use moodle_exception;

/**
 * Service to create content reports and notify the moderators.
 *
 * @package    local_chuspasocial
 */
class report_service {
    /** @var string[] Types of content that can be reported. */
    private const ITEMTYPES = ['post', 'comment', 'listing'];

    /**
     * Create a report and notify the moderators.
     *
     * @param \stdClass $data Object with userid (reporter), itemtype, itemid and reason.
     * @return \stdClass The created report, with the id of the reporter in userid.
     * @throws invalid_parameter_exception When the item type is not valid.
     * @throws moodle_exception When the user already reported the same content.
     */
    public static function create_report(
        string $itemtype,
        int $itemid,
        string $reason
    ): int {
        global $DB, $USER;

        if (!in_array($itemtype, self::ITEMTYPES, true)) {
            throw new invalid_parameter_exception(
                'Invalid report item type: ' . $itemtype
            );
        }

        $reason = trim($reason);

        if ($itemid <= 0 || $reason === '') {
            throw new invalid_parameter_exception(
                'Invalid report item ID or reason.'
            );
        }

        $conditions = [
            'reporterid' => $USER->id,
            'itemtype' => $itemtype,
            'itemid' => $itemid,
        ];

        if ($DB->record_exists('local_chuspasocial_report', $conditions)) {
            throw new moodle_exception('reportsent', 'local_chuspasocial');
        }

        $record = (object) [
            'itemtype' => $itemtype,
            'itemid' => $itemid,
            'reporterid' => $USER->id,
            'reason' => $reason,
            'status' => 'open',
            'timecreated' => time(),
        ];

        $record->id = $DB->insert_record(
            'local_chuspasocial_report',
            $record
        );

        self::notify_moderators($record);

        return (int) $record->id;
    }

    /**
     * Send the newreport notification to every user who can moderate, except the reporter.
     *
     * @param \stdClass $report The report record just created.
     */
    private static function notify_moderators(\stdClass $report): void {
        // The capability is not defined yet in db/access.php: nobody to notify until it is.
        if (!get_capability_info('local/chuspasocial:moderate')) {
            return;
        }

        $reporter = core_user::get_user($report->reporterid);
        $moderators = get_users_by_capability(\context_system::instance(), 'local/chuspasocial:moderate', 'u.*');
        if (!$reporter || !$moderators) {
            return;
        }

        $item = $report->itemtype . ' #' . $report->itemid;
        foreach ($moderators as $moderator) {
            if ((int) $moderator->id === (int) $report->reporterid) {
                continue;
            }

            $message = new message();
            $message->component = 'local_chuspasocial';
            $message->name = 'newreport';
            $message->userfrom = $reporter;
            $message->userto = $moderator;
            $message->subject = get_string('messageprovider:newreport_subject', 'local_chuspasocial', $item);
            $message->fullmessage = get_string('reportreason', 'local_chuspasocial') . ': ' . $report->reason;
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml = '';
            $message->smallmessage = $message->subject;
            $message->notification = 1;

            message_send($message);
        }
    }

    /**
     * Mark a report as resolved and hide the reported content.
     *
     * @param int $reportid Id of the report to resolve.
     * @throws \required_capability_exception When the user cannot moderate.
     */
    public static function resolve_and_hide(int $reportid): void {
        global $DB, $USER;

        require_capability('local/chuspasocial:moderate', \context_system::instance());

        $report = $DB->get_record('local_chuspasocial_report', ['id' => $reportid], '*', MUST_EXIST);

        $transaction = $DB->start_delegated_transaction();
        try {
            $report->status = 'resolved';
            $report->resolvedby = $USER->id;
            $report->timeresolved = time();
            $DB->update_record('local_chuspasocial_report', $report);

            if ($report->itemtype === 'post') {
                $DB->set_field('local_chuspasocial_post', 'hidden', 1, ['id' => $report->itemid]);
            }
            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
        }
    }
}
