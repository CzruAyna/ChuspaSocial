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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Page to view a marketplace listing, its external contact, and mark it as sold.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_chuspasocial\local\listing_service;

$id = required_param('id', PARAM_INT);

require_login();

$context = context_system::instance();
require_capability('local/chuspasocial:viewmarketplace', $context);

$marketplaceenabled = get_config('local_chuspasocial', 'enablemarketplace');
if ($marketplaceenabled !== false && empty($marketplaceenabled)) {
    throw new moodle_exception('marketplacedisabled', 'local_chuspasocial');
}

$pageurl = new moodle_url('/local/chuspasocial/listing.php', ['id' => $id]);
$marketplaceurl = new moodle_url('/local/chuspasocial/marketplace.php');

$listing = $DB->get_record(
    'local_chuspasocial_listing',
    ['id' => $id],
    '*',
    MUST_EXIST
);

$seller = core_user::get_user($listing->userid, '*', MUST_EXIST);
$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(format_string($listing->title));
$PAGE->set_heading(get_string('pluginname', 'local_chuspasocial'));
$PAGE->navbar->add(get_string('books', 'local_chuspasocial'), $marketplaceurl);
$PAGE->navbar->add(format_string($listing->title));

if (data_submitted() && optional_param('markassold', 0, PARAM_BOOL)) {
    require_sesskey();

    if ((int) $listing->userid !== (int) $USER->id) {
        throw new moodle_exception(
            'nopermissions',
            'error',
            $pageurl,
            get_string('markassold', 'local_chuspasocial')
        );
    }

    if ($listing->status !== 'sold') {
        listing_service::mark_sold($id);
        redirect(
            $pageurl,
            get_string('changessaved'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($listing->title));

echo html_writer::tag(
    'p',
    get_string('authorname', 'moodle') . ': ' . s($listing->author)
);

echo html_writer::tag(
    'p',
    get_string('price', 'local_chuspasocial') . ': ' .
        format_float((float) $listing->price, 2)
);

echo $OUTPUT->box(
    format_text(
        $listing->description ?? '',
        $listing->descriptionformat,
        ['context' => $context]
    ),
    'generalbox'
);

if (!empty($listing->externalcontact)) {
    echo html_writer::tag(
        'p',
        s($listing->externalcontact),
        ['class' => 'external-contact']
    );
    echo $OUTPUT->notification(
        'Este contacto es externo a la institución',
        \core\output\notification::NOTIFY_WARNING
    );
}

// Show the contact button only to users other than the seller.
if ((int) $listing->userid !== (int) $USER->id) {
    $contacturl = new moodle_url('/message/index.php', ['id' => $seller->id]);
    echo html_writer::link(
        $contacturl,
        get_string('contactseller', 'local_chuspasocial'),
        ['class' => 'btn btn-primary']
    );
}

if ($listing->status !== 'sold' && (int) $listing->userid === (int) $USER->id) {
    echo html_writer::start_tag('form', [
        'method' => 'post',
        'action' => $pageurl,
    ]);
    echo html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => 'sesskey',
        'value' => sesskey(),
    ]);
    echo html_writer::empty_tag('input', [
        'type' => 'submit',
        'name' => 'markassold',
        'value' => get_string('markassold', 'local_chuspasocial'),
        'class' => 'btn btn-primary',
    ]);
    echo html_writer::end_tag('form');
}

echo $OUTPUT->footer();
