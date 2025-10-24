<?php
require_once('../../config.php');

$courseid = required_param('id', PARAM_INT);
core_courseformat\activityoverviewbase::redirect_to_overview_page($courseid, 'apifetchpower');

