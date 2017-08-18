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
 * Plugin internal classes, functions and constants are defined here.
 *
 * @package     local_eventattendance
 * @copyright   2017 Joao Almeida <contact@joaoalmeida.me>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//defined('MOODLE_INTERNAL') || die();

/**
 * Record user attendance
 *
 * @param  int $eventid Calendar Event ID.
 * @param  int $userid  User ID.
 */
function insert_user_attendance($eventid, $userid) {
	global $DB;

    $record = new stdClass();
    $record->userid = $userid;
    $record->eventid = $eventid;

    $table = 'local_eventattendance';

    return $DB->insert_record($table, $record);
}

/**
 * Delete user attendance
 *
 * @param  int $eventid Calendar Event ID.
 * @param  int $userid  User ID.
 */
function delete_user_attendance($eventid, $userid) {
	global $DB;

    $record = new stdClass();
    $record->userid = $userid;
    $record->eventid = $eventid;

    $table = 'local_eventattendance';

    return $DB->delete_records($table, array('userid' => $userid, 'eventid' => $eventid) );
}

/**
 * Get user attendance for an event
 *
 * @param  int $eventid Calendar Event ID.
 * @param  int $userid  User ID.
 * @return bool True if user is attending event
 */
function check_user_attendance($eventid, $userid){
	global $DB;

	$table = 'local_eventattendance';

	return $DB->record_exists($table, array('eventid' => $eventid, 'userid' => $userid));
}

/**
 * Get event atendees
 *
 * @param  int $eventid Calendar Event ID.
 * @return array User objects array.
 */
function get_event_atendees($eventid){
	global $DB;

	$table = 'local_eventattendance';

	if($DB->record_exists('event', array('id' => $eventid) )){

		$usersInfo = array();

		//This event exists
		$users = $DB->get_records($table, array('eventid' => $eventid));

		foreach ($users as $user) {
			
			$result = $DB->get_record('user', array('id' => $user->userid));

			//$userInfo = new stdClass;
			//$userInfo->name = $result->firstname . ' ' . $result->lastname;
			//$userInfo->id = $user->userid;

			array_push($usersInfo, $result);
		}

		return $usersInfo;
		
	}

	return null;
}

function render_event_attendees($eventid){
	global $OUTPUT;
	$attendees = get_event_atendees($eventid);
	$out = '';

	foreach ($attendees as $attendee) {

		$userpicture = new user_picture($attendee);
        $userpicture->link = false;
        $userpicture->alttext = false;
        $userpicture->size = 100;

    	$out .= "<div class=\"snap-media-object\" style=\"text-align: center;  margin-right: 10px;\">";
        $out .= "<a href=\"" . new moodle_url('/user/profile.php', array('id'=>$attendee->id)) . "\">";
        $out .= $OUTPUT->render($userpicture);
        $out .= "<div class=\"snap-media-body\">";
        $out .= "<p>". $attendee->firstname . ' ' . $attendee->lastname . "</p>";
        $out .= "</a></div></div>";      
    }

    if($out == ''){
    	$out .= '<p>No people attending this event.</p>';
    }

    return $out;
}

