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
 * @package mod-aspire
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/aspire/lib.php');

    // setting for base url of aspire installation - for constructing urls
    $settings->add(new admin_setting_configtext('aspire_talisurl', get_string('talisurl', 'aspire'),
                       get_string('configtalisurl', 'aspire'), 'http://resourcelists.falmouth.ac.uk', PARAM_URL));

    /*$settings->add(new admin_setting_configtext('aspire_listentities', get_string('aspire_hierarchy', 'aspire'),
                       get_string('config_hierarchy', 'aspire'), 'module, course'));*/

}

