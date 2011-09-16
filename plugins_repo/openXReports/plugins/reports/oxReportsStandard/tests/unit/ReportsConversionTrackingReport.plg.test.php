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
$Id: ReportsConversionTrackingReport.plg.test.php 33995 2009-03-18 23:04:15Z chris.nutting $
*/

require_once MAX_PATH . '/lib/max/Plugin.php';
require_once MAX_PATH . '/lib/OA/Dal/DataGenerator.php';

/**
 * A class for testing the Plugins_Reports_Standard_ConversionTrackingReport class.
 *
 * @package    OpenXPlugin
 * @subpackage TestSuite
 * @author     Łukasz Wikierski <lukasz.wikierski@openx.org>
 */
class Plugins_TestOfPlugins_Reports_oxStandard_ConversionTrackingReport extends UnitTestCase
{
    /**
     * @var Plugins_Reports_Standard_ConversionTrackingReport
     */
    var $oPlugin;

    /**
     * The constructor method.
     */
    function Plugins_TestOfPlugins_Reports_oxStandard_ConversionTrackingReport()
    {
        $this->UnitTestCase();
    }

    function setUp()
    {
        //$this->oPlugin = &MAX_Plugin::factory('reports', 'oxStandard', 'conversionTrackingReport');
        $GLOBALS['_MAX']['CONF']['pluginPaths']['plugins'] = str_replace('reports/oxReportsStandard/tests/unit','',dirname(str_replace(MAX_PATH,'',__FILE__)));
        $this->oPlugin = &OX_Component::factory('reports', 'oxReportsStandard', 'conversionTrackingReport');
    }

    function tearDown()
    {
        DataGenerator::cleanUp();
        TestEnv::restoreConfig();
    }

    /**
     * A method to test the _prepareTrackerVariables() method.
     */
    function test_prepareTrackerVariables()
    {
        // Prepare test data
        $doTrackers = OA_Dal::factoryDO('trackers');
        $trackerId = DataGenerator::generateOne($doTrackers);
        $doVariables = OA_Dal::factoryDO('variables');
        $doVariables->trackerid = $trackerId;
        $variableId = DataGenerator::generateOne($doVariables);
        $doAffiliates = OA_Dal::factoryDO('affiliates');
        $publisherId = DataGenerator::generateOne($doAffiliates);
        $doVariablePublisher = OA_Dal::factoryDO('variable_publisher');
        $doVariablePublisher->variable_id = $trackerId;
        $doVariablePublisher->publisher_id = $publisherId;
        $doVariablePublisher->visible = 1;
        $variablepublisherId = DataGenerator::generateOne($doVariablePublisher);

        // Tests with empty connections table
        $result = $this->oPlugin->_prepareTrackerVariables(array());
        $this->assertEqual(count($result),0);
        // Tests with one existing tracker in connections table
        $result = $this->oPlugin->_prepareTrackerVariables(array( $trackerId => array()));
        $this->assertEqual(count($result),1);
    }
}

?>
