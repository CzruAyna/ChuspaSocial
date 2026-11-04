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
 * Admin settings for local_chuspasocial.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_chuspasocial', get_string('pluginname', 'local_chuspasocial'));
    $ADMIN->add('localplugins', $settings);

    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configtext(
            'local_chuspasocial/currency',
            get_string('currency', 'local_chuspasocial'),
            get_string('currency_desc', 'local_chuspasocial'),
            'BOB',
            PARAM_ALPHA
        ));
    }
}
if ($ADMIN->fulltree) {
    
    $settings->add(new admin_setting_configtext(
        'local_chuspasocial/currency',           
        get_string('currency', 'local_chuspasocial'),      
        get_string('currency_desc', 'local_chuspasocial'), 
        'BOB',                                   
        PARAM_ALPHA                              
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chuspasocial/enablemarketplace',
        new lang_string('enablemarketplace', 'local_chuspasocial'),
        new lang_string('enablemarketplace_desc', 'local_chuspasocial'),
        1
    ));
    $settings->add(new admin_setting_configtext(
        'local_chuspasocial/postsperpage',
        new lang_string('postsperpage', 'local_chuspasocial'),
        new lang_string('postsperpage_desc', 'local_chuspasocial'),
        20,
        PARAM_INT
    ));
    $settings->add(new admin_setting_configtext(
        'local_chuspasocial/maxpostlength',
        new lang_string('maxpostlength', 'local_chuspasocial'),
        new lang_string('maxpostlength_desc', 'local_chuspasocial'),
        2000,
        PARAM_INT
    ));
    $settings->add(new admin_setting_configtext(
        'local_chuspasocial/maxpostimages',
        new lang_string('maxpostimages', 'local_chuspasocial'),
        new lang_string('maxpostimages_desc', 'local_chuspasocial'),
        4,
        PARAM_INT
    ));
}
