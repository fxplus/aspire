<?php

/**
 * Internal library of functions for module aspire
 *
 * All the aspire specific functions, needed to implement the module
 * logic, should go here. 
 * 
 * Never include this file from your lib.php
 *
 * @package    mod
 * @subpackage aspire
 * @copyright  2014 tombola
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Implements moodle hook listurl
 *
 * @param array $things
 * @return object
 */

function aspire_listurl($course_code) {

    global $CFG;

    $baseurl = get_user_preferences('aspire_talisurl', $CFG->aspire_talisurl);
    // $hierarchy = aspire_get_hierarchy();
    // debugging(krumo($hierarchy));

    $url = $baseurl."/modules/".urlencode($course_code)."/lists.json";

    debugging('Aspire - json list url: '.$url);
    libxml_use_internal_errors(true); // http://goo.gl/AJhz2
    if ($json = @file_get_contents($url)){
        $json = json_decode($json);

        foreach ($json as $listurl => $data) {
        # we only want lists. not courses or departments
            if (preg_match("/\/lists\//", $listurl)) {
                $sitetype = 'modules';
                //readinglist_url = "http://resourcelists.falmouth.ac.uk/$sitetype/$course_code.html";
                $readinglist_url = $listurl .'.html';
                debugging('Aspire - matched reading list urls from json: '.$readinglist_url);
            }
        }
        return $readinglist_url;
    } else {
        return NULL;
    }
}

function aspire_load_listhtml($course_code, $list_url = NULL) {
    if(!$list_url) {$list_url = aspire_listurl($course_code);}
    $doc = new DOMDocument;
    if (@$doc->loadHTMLFile($list_url)){
        // return DomDocument object of the html page representing a reading list
        return ($doc->loadHTMLFile($list_url))?  $doc :  NULL;
    } else {
        return NULL;
    }
}

function aspire_get_listsections($doc) {
    if (is_object($doc)) {
        $toc = $doc->getElementById("toc");
        $links = $toc->getElementsByTagName("a");
        $list = "<ul id='reading_items'>";

        foreach ($links as $link) {
            // get anchor # for reading list section
            $href =  $link->getAttribute("href");
            // get name of reading list section 
            $name = trim($link->nodeValue);
            // html name attribute
            $listId = str_replace('#','',$href);
            $info = $listId.'|'.$name;
            $select_list[$info] = $name;
        }
        return $select_list;
    } else {
        return NULL;
    }
}

function aspire_get_sectionhtml($course_code, $section_id, $doc = NULL) {
    if (!$doc) {$doc = aspire_load_listhtml($course_code);}
    $list_obj = $doc->getElementById($section_id);
    // parse reading list html - performance problem?
    $readinglist = aspire_cleanup_section($list_obj); 
    return $readinglist;
}

/* 
 * The following two functions resolve layout issues due to the nested divs used in aspire html
 * quite possibly overkill, and certainly not performative
 */
function aspire_cleanup_section($list_obj) {
    // cleanup the html by picking concise fragments out of all the containers
    $readinglist = new object();
    $readinglist->explanation = aspire_get_html_by_class($list_obj,'sectionNote');
    $readinglist->html = aspire_get_html_by_class($list_obj,'span9');
    return $readinglist;
}

// pick out simple html from aspire markup
function aspire_get_html_by_class($domelement, $classname = "span9") {
    $doc = new DomDocument;
    $doc->appendChild($doc->importNode($domelement, true));
    $finder = new DomXPath($doc);
    $nodes = $finder->query("//*[contains(@class, '$classname')]");
    $output = new DomDocument;
    foreach ($nodes as $node) {
        $output->appendChild($output->importNode($node, true));
    }
    return $output->saveHTML();
}

function aspire_theme_readinglist($aspire) {
    $html = array();
    $html[] = '<h3 classs="readinglist-title">Reading List: '.$aspire->name.'</h3>';
    $html[] = '<div classs="readinglist-intro">'.$aspire->explanation.'</div>';
    $html[] = '<div classs="readinglist-items">'.$aspire->html.'</div>'; // could be stored as serialised array instead?
    return implode("\n", $html);
}

function aspire_get_hierarchy() {
    global $CFG;
    $hierarchy = get_user_preferences('aspire_listentities', $CFG->aspire_listentities);
    $hierarchy = str_replace(' ', '', $hierarchy);
    return explode(',', $hierarchy);
}