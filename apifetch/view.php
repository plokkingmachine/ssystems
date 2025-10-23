<?php
// This file is part of Moodle - http://moodle.com/
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
 * Main view for the local_apifetch plugin.
 *
 * @package   local_apifetch
 * @copyright 2025 FW
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php'); // Include Moodle config.
require_once($CFG->libdir . '/formslib.php'); // Include formslib for forms.
require_once(__DIR__ . '/lib.php');           // view.php has to know about lib.php

// Require login and set page context.
require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/apifetch/view.php'));
$PAGE->set_title(get_string('pluginname', 'local_apifetch'));
$PAGE->set_heading(get_string('pluginname', 'local_apifetch'));

// Define the form class extending moodleform.
class apifetch_form extends moodleform {
    /**
     * Define the form elements.
     */
    public function definition() {
        $mform = $this->_form;

        // Text field for API URL.
        $mform->addElement('text', 'apiurl', get_string('apiurl', 'local_apifetch'));
        $mform->setType('apiurl', PARAM_URL); // Validate as URL.
        $mform->addRule('apiurl', null, 'required'); // Required field.

        // Text field for authentication token (password type for security).
        $mform->addElement('password', 'apitoken', get_string('apitoken', 'local_apifetch'));
        $mform->setType('apitoken', PARAM_RAW); // Raw input, as tokens can be complex.

        // Submit button to trigger the request.
        $this->add_action_buttons(false, get_string('fetchdata', 'local_apifetch'));
    }
}

// Instantiate the form.
$form = new apifetch_form();

// Initialize variables for display.
$data = null;
$error = null;

// Handle form submission.
if ($form->is_submitted() && $form->is_validated()) {
    $formdata = $form->get_data();

    // Call the API fetch function from lib.php.
    $result = local_apifetch_fetch_json($formdata->apiurl, $formdata->apitoken);

    if ($result['success']) {
        $data = $result['data'];
    } else {
        $error = $result['error'];
    }
}

// Output the page header.
echo $OUTPUT->header();

// Display the form.
$form->display();

// If there's data, display the JSON response.
if ($data !== null) {
    echo html_writer::tag('h3', get_string('response', 'local_apifetch'));
    echo html_writer::tag('pre', json_encode($data, JSON_PRETTY_PRINT)); // Pretty-print JSON for readability.
}

// If there's an error, display it.
if ($error !== null) {
    echo html_writer::tag('div', get_string('error', 'local_apifetch') . ': ' . $error, ['class' => 'alert alert-danger']);
}

// Output the page footer.
echo $OUTPUT->footer();
