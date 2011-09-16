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
$Id: oxMarketMaintenance.class.php 33995 2009-03-18 23:04:15Z chris.nutting $
*/

require_once LIB_PATH . '/Extension/maintenanceStatisticsTask/MaintenanceStatisticsTask.php';
require_once 'ImportMarketStatistics.php';

/**
 * Class implementing addMaintenanceStatisticsTask hook for oxMarket statistics
 *
 * @package    OpenXPlugin
 * @subpackage oxMarket
 * @author     Lukasz Wikierski <lukasz.wikierski@openx.org>
 */
class Plugins_MaintenanceStatisticsTask_oxMarketMaintenance_oxMarketMaintenance extends Plugins_MaintenanceStatisticsTask
{
    /**
     * Method returns OX_Maintenance_Statistics_Task 
     * to run in the Maintenance Statistics Engine
     * Implements hook 'addMaintenanceStatisticsTask'
     * 
     * @return OX_Maintenance_Statistics_Task
     */
    function addMaintenanceStatisticsTask()
    {
        return new Plugins_MaintenaceStatisticsTask_oxMarketMaintenance_ImportMarketStatistics();
    }
}
?>