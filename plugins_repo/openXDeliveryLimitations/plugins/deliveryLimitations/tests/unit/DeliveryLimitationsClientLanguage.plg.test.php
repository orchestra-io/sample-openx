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
$Id: DeliveryLimitationsClientLanguage.plg.test.php 33995 2009-03-18 23:04:15Z chris.nutting $
*/

require_once MAX_PATH . '/lib/max/Plugin.php';
// Using multi-dirname so that the tests can run from either plugins or plugins_repo
require_once dirname(dirname(dirname(__FILE__))) . '/Client/Language.delivery.php';

/**
 * A class for testing the Plugins_DeliveryLimitations_Client_Language class.
 *
 * @package    OpenXPlugin
 * @subpackage TestSuite
 * @author     Andrew Hill <andrew@m3.net>
 */
class Plugins_TestOfPlugins_DeliveryLimitations_Client_Language extends UnitTestCase
{
    function setUp()
    {
        $this->langSave = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }
    
    function tearDown()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $this->langSave;
    }
    
    function testMAX_checkClient_Language()
    {
        $this->assertTrue(MAX_checkClient_Language('en', '=~', array('language' => 'en')));
        $this->assertFalse(MAX_checkClient_Language('en', '!~', array('language' => 'en')));
        $this->assertTrue(MAX_checkClient_Language('en,pl,fr,de', '=~', array('language' => 'en')));
        $this->assertTrue(MAX_checkClient_Language('en,pl,fr,de', '=~', array('language' => 'jp,en')));
        $this->assertFalse(MAX_checkClient_Language('en,pl,fr,de', '=~', array('language' => 'jp,en-us')));
        $this->assertTrue(MAX_checkClient_Language('en', '=~', array('language' => 'jp,en')));
        
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-us,en,pl';
        $this->assertFalse(MAX_checkClient_Language('af', '=~'));
        $this->assertTrue(MAX_checkClient_Language('af,pl', '=~'));
    }
}

?>
