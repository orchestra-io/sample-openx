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
$Id: ChrisBasic.php 30820 2009-01-13 19:02:17Z andrew.hill $
*/

require_once SIM_PATH . 'SimulationScenario.php';

/**
 * A class for simulating maintenance/delivery scenarios
 *
 * @package
 * @subpackage
 * @author
 */
class ChrisBasic extends SimulationScenario
{

    /**
     * The constructor method.
     */
    function ChrisBasic()
    {
        $this->init("ChrisBasic");
        $this->setDateTime($GLOBALS['_MAX']['CONF']['sim']['starthour'], $GLOBALS['_MAX']['CONF']['sim']['startday']);
    }

    function run()
    {
        $this->newTables();
        $this->loadDataset('ChrisBasic');
        $this->printPrecis();
        for($i=1;$i<=$this->scenarioConfig['iterations'];$i++)
        {
            $this->printHeading('Started iteration: '. $i, 3);
            $this->runPriority();
            $this->makeRequests($i);
            $this->printHeading('Ended iteration: '. $i, 3);
        }
        //$this->runMaintenance();
        $this->printPostSummary();
        $this->printSummaryData();
    }

}

?>