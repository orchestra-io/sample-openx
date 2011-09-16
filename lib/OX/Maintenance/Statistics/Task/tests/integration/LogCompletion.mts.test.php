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
$Id: LogCompletion.mts.test.php 30820 2009-01-13 19:02:17Z andrew.hill $
*/

require_once LIB_PATH . '/Maintenance/Statistics.php';
require_once LIB_PATH . '/Maintenance/Statistics/Task/LogCompletion.php';

require_once MAX_PATH . '/lib/OA/ServiceLocator.php';
require_once MAX_PATH . '/lib/pear/Date.php';

/**
 * A class for testing the OX_Maintenance_Statistics_Task_LogCompletion class.
 *
 * @package    OpenXMaintenance
 * @subpackage TestSuite
 * @author     Andrew Hill <andrew.hill@openx.org>
 */
class Test_OX_Maintenance_Statistics_Task_LogCompletion extends UnitTestCase
{

    /**
     * The constructor method.
     */
    function Test_OX_Maintenance_Statistics_Task_LogCompletion()
    {
        $this->UnitTestCase();
    }

    /**
     * Test the creation of the class.
     */
    function testCreate()
    {
        $oLogCompletion = new OX_Maintenance_Statistics_Task_LogCompletion();
        $this->assertTrue(is_a($oLogCompletion, 'OX_Maintenance_Statistics_Task_LogCompletion'));
    }

    /**
     * A method to test the run() method.
     */
    function testRun()
    {
        // Reset the testing environment
        TestEnv::restoreEnv();

        $aConf =& $GLOBALS['_MAX']['CONF'];
        $oTable =& OA_DB_Table_Core::singleton();
        $oDbh =& OA_DB::singleton();
        $oServiceLocator =& OA_ServiceLocator::instance();

        $oNow = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $oNow);
        // Create and register a new OX_Maintenance_Statistics object
        $oMaintenanceStatistics = new OX_Maintenance_Statistics();
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new OX_Maintenance_Statistics_Task_LogCompletion object
        $oLogCompletion = new OX_Maintenance_Statistics_Task_LogCompletion();

        // Set some of the object's variables, and log
        $oLogCompletion->oController->updateIntermediate = true;
        $oLogCompletion->oController->oUpdateIntermediateToDate = new Date('2004-06-06 17:59:59');
        $oLogCompletion->oController->updateFinal = false;
        $oLogCompletion->oController->oUpdateFinalToDate = null;
        $oEnd = new Date('2004-06-06 18:12:00');
        $oLogCompletion->run($oEnd);
        // Test
        $query = "
            SELECT
                *
            FROM
                ".$oDbh->quoteIdentifier($aConf['table']['prefix'].$aConf['table']['log_maintenance_statistics'],true)."
            WHERE
                adserver_run_type = 0";
        $rc = $oDbh->query($query);
        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['start_run'], '2004-06-06 18:10:00');
        $this->assertEqual($aRow['end_run'], '2004-06-06 18:12:00');
        $this->assertEqual($aRow['duration'], 120);
        $this->assertEqual($aRow['updated_to'], '2004-06-06 17:59:59');
        // Set some of the object's variables, and log
        $oLogCompletion->oController->updateIntermediate = false;
        $oLogCompletion->oController->oUpdateIntermediateToDate = null;
        $oLogCompletion->oController->updateFinal = true;
        $oLogCompletion->oController->oUpdateFinalToDate = new Date('2004-06-06 17:59:59');
        $oEnd = new Date('2004-06-06 18:13:00');
        $oLogCompletion->run($oEnd);
        // Test
        $query = "
            SELECT
                *
            FROM
                ".$oDbh->quoteIdentifier($aConf['table']['prefix'].$aConf['table']['log_maintenance_statistics'],true)."
            WHERE
                adserver_run_type = 1";
        $rc = $oDbh->query($query);
        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['start_run'], '2004-06-06 18:10:00');
        $this->assertEqual($aRow['end_run'], '2004-06-06 18:13:00');
        $this->assertEqual($aRow['duration'], 180);
        $this->assertEqual($aRow['updated_to'], '2004-06-06 17:59:59');
        // Set some of the object's variables, and log
        $oLogCompletion->oController->updateIntermediate = true;
        $oLogCompletion->oController->oUpdateIntermediateToDate = new Date('2004-06-06 17:59:59');
        $oLogCompletion->oController->updateFinal = true;
        $oLogCompletion->oController->oUpdateFinalToDate = new Date('2004-06-06 17:59:59');
        $oEnd = new Date('2004-06-06 18:14:00');
        $oLogCompletion->run($oEnd);
        // Test
        $query = "
            SELECT
                *
            FROM
                ".$oDbh->quoteIdentifier($aConf['table']['prefix'].$aConf['table']['log_maintenance_statistics'],true)."
            WHERE
                adserver_run_type = 2";
        $rc = $oDbh->query($query);
        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['start_run'], '2004-06-06 18:10:00');
        $this->assertEqual($aRow['end_run'], '2004-06-06 18:14:00');
        $this->assertEqual($aRow['duration'], 240);
        $this->assertEqual($aRow['updated_to'], '2004-06-06 17:59:59');

        // Reset the testing environment
        TestEnv::restoreEnv();
    }

    /**
     * Method to test the testSetMaintenanceStatisticsRunReport method.
     *
     * Requirements:
     * Test 1: Test two writes to reports.
     */
    function testSetMaintenanceStatisticsRunReport()
    {
        $aConf = $GLOBALS['_MAX']['CONF'];
        $oDbh =& OA_DB::singleton();

        // Create a new OX_Maintenance_Statistics_Task_LogCompletion object
        $oLogCompletion = new OX_Maintenance_Statistics_Task_LogCompletion();

        // Test 1
        $report = 'Maintenance run has finished :: Maintenance will run again at XYZ.';
        $oLogCompletion->_setMaintenanceStatisticsRunReport($report);

        $query = "
            SELECT
                timestamp,
                usertype,
                userid,
                action,
                object,
                details
            FROM
                ".$oDbh->quoteIdentifier($aConf['table']['prefix'].$aConf['table']['userlog'],true)."
            WHERE
                userlogid = 1";

        $rc = $oDbh->query($query);
        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['usertype'], phpAds_userMaintenance);
        $this->assertEqual($aRow['userid'], '0');
        $this->assertEqual($aRow['action'], phpAds_actionBatchStatistics);
        $this->assertEqual($aRow['object'], '0');
        $this->assertEqual($aRow['details'], $report);

        $report = '2nd Maintenance run has finished :: Maintenance will run again at XYZ.';
        $oLogCompletion->_setMaintenanceStatisticsRunReport($report);

        $query = "
            SELECT
                timestamp,
                usertype,
                userid,
                action,
                object,
                details
            FROM
                ".$oDbh->quoteIdentifier($aConf['table']['prefix'].$aConf['table']['userlog'],true)."
            WHERE
                userlogid = 2";

        $rc = $oDbh->query($query);
        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['usertype'], phpAds_userMaintenance);
        $this->assertEqual($aRow['userid'], '0');
        $this->assertEqual($aRow['action'], phpAds_actionBatchStatistics);
        $this->assertEqual($aRow['object'], '0');
        $this->assertEqual($aRow['details'], $report);

        TestEnv::restoreEnv();
    }

}

?>