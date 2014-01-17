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
 * Internal library of functions for module aspire
 *
 * All the aspire specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod
 * @subpackage aspire
 * @copyright  2014 tombola
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Does something really useful with the passed things
 *
 * @param array $things
 * @return object
 */
//function aspire_do_something_useful(array $things) {
//    return new stdClass();
//} 
function aspire_listurl($course_code) {

    $url = "http://resourcelists.falmouth.ac.uk/modules/".urlencode($course_code)."/lists.json";
    if ($json = @file_get_contents($url)){
        $json = json_decode($json);

        foreach ($json as $listurl => $data) {
        # we only want lists. not courses or departments
            if (preg_match("/\/lists\//", $listurl)) {
                $sitetype = 'modules';
                //readinglist_url = "http://resourcelists.falmouth.ac.uk/$sitetype/$course_code.html";
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
            $href =  $link->getAttribute("href"); // get anchor # for reading list section
            $name = trim($link->nodeValue); // get name of reading list section
            $listId = str_replace('#','',$href); // html name attribute
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
    $readinglist = aspire_cleanup_section($list_obj); // parse reading list html - performance problem?
    // $sectionhtml = (is_string($list_obj)) ? $readinglist : $doc->saveHTML($list_obj); // legacy
    return $readinglist;
}

/* 
 * The following two functions resolve layout issues due to the nested divs used in aspire html
 * quite possibly overkill, and certainly not performative
 */
function aspire_cleanup_section($list_obj) {
    // cleanup the html by picking concise fragments out of all the containers
    //$list_obj = aspire_get_html_by_class($list_obj,'sectionNote')."\n".aspire_get_html_by_class($list_obj,'span9');
    $readinglist = new object();
    $readinglist->explanation = aspire_get_html_by_class($list_obj,'sectionNote');
    $readinglist->html = aspire_get_html_by_class($list_obj,'span9');
    return $readinglist;
}

function aspire_get_html_by_class($domelement, $classname = "span9") {
    $doc = new DomDocument;
    $doc->appendChild($doc->importNode($domelement, true));
    $finder = new DomXPath($doc);
    $nodes = $finder->query("//*[contains(@class, '$classname')]");
    $output = new DomDocument;
    foreach ($nodes as $node) {
        $output->appendChild($output->importNode($node, true)); //= $node->ownerDocument->saveHTML($node);
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