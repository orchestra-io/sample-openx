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
$Id: postscript_oxMarket_upgrade_1.0.0-RC3.php 44038 2009-09-29 13:26:13Z lukasz.wikierski $
*/

$className = 'oxMarket_UpgradePostscript_1_0_0_RC3';

/**
 * Updates the url conf setting
 *
 * @package    Plugin
 * @subpackage openXMarket
 */
class oxMarket_UpgradePostscript_1_0_0_RC3
{
    
    
    function execute($aParams)
    {
        $oManager   = & new OX_Plugin_ComponentGroupManager();
        $aComponentSettings    = $oManager->getComponentGroupSettings('oxMarket', false);
        foreach ($aComponentSettings as $setting) {
            if ($setting['key'] == 'marketCaptchaUrl') {
                $value = $setting['value'];
                break; 
            }
        } 
        
        $oSettings  = new OA_Admin_Settings();
        $oSettings->settingChange('oxMarket','marketCaptchaUrl',$value);
        if (!$oSettings->writeConfigChange()) {
            OA::debug('openXMarket plugin: Couldn\'t update marketCaptchaUrl');
        }
        return true;
    }
}

?>