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
$Id: Browser.delivery.php 30820 2009-01-13 19:02:17Z andrew.hill $
*/

/**
 * @package    OpenXPlugin
 * @subpackage DeliveryLimitations
 * @author     Chris Nutting <chris.nutting@openx.org>
 * @author     Andrzej Swedrzynski <andrzej.swedrzynski@openx.org>
 */

require_once MAX_PATH . '/lib/max/Delivery/limitations.delivery.php';

/**
 * Check to see if this impression contains the valid browser.
 *
 * @param string $limitation The browser (or comma list of browsers) limitation
 * @param string $op The operator ('==', '!=', '=~', '!~')
 * @param array $aParams An array of additional parameters to be checked
 * @return boolean Whether this impression's browser passes this limitation's test.
 */
function MAX_checkClient_Browser($limitation, $op, $aParams = array())
{
    return MAX_limitationsMatchArray('browser', $limitation, $op, $aParams);
}

/**
 * A function to set the viewer's useragent information in the
 * $GLOBALS['_MAX']['CLIENT'] global variable, if the option to use
 * phpSniff to extract useragent information is set in the
 * configuration file.
 */
function MAX_remotehostSetClientInfo()
{
    if ($GLOBALS['_MAX']['CONF']['Client']['sniff'] && isset($_SERVER['HTTP_USER_AGENT'])) {
        if (!class_exists('phpSniff')) {
            include MAX_PATH.$GLOBALS['_MAX']['CONF']['pluginPaths']['extensions'].'deliveryLimitations/Client/lib/phpSniff/phpSniff.class.php';
        }
        $client = new phpSniff($_SERVER['HTTP_USER_AGENT']);
        $GLOBALS['_MAX']['CLIENT'] = $client->_browser_info;
    }
}


?>
