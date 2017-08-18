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
 * Responsible for adding and deleting events attendees
 *
 * @package     local_eventattendance
 * @copyright   2017 Joao Almeida <contact@joaoalmeida.me>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->dirroot.'/calendar/lib.php');

//Make this file handle the post/get requests to generate and delete entries in the attendanceevents database

$eventid = required_param('id', PARAM_INT);
$event = calendar_event::load($eventid);
global $USER;

/**
 * We are going to be picky here, and require that any event types other than
 * group and site be associated with a course. This means any code that is using
 * custom event types (and there are a few) will need to associate thier event with
 * a course
 */
if ($event->eventtype !== 'user' && $event->eventtype !== 'site') {
    $courseid = $event->courseid;
}
$course = $DB->get_record('course', array('id'=>$courseid));
require_login($course);
if (!$course) {
    $PAGE->set_context(context_system::instance());
}

if ($eventid != 0) {

	$viewcalendarurl = new moodle_url(CALENDAR_URL.'view.php', array('view'=>'day', 'course'=> $event->courseid));
	$viewcalendarurl->param('time', $event->timestart, '%Y');

    // Delete the event and possibly repeats
    if (!check_user_attendance($eventid, $USER->id)) {
    	insert_user_attendance($eventid, $USER->id);
    }
    else {
    	delete_user_attendance($eventid, $USER->id);
    }

    // And redirect
    redirect($viewcalendarurl.'#event_'.$eventid);
}
?>