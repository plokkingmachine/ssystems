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

require_once(__DIR__ . '/../../config.php');  // Moodle config
require_once($CFG->libdir . '/formslib.php'); // Enables forms
require_once(__DIR__ . '/lib.php');           // view.php has to know about lib.php

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/apifetchpower/view.php'));
$PAGE->set_title(get_string('pluginname', 'local_apifetchpower'));
$PAGE->set_heading(get_string('pluginname', 'local_apifetchpower'));

class apifetch_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        // Dropdown for region selection
        // https://api.energy-charts.info/
        // --> Look for 'Available countries'
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
        $mform->addElement('select', 'country', get_string('country', 'local_apifetchpower'), $region_options);
        $mform->setDefault('country', 'de');

        $mform->addElement('text', 'start_time', get_string('starttime', 'local_apifetchpower'));
        $mform->setType('start_time', PARAM_TEXT);
        $mform->setDefault('start_time', '2025-08-01T00:00Z');
        $mform->addRule('start_time', null, 'required');

        $mform->addElement('text', 'end_time', get_string('endtime', 'local_apifetchpower'));
        $mform->setType('end_time', PARAM_TEXT);
        $mform->setDefault('end_time', '2025-09-01T00:00Z');
        $mform->addRule('end_time', null, 'required');

        /* $mform->addElement('password', 'apitoken', get_string('apitoken', 'local_apifetch')); */
        /* $mform->setType('apitoken', PARAM_RAW);


        // Submit button to trigger the request.
        //
        // CHANGED: Two submit buttons, one for JSON, one
        // for chart rendering
        /* $this->add_action_buttons(false, get_string('fetchdata', 'local_apifetch')); */
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'fetchdata', get_string('fetchdata', 'local_apifetchpower'));
        $buttonarray[] = $mform->createElement('submit', 'renderchart', get_string('renderchart', 'local_apifetchpower'));
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }
}

$form = new apifetch_form();

// Initialize variables for display.
$data = null;
$error = null;
$render_chart = false;

if ($form->is_submitted() && $form->is_validated()) {
    $formdata = $form->get_data();

    // Build the API URL using the configured form fields.
    $base_url = 'https://api.energy-charts.info/public_power';
    $url = $base_url . '?country=' . urlencode($formdata->country) . 
           '&start=' . urlencode($formdata->start_time) . 
           '&end=' . urlencode($formdata->end_time);

    // Call the API fetch function from lib.php with the built URL.
    $result = local_apifetchpower_fetch_json($url, $formdata->apitoken);

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
$PAGE->requires->js(new moodle_url('/local/apifetchpower/uplot.min.js'), ['in_head' => true]);
$PAGE->requires->css(new moodle_url('/local/apifetchpower/styles.css'));
echo $OUTPUT->header();

// Display the form.
$form->display();

// If rendering chart and data is available, output the chart div and script.
if ($render_chart && $data !== null) {
    echo html_writer::tag('h3', get_string('charttitle', 'local_apifetchpower'));
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
    echo html_writer::tag('h3', get_string('response', 'local_apifetchpower'));
    echo html_writer::tag('pre', json_encode($data, JSON_PRETTY_PRINT));
}

// Error display
if ($error !== null) {
    echo html_writer::tag('div', get_string('error', 'local_apifetchpower') . ': ' . $error, ['class' => 'alert alert-danger']);
}

echo $OUTPUT->footer();
