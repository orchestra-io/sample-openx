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
$Id: kontera.class.php 33995 2009-03-18 23:04:15Z chris.nutting $
*/

/**
 * @package    OpenXPlugin
 * @subpackage 3rdPartyServers
 * @author     Radek Maciaszek <radek@m3.net>
 *
 */

require_once LIB_PATH . '/Extension/3rdPartyServers/3rdPartyServers.php';

/**
 *
 * 3rdPartyServer plugin. Allow for generating different banner html cache
 *
 * @static
 */
class Plugins_3rdPartyServers_ox3rdPartyServers_kontera extends Plugins_3rdPartyServers
{

    /**
     * Return the name of plugin
     *
     * @return string
     */
    function getName()
    {
        return $this->translate('Rich Media - Kontera');
    }

    /**
     * Return plugin cache
     *
     * @return string
     */
    function getBannerCache($buffer, &$noScript)
    {
        if (!stristr($buffer, 'kontera.com')) {
            // This does not appear to be a kontera tag, leave unchanged
            return $buffer;
        }

        $search = "#var dc_adprod\s*=\s*[\'\\\"]([a-zA-Z]+)[\'\\\"];#";
        $replace = "var dc_adprod='$1';\nvar dc_redirect3PartyUrl='{clickurl}';\n";

        $buffer = preg_replace ($search, $replace, $buffer);

        return $buffer;
    }

}

?>
