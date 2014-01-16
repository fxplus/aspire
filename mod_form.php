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

/**
 * Module instance settings form
 */
class mod_aspire_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('aspirename', 'aspire'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'aspirename', 'aspire');

        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor();

        //-------------------------------------------------------------------------------
        // Adding the rest of aspire settings, spreeading all them into this fieldset
        // or adding more fieldsets ('header' elements) if needed for better logic
        $mform->addElement('static', 'label1', 'aspiresetting1', 'Your aspire fields go here. Replace me!');

        $mform->addElement('header', 'aspirefieldset', get_string('aspirefieldset', 'aspire'));
        $mform->addElement('static', 'label2', 'aspiresetting2', 'Your aspire fields go here. Replace me!');

        require_once ('../krumo/class.krumo.php'); // DEBUGGING
        debugging(krumo($mform)); // DEBUGGING

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
    //global $themedebug;
    //$themedebug['$mform'] = $mform;
    global $update, $CFG, $COURSE;

    //$resource_instance = get_field('course_modules','instance','id',$update); // instance of this resource

    $mform->addElement('hidden', 'summary', '', 'id="summary"');
    $mform->addElement('hidden', 'alltext', '', 'id="alltext"');
    $mform->addElement('hidden', 'reference', '');

    //$mform->addElement('hidden', 'aspire_section', 'id="aspire_section"');

    $options = array(
        'option1' => 'section1',
        'option2' => 'section2',
        'option3' => 'section3',
        );

    $mform->addElement('select', 'type', 'Reading List:', $options, 'id="aspire_section"');

    //$course_code = strtolower(substr($COURSE->idnumber,0,strpos($COURSE->idnumber,':')));
    $course_code = strtolower($COURSE->idnumber);

    //$themedebug['$COURSE'] = $COURSE;
    //$themedebug['$course_code'] = $course_code;

    if ($course_code == "" OR $course_code == null) {
        return false; // fallback for manual courses with no occ code
    }
    else {
        $url = "http://resourcelists.falmouth.ac.uk/modules/".$course_code."/lists.json";
        $json = file_get_contents($url);
        //$themedebug['json'] = $json;
        $json = json_decode($json);
        //$themedebug['json_decode'] = $json;
        foreach ($json as $listurl => $data) {
        # we only want lists. not courses or departments
            if (preg_match("/\/lists\//", $listurl)) {
                //$themedebug['listurl'] = $listurl;
                $sitetype = 'modules';
                $readinglist_url = "http://resourcelists.falmouth.ac.uk/$sitetype/$course_code.html";
                $readinglist_url = $listurl .'.html';
            }
        }
        /* this gets lists associated with courses (awards?), as well as modules
        $url = "http://resourcelists.falmouth.ac.uk/courses/".$course_code."/lists.json";
        $json = file_get_contents($url);
        $json = json_decode($json);
        foreach ($json as $listurl => $data) {
        # we only want lists. not courses or departments
            if (preg_match("/\/lists\//", $listurl)) {
                $sitetype = 'courses';
                $readinglist_url = "http://resourcelists.falmouth.ac.uk/$sitetype/$course_code/lists.html";
                }
        }
        */
        //echo $readinglist_url."<br />";

        libxml_use_internal_errors(true); // http://goo.gl/AJhz2

        $doc = new DOMDocument;
        $doc->loadHTMLFile($readinglist_url);
        $toc = $doc->getElementById("toc");
        $links = $toc->getElementsByTagName("a");

        require_once ('../krumo/class.krumo.php'); // DEBUGGING
        debugging(krumo($readinglist_url)); // DEBUGGING

        $list = "<ul id='reading_items'>";

        foreach ($links as $link) {
            $href =  $link->getAttribute("href"); // get anchor # for reading list section

            $name = $link->nodeValue; // get name of reading list section

            $rl_names[$name] = $href; // DEBUGGING

            $listId = str_replace('#','',$href); // html name attribute
            $list_obj = $doc->createDocumentFragment();
            $list_obj->appendXML("<h3>$name</h3>"); // 
            //$list_obj = $doc->getElementById($listId);
            $f = $doc->createDocumentFragment();
            // $f->appendXML('<a class="add_reading"  data-url="'.$readinglist_url.$href.'" title="add reading list to course/module">+ ADD READING LIST</a>');
            $f->appendXML('<a class="add_reading" data-url="'.$readinglist_url.$href.'" title="add reading list to course/module">+ ADD READING LIST</a>');
            $list_obj->appendChild($f);

            $list .= $doc->saveHTML($list_obj);
        }
        $list .= "</ul>";
    }

    debugging(krumo($rl_names)); // DEBUGGING

    $mform->addElement('html',
    '<div class="fitem"><a id="choose_reading_list" class="action_btn" target="_blank" >Choose a reading list</a></div><div class="fitem" id="lr_preview"></div><div class="resource_select_box">'.$list.'</div>');
}
