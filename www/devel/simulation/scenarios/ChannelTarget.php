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
$Id: ChannelTarget.php 30820 2009-01-13 19:02:17Z andrew.hill $
*/

require_once SIM_PATH . 'SimulationScenario.php';

/**
 * A class for simulating maintenance/delivery scenarios
 *
 * @package
 * @subpackage
 * @author
 */
class ChannelTarget extends SimulationScenario
{

    /**
     * The constructor method.
     */
    function ChannelTarget()
    {
        $this->init("ChannelTarget");
    }

    function run()
    {
        $this->newTables();
        $this->loadDataset('ChannelTarget.xml');
        $this->printPrecis();

        for($i=1;$i<=$this->scenarioConfig['iterations'];$i++)
        {
            // MAX_checkSite_Pageurl uses $GLOBALS['loc'] instead of source
            // therefore I had to bodge the scenario class to set the $GLOBAL
            // really not sure if this is correct anymore!!
            if ($i==1)
            {
                // all requests in first iteration will fail to be delivered
                $GLOBALS['loc'] = 'www.nowhere.com';
            }
            else
            {
                // all requests after first iteration will be delivered
                $GLOBALS['loc'] = 'www.example.com';
            }
            $this->makeRequests($i);
            $this->runPriority();
            //$this->runMaintenance();
        }
        $this->printPostSummary();
        $this->printSummaryData();
    }
}

?>