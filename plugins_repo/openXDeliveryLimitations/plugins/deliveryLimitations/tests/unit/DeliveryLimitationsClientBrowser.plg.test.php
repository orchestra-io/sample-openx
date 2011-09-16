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
$Id: DeliveryLimitationsClientBrowser.plg.test.php 33995 2009-03-18 23:04:15Z chris.nutting $
*/

require_once MAX_PATH . '/lib/max/Plugin.php';
// Using multi-dirname so that the tests can run from either plugins or plugins_repo
require_once dirname(dirname(dirname(__FILE__))) . '/Client/Browser.delivery.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/etc/Client/etc/postscript_install_Client.php';

/**
 * A class for testing the Plugins_DeliveryLimitations_Client_Browser class.
 *
 * @package    OpenXPlugin
 * @subpackage TestSuite
 * @author     Andrzej Swedrzynski <andrzej.swedrzynski@m3.net>
 */
class Plugins_TestOfPlugins_DeliveryLimitations_Client_Browser extends UnitTestCase
{

    function test_postscript_install_Client()
    {
        $oSettings  = new OA_Admin_Settings();
        $oSettings->settingChange('logging','sniff','1');
        $oSettings->writeConfigChange();
        $this->assertTrue($GLOBALS['_MAX']['CONF']['logging']['sniff']);
        $oPostInstall = new postscript_install_Client();
        $oPostInstall->execute();
        $this->assertNull($GLOBALS['_MAX']['CONF']['logging']['sniff']);
        $this->assertTrue($GLOBALS['_MAX']['CONF']['Client']['sniff']);
    }

    function testMAX_checkClient_Browser()
    {
        $GLOBALS['_MAX']['CLIENT']['browser'] = 'FF';
        $this->assertFalse(MAX_checkClient_Browser('LX,LI', '=~'));
        $this->assertTrue(MAX_checkClient_Browser('LX,FF', '=~'));
    }
}
?>
