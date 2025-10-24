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
 * Main view for the local_apifetchpower plugin.
 *
 * @package   local_apifetchpower
 * @copyright 2025 FW
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php'); // Include Moodle config.
require_once($CFG->libdir . '/formslib.php'); // Include formslib for forms.
require_once(__DIR__ . '/lib.php');           // view.php has to know about lib.php

/* Course module ID */
/* $id = required_param('id', PARAM_INT); */
/* AVOID DATABASE REQUEST */
$id = optional_param('id', 0, PARAM_INT);

/* AVOID DATABASE REQUEST */
/* $cm = get_coursemodule_from_id('apifetchpower', $id, 0, false, MUST_EXIST); */


/* list($course, $cm) = get_course_and_cm_from_cmid($id, 'apifetchpower'); */
/* $instance = $DB->get_record('apifetchpower', ['id' => $cm->instance], '*', MUST_EXIST); */


// --- Provide minimal in-memory objects to avoid DB queries ---
$cm = new stdClass();
$cm->id = $id;
$cm->instance = 0;
$cm->course = 1; // arbitrary course id (no DB lookup)

$course = new stdClass();
$course->id = $cm->course;
$course->fullname = 'Site'; // used for page heading

$instance = new stdClass();
$instance->id = 0;
$instance->name = 'API Fetch Power';
$instance->intro = ''; // set any static intro text here if needed




// Require login and set page context.
/* require_login(); */
/* $context = context_system::instance(); */

/* AVOID DATABASE REQUESTS */
/* require_course_login($course, true, $cm); */
/* $context = context_module::instance($cm->id); */

// Use site-level login/context instead of course/module context to avoid DB lookups
require_login(); // ensures user is logged in (site level)
$context = context_system::instance();





$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/mod/apifetchpower/view.php', ['id' => $id]));
/* $PAGE->set_url('/mod/apifetchpower/view.php', ['id' => $cm->id]); */
/* $PAGE->set_title(get_string('pluginname', 'local_apifetchpower')); */
$PAGE->set_title(format_string($instance->name));
/* $PAGE->set_heading(get_string('pluginname', 'local_apifetchpower')); */
$PAGE->set_heading(format_string($course->fullname));

// BEFORE $OUTPUT -> header()
$PAGE->requires->css(new moodle_url('/mod/apifetchpower/styles.css'));
$PAGE->requires->js(new moodle_url('/mod/apifetchpower/uplot.min.js'), ['in_head' => true]);


echo $OUTPUT->header();
/* echo $OUTPUT->image_tag($OUTPUT->image_url('logo.svg', 'mod_apifetchpower'), ['alt' => get_string('pluginname', 'mod_apifetchpower'), 'class' => 'apifetch-icon']); */
// Get the resolved URL for the icon.
$iconurl = $OUTPUT->image_url('icon', 'mod_apifetchpower')->out(false);

// Output a plain <img> tag using html_writer (safe, theme-independent).
echo html_writer::empty_tag('img', [
    'src'   => $iconurl,
    'alt'   => get_string('pluginname', 'mod_apifetchpower'),
    'class' => 'apifetch-icon',
    'style' => 'height:150px;margin-top:1em;margin-bottom:1em;'
]);
echo $OUTPUT->heading(format_string($instance->name));

// Show intro if present (FEATURE_MOD_INTRO)
if (!empty($instance->intro)) {
    /* echo format_module_intro('apifetchpower', $instance, $cm->id); */
    echo $OUTPUT->box(format_text($instance->intro), 'generalbox mod_introbox', 'intro');
}

// Define the form class extending moodleform.
class apifetch_form extends moodleform {
    /**
     * Define the form elements.
     */
    public function definition() {
        $mform = $this->_form;


        $id = optional_param('id', 0, PARAM_INT);
        $cm = new stdClass();
        $cm->id = $id;
        $cm->instance = 0;
        $cm->course = 1; // arbitrary course id (no DB lookup)

        // Hidden field with $cm id
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $cm->id);

