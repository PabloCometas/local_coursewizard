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
 * English language strings.
 *
 * @package     local_coursewizard
 * @copyright   2026 cometas
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['askdates'] = 'Ask for course start and end date';
$string['askdates_desc'] = 'The wizard asks for the course start and end date. Useful for courses with a fixed term or period (e.g. universities). If disabled, the site defaults apply (Site administration → Courses → Default settings).';
$string['category'] = 'Category';
$string['coursename'] = 'Course name';
$string['createnow'] = 'Create course';
$string['decision'] = 'Thanks, {$a->name}! You have entered everything needed and can create your course now. Would you like to add more details, such as {$a->details}?';
$string['emptycourse'] = 'Empty course';
$string['eventcapacity'] = 'Places';
$string['eventcapacitymin'] = 'At least one place is needed.';
$string['eventcreated'] = 'Your event "{$a}" has been created. Participants can now sign up.';
$string['eventend'] = 'End';
$string['eventendbeforestart'] = 'The end must be after the start.';
$string['events'] = 'Offer event courses';
$string['events_desc'] = 'The wizard first asks whether to create a course or an event. An event course offers a session to sign up for (face-to-face with guest access) and appears in the course explorer under "Events". If the site has no in-person events, leave this off. Requires face-to-face to be installed and the enrolment method "Guest access" to be enabled.';
$string['eventstart'] = 'Start';
$string['explain'] = 'What is this for?';
$string['explain_capacity'] = 'This many people can sign up for the session. When it is full, the course catalogue shows it as booked out. Further sessions, a waiting list or costs can be set up later in the event.';
$string['explain_category'] = 'Categories organise the courses on the site, much like folders. In the course list, your course appears in this category. It often also decides who may manage the course. If you are unsure, keep the suggestion. You can move the course at any time later.';
$string['explain_dates'] = 'The start date defines from when the course counts as in progress, for example in timelines and reports. After the end date, Moodle shows it to participants under "Past". The course is not locked: enrolled users can still open it.';
$string['explain_eventdate'] = 'This is the first session of your event. It appears in the course catalogue under "Events", and participants can sign up for it directly. You can add further sessions later in the event.';
$string['explain_fullname'] = 'The course name appears wherever your course shows up: at the top of the course page, on the Dashboard and in "My courses", in the course list, in the navigation and in notifications to participants. Choose a short, clear name that makes the course easy to recognise in a list. You can change it at any time later.';
$string['explain_image'] = 'The image appears as a card on the Dashboard, in "My courses" and in the course list. A landscape image with an aspect ratio of 16:9 works best, for example 1200 × 675 pixels, as JPG or PNG. Keep important content in the centre, as the edges are cropped in some views. Text in the image is hard to read on small cards. Without an image, Moodle shows a coloured pattern. If you create the course from a template and choose no image of your own, it gets the image of the template.';
$string['explain_kind'] = 'An e-learning or blended course contains learning content that participants work through themselves, possibly with in-person parts. An event is an occasion with a fixed date, for example a workshop or a networking event. People sign up for it instead of enrolling in a course. Events appear in the course catalogue under "Events" and not under "My courses".';
$string['explain_more'] = 'Your site requires this information so that all courses are organised consistently. If you have questions about it, ask the administrators of your site.';
$string['explain_summary'] = 'People see the description in the course list before opening the course, and when they enrol themselves. Two or three sentences are enough: what it is about, who it is for and what you can do afterwards.';
$string['explain_visible'] = '"Show" means: enrolled participants see the course and can open it. "Hide" means: only you and other teachers see it. This is handy while you are still building the course. You can change the visibility at any time later.';
$string['greeting'] = 'Hi {$a}! I will help you create a new course, step by step.';
$string['imagefromtemplate'] = '(from template)';
$string['kind'] = 'Type';
$string['kindcourse'] = 'E-learning / blended';
$string['kindcourse_help'] = 'Learning content participants work through themselves, possibly with in-person parts. Appears under "My courses".';
$string['kindevent'] = 'Event';
$string['kindevent_help'] = 'An occasion with a fixed date, for example a workshop. People sign up for the session. Appears in the course catalogue under "Events".';
$string['later'] = 'Later';
$string['moredetails'] = 'Add more details';
$string['optionalhint'] = 'That covers the essentials. You can create your course now or add a few optional details.';
$string['pluginname'] = 'Course wizard';
$string['privacy:metadata'] = 'The Course wizard plugin does not store any personal data.';
$string['searchcategory'] = 'Choose another category …';
$string['step_capacity'] = 'How many places are there?';
$string['step_category'] = 'In which category should your course appear? One is already selected. If it does not fit, click the field and choose another one.';
$string['step_dates'] = 'When does your course start, and when does it end?';
$string['step_done'] = 'Thanks, {$a}! Everything is ready. Check the answers above and create your course.';
$string['step_eventdate'] = 'When does your event take place?';
$string['step_fullname'] = 'What should your new course be called?';
$string['step_image'] = 'Do you have an image for your course? This is optional too.';
$string['step_kind'] = 'What would you like to create?';
$string['step_more'] = 'On this site, the course also needs the following:';
$string['step_summary'] = 'Would you like to describe your course briefly? This is optional.';
$string['step_visible'] = 'Should participants already be able to see your course?';
$string['template'] = 'Template';
$string['templateapplied'] = 'Your course was created from the template "{$a}".';
$string['templateimage'] = 'This is the image of the template. You can upload your own or use it.';
$string['usetemplate'] = 'Create from a template';
$string['usetemplateimage'] = 'Use it';
$string['viewtemplate'] = 'View';
