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
$Id: simconst.php 34420 2009-03-27 15:40:26Z matteo.beccati $
*/

/**
 * @package    Max
 * @subpackage SimulationSuite
 * @author
 */

if (!defined('SIM_PATH')) {
    define('SIM_PATH', MAX_PATH.'/www/devel/simulation/');
}
define('SIM_TMP', SIM_PATH.'/tmp');
define('SIM_TEMPLATES', SIM_PATH.'/templates');
define('SCENARIOS', 'scenarios');
define('SCENARIOS_DATASETS', SIM_PATH.SCENARIOS.'/datasets/');
define('SCENARIOS_REQUESTSETS', SIM_PATH.SCENARIOS.'/requestsets/');

error_reporting(E_ALL ^ E_NOTICE);
define('TEST_ENVIRONMENT_RUNNING', true);

require_once 'lib.inc.php';

$conf['simdb'] = $conf['database'];
$conf['realConfig'] = OX_getHostName();

global $is_simulation;
$is_simulation = true;

?>