        //
        // Dropdown for region selection
        $region_options = [
          'de' => 'Germany',
          'ch' => 'Switzerland',
          'eu' => 'European Union',
          'all' => 'Europe',
          'al' => 'Albania',
          'am' => 'Armenia',
          'at' => 'Austria',
          'az' => 'Azerbaijan',
          'ba' => 'Bosnia-Herzegovina',
          'be' => 'Belgium',
          'bg' => 'Bulgaria',
          'by' => 'Belarus',
          'cy' => 'Cyprus',
          'cz' => 'Czech Republic',
          'dk' => 'Denmark',
          'ee' => 'Estonia',
          'es' => 'Spain',
          'fi' => 'Finland',
          'fr' => 'France',
          'ge' => 'Georgia',
          'gr' => 'Greece',
          'hr' => 'Croatia',
          'hu' => 'Hungary',
          'ie' => 'Ireland',
          'it' => 'Italy',
          'lt' => 'Lithuania',
          'lu' => 'Luxembourg',
          'lv' => 'Latvia',
          'md' => 'Moldova',
          'me' => 'Montenegro',
          'mk' => 'North Macedonia',
          'mt' => 'Malta',
          'nie' => 'North Ireland',
          'nl' => 'Netherlands',
          'no' => 'Norway',
          'pl' => 'Poland',
          'pt' => 'Portugal',
          'ro' => 'Romania',
          'rs' => 'Serbia',
          'ru' => 'Russia',
          'se' => 'Sweden',
          'si' => 'Slovenia',
          'sk' => 'Slovak Republic',
          'tr' => 'Turkey',
          'ua' => 'Ukraine',
          'uk' => 'United Kingdom',
          'xk' => 'Kosovo',
        ];
        $mform->addElement('select', 'country', get_string('country', 'apifetchpower'), $region_options);
        $mform->setDefault('country', 'de'); // Default value.

        // Text field for start time (format: 2025-01-01T17:00Z).
        $mform->addElement('text', 'start_time', get_string('starttime', 'apifetchpower'));
        $mform->setType('start_time', PARAM_TEXT); // Raw text input.
        $mform->setDefault('start_time', '2025-08-01T00:00Z'); // Default value.
        $mform->addRule('start_time', null, 'required'); // Required field.

        // Text field for end time (format: 2025-01-01T18:00Z).
        $mform->addElement('text', 'end_time', get_string('endtime', 'apifetchpower'));
        $mform->setType('end_time', PARAM_TEXT); // Raw text input.
        $mform->setDefault('end_time', '2025-09-01T00:00Z'); // Default value.
        $mform->addRule('end_time', null, 'required'); // Required field.

        /* // Text field for authentication token (password type for security). */
        /* $mform->addElement('password', 'apitoken', get_string('apitoken', 'local_apifetch')); */
        /* $mform->setType('apitoken', PARAM_RAW); // Raw input, as tokens can be complex. */


        // Submit button to trigger the request.
        //
        // CHANGED: Two submit buttons, one for JSON, one
        // for chart rendering
        /* $this->add_action_buttons(false, get_string('fetchdata', 'local_apifetch')); */
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'fetchdata', get_string('fetchdata', 'apifetchpower'));
        $buttonarray[] = $mform->createElement('submit', 'renderchart', get_string('renderchart', 'apifetchpower'));
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }
}

// Instantiate the form.
$form = new apifetch_form();
$form->set_data((object)['id' => $cm->id]);

// Initialize variables for display.
$data = null;
$error = null;
$render_chart = false;

// Handle form submission.
if ($form->is_submitted() && $form->is_validated()) {
    $formdata = $form->get_data();

    // Build the API URL automatically using form fields.
    $base_url = 'https://api.energy-charts.info/public_power';
    $url = $base_url . '?country=' . urlencode($formdata->country) . 
           '&start=' . urlencode($formdata->start_time) . 
           '&end=' . urlencode($formdata->end_time);

    // Call the API fetch function from lib.php with the built URL.
    $result = local_apifetchpower_fetch_json($url);

    if ($result['success']) {
        $data = $result['data'];
        // Check which button was pressed.
        if (isset($formdata->renderchart)) {
            $render_chart = true;
        }
    } else {
        $error = $result['error'];
    }
}

// Output the page header.
// uPlot lib
/* echo $OUTPUT->header(); */

// Display the form.
$form->display();

// If rendering chart and data is available, output the chart div and script.
if ($render_chart && $data !== null) {
    echo html_writer::tag('h3', get_string('charttitle', 'apifetchpower'));
    // Container for all charts.
    echo '<div id="apifetch-charts-container">';
    // Dynamically generate divs for each production_type.
    if (isset($data['production_types'])) {
        foreach ($data['production_types'] as $index => $type) {
            echo '<div id="chart-' . $index . '" style="width: 100%; height: 400px; margin-bottom: 20px;"></div>';
        }
    }
    echo '</div>';
    echo '<script>
        // Ensure uPlot initializes on clean elements.
        const apiData = ' . json_encode($data) . ';
        if (apiData.unix_seconds && apiData.production_types) {
            // Prepare data for each chart: [unix_seconds, type_data]
            const chartsData = apiData.production_types.map(type => [apiData.unix_seconds, type.data]);

            // uPlot sync setup.
            const mooSync = uPlot.sync("moo");
            const synced = true; // Enable sync by default.

            let syncedUpDown = true;

				    function upDownFilter(type) {
				    	return syncedUpDown || (type != "mouseup" && type != "mousedown");
				    }

            const matchSyncKeys = (own, ext) => own == ext;

            // Cursor options for sync.
            const cursorOpts = {
                lock: true,
                focus: { prox: 16 },
                dblclick: true,
                x: { show: true },
                y: { show: true },
                sync: {
                    key: mooSync.key,
                    setSeries: true,
                    match: [matchSyncKeys, matchSyncKeys],
                    filters: { pub: upDownFilter, }
                },
            };

            // Common options for all charts.
            const baseOpts = {
                width: 1200,
                height: 400,
                cursor: cursorOpts,
                series: [
                    {}, // X-axis (time).
                    {
                        label: "Value",
                        stroke: "blue",
                        value: (u, v) => v == null ? "-" : v.toFixed(2),
                    }
                ],
                axes: [
                    {
                        label: "Time",
                        values: (u, vals) => vals.map(v => {
                            const date = new Date(v * 1000);
                            return date.toLocaleDateString() + "\n" + date.toLocaleTimeString();
                        }),
                    },
                    {
                        label: "Value",
                    },
                ],
            };

            // Create and sync each chart.
            const uplots = [];
            apiData.production_types.forEach((type, index) => {
            //console.log("Creating chart for:", type.name, "index:", index); // Debug: Check loop progress.

            const opts = { ...baseOpts };
            opts.title = type.name;
            opts.series[1].label = type.name;

            const chartElement = document.getElementById("chart-" + index);
            if (chartElement && !chartElement.hasChildNodes()) {
                try {
                    const uplot = new uPlot(opts, chartsData[index], chartElement);
                    //console.log("Chart created for:", type.name); // Debug: Confirm creation.

                    // Zoom reset for all plots.
                    uplot.on("dblclick", () => {
                        uplots.forEach(up => {
                            up.setScale("x", { min: null, max: null });
                            up.setScale("y", { min: null, max: null });
                        });
                    });
                    uplots.push(uplot);
                    if (synced) {
                        mooSync.sub(uplot);
                    }
                } catch (error) {
                    console.error("Error creating chart for", type.name, ":", error); // Debug: Catch errors.
                }
            } else {
                console.warn("Chart element not found or not empty for index:", index); // Debug: Element issues.
            }
    });
    //console.log("Total charts created:", uplots.length); // Debug: Final count.
        } else {
            document.getElementById("apifetch-charts-container").innerHTML = "Invalid data format.";
        }
    </script>';
} elseif (!$render_chart && $data !== null) {
    // Display JSON if "Fetch Data" was pressed.
    echo html_writer::tag('h3', get_string('response', 'apifetchpower'));
    echo html_writer::tag('pre', json_encode($data, JSON_PRETTY_PRINT));
}

// If there's an error, display it.
if ($error !== null) {
    echo html_writer::tag('div', get_string('error', 'apifetchpower') . ': ' . $error, ['class' => 'alert alert-danger']);
}

// Output the page footer.
echo $OUTPUT->footer();
