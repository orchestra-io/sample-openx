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
$Id: test_data_summary_ad_hourly_001.php 30820 2009-01-13 19:02:17Z andrew.hill $
*/

/**
 *
 * @abstract A class for generating/loading a dataset for delivery testing
 * @package Test Classes
 * @author Monique Szpak <monique.szpak@openads.org>
 *
 */

require_once MAX_PATH . '/tests/testClasses/OATestData_DataObjects.php';
class OA_Test_Data_data_summary_ad_hourly_001 extends OA_Test_Data_DataObjects
{

    function OA_Test_Data_data_summary_ad_hourly_001()
    {
    }

    /**
     * method for extending OA_Test_Data_DataObject
     */

    function generateTestData()
    {
        if (!parent::init())
        {
            return false;
        }

        // Disable Auditing while loading the test data:
        $GLOBALS['_MAX']['CONF']['audit']['enabled'] = false;

        parent::generateTestData();

        for ($hour = 0; $hour < 24; $hour ++)
        {
            $doDSAH = OA_Dal::factoryDO('data_summary_ad_hourly');
            $doDSAH->date_time = sprintf('%s %02d:00:00', substr(OA::getNow(), 0, 10), $hour);
            $doDSAH->ad_id = $this->aIds['banners'][1];
            $doDSAH->creative_id = rand(1, 999);
            $doDSAH->zone_id = $this->aIds['zones'][1];
            $doDSAH->requests = rand(1, 999);
            $doDSAH->impressions = rand(1, 999);
            $doDSAH->clicks = rand(1, 999);
            $doDSAH->conversions = rand(1, 999);
            $doDSAH->total_basket_value = 0;
            $this->aIds['DSAH'][] = DataGenerator::generateOne($doDSAH);
        }
        return $this->aIds;
    }

}
?>

