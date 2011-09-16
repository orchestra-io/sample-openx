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
$Id: Channel.dal.test.php 6839 2007-05-22 20:08:04Z aj@seagullproject.org $
*/

require_once MAX_PATH . '/lib/OA/Dal.php';
require_once MAX_PATH . '/lib/max/Dal/tests/util/DalUnitTestCase.php';

/**
 * A class for testing non standard DataObjects_Channel methods
 *
 * @package    MaxDal
 * @subpackage TestSuite
 *
 */
class DataObjects_Acls_channelTest extends DalUnitTestCase
{
    /**
     * The constructor method.
     */
    function DataObjects_Acls_channelTest()
    {
        $this->UnitTestCase();
    }

    function tearDown()
    {
        DataGenerator::cleanUp();
    }

    function testDuplicate()
    {
        //  create test channel
        $doChannel = OA_Dal::factoryDO('channel');
        $doChannel->acls_updated = '2007-04-03 19:29:54';
        $channelId = DataGenerator::generateOne($doChannel, true);

        //  create test acls
        $doAcls = OA_Dal::factoryDO('acls_channel');
        $doAcls->channelid = $channelId;
        $doAcls->type = 'Client:Ip';
        $doAcls->comparison = '==';
        $doAcls->data = '127.0.0.1';
        $doAcls->executionorder = 1;
        $doAcls->insert();

        $doAcls = OA_Dal::factoryDO('acls_channel');
        $doAcls->channelid = $channelId;
        $doAcls->type = 'Client:Domain';
        $doAcls->comparison = '==';
        $doAcls->data = 'example.com';
        $doAcls->executionorder = 2;
        $doAcls->insert();

        // duplicate
        $newChannelId = OA_Dal::staticDuplicate('channel', $channelId);

        //  retrieve acls for original and duplicate channel
        $doAcls = OA_Dal::factoryDO('acls_channel');
        $doAcls->channelid = $channelId;
        $doAcls->orderBy('executionorder');
        if ($doAcls->find()) {
            while ($doAcls->fetch()) {
                $aAcls[] = clone($doAcls);
            }
        }

        $doNewAcls = OA_Dal::factoryDO('acls_channel');
        $doNewAcls->channelid = $newChannelId;
        $doNewAcls->orderBy('executionorder');
        if ($doNewAcls->find()) {
            while ($doNewAcls->fetch()) {
                $aNewAcls[] = clone($doNewAcls);
            }
        }

        //  iterate through acls ensuring they were properly copied
        if ($this->assertEqual(count($aAcls), count($aNewAcls))) {
            for ($x = 0; $x < count($aAcls); $x++) {
                $this->assertNotEqual($aAcls[$x]->channelid, $aNewAcls[$x]->channelid);
                $this->assertEqualDataObjects($this->stripKeys($aAcls[$x]), $this->stripKeys($aNewAcls[$x]));
            }
        }
    }
}

?>