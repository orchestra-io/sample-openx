<?php

/*
+---------------------------------------------------------------------------+
| OpenX v${RELEASE_MAJOR_MINOR}                                                                |
| =======${RELEASE_MAJOR_MINOR_DOUBLE_UNDERLINE}                                                                |
|                                                                           |
| Copyright (c) 2003-2009 OpenX Limited                                     |
| For contact details, see: http://www.openx.org/                           |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id: save.php 30820 2009-01-13 19:02:17Z andrew.hill $
*/

check_environment();

if (array_key_exists('submit', $_REQUEST))
{
    if (preg_match('/[\W\d]+/', $_REQUEST['name']))
    {
        display_error('Not Saved: Scenario name must not contain spaces, numerals or punctuation', $_REQUEST['name']);
    }
    else
    {
        $conf = write_sim_ini_file($conf);
        save_scenario($_REQUEST['name'], $conf);
    }
}

require_once MAX_PATH.'/www/admin/js/jscalendar/calendar.php';
// make simlink to calendar in the simulation folder
// ln -sf ../www/admin/js/jscalendar calendar
$calobj = new DHTML_Calendar();

include TPL_PATH.'/frameheader.html';
$calobj->load_files();
include TPL_PATH.'/body_save_scenario.html';
?>