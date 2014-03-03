<?php

/**
 * The main aspire configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage aspire
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/aspire/locallib.php');

/**
 * Module instance settings form
 */
class mod_aspire_mod_form extends moodleform_mod {
    
    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;
        //$mform->addElement('header', 'aspirefieldset', get_string('aspirefieldset', 'aspire'));
        aspire_setup_elements($mform);
        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
}

function aspire_setup_elements(&$mform){
    global $update, $CFG, $COURSE;
    $course_code = strtolower($COURSE->idnumber);
    $mform->addElement('hidden', 'summary', 'reading lists for module ', 'id="summary"');
    // save course_id so that aspire url is not interrupted - eg if course id mistakenly changed in moodle
    $mform->addElement('hidden', 'module_id', $course_code);
    // fallback for manual courses with no code
    if ($course_code == "" OR $course_code == null) {
        return false; 
    }
    else {
        if ($list_url = aspire_listurl($course_code)){
            $toc = aspire_load_listhtml($course_code, $list_url);
            $sections = aspire_get_listsections($toc);
            $mform->addElement('select', 'rl_section', 'Reading List:', $sections, 'id="aspire_section"');
            $mform->addRule('rl_section', null, 'required', null, 'client');
        }
        else {
            $mform->addElement('html', '<div class="warning"><h3>No reading list was found.<h3></div>

                This means that the \'course code\' designated in the course settings does not match up to a module in Talis Aspire');
        }
    }
}

