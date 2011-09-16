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
$Id: postscript_oxMarket_upgrade_1.2.0-RC1.php 46828 2009-11-25 08:59:35Z matthieu.aubry $
*/

$className = 'oxMarket_UpgradePostscript_1_2_0_RC1';

/**
 * Mark all websites as not synchronized, to update website names during next maintenanace.
 *
 * @package    Plugin
 * @subpackage openXMarket
 */
class oxMarket_UpgradePostscript_1_2_0_RC1
{
    var $oUpgrade;
    
    function execute($aParams)
    {
        $this->oUpgrade = & $aParams[0];
        
        $oDbh = &OA_DB::singleton();
        $prefix = $GLOBALS['_MAX']['CONF']['table']['prefix'];
        $prefTable = $oDbh->quoteIdentifier($prefix.'ext_market_website_pref', true);

        $query = "UPDATE ".$prefTable."
                  SET is_url_synchronized = 'f'";
        $ret = $oDbh->query($query);
    
        if (PEAR::isError($ret))
        {
            $this->logError($ret->getUserInfo());
            $this->logOnly('Cannot mark websites as not synchronized, to allow send proper website names.');
        }
        
        return true;
    }
    
    function logOnly($msg)
    {
        $this->oUpgrade->oLogger->logOnly($msg);
    }
    
    function logError($msg)
    {
        $this->oUpgrade->oLogger->logError($msg);
    }

}

?>