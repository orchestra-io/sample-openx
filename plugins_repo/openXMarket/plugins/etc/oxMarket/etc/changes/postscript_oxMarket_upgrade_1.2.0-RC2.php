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
$Id: postscript_oxMarket_upgrade_1.2.0-RC2.php 46917 2009-11-27 09:36:55Z lukasz.wikierski $
*/

$className = 'oxMarket_UpgradePostscript_1_2_0_RC2';

/**
 * Mark all websites as not synchronized, to update website names during next maintenanace.
 *
 * @package    Plugin
 * @subpackage openXMarket
 */
class oxMarket_UpgradePostscript_1_2_0_RC2
{
    var $oUpgrade;
    
    function execute($aParams)
    {
        $this->oUpgrade = & $aParams[0];
    
        $oManager   = & new OX_Plugin_ComponentGroupManager();
        $aComponentSettings    = $oManager->getComponentGroupSettings('oxMarket', false);
        foreach ($aComponentSettings as $setting) {
            if ($setting['key'] == 'marketPublicApiUrl') {
                $value = $setting['value'];
                break; 
            }
        } 
        
        $oSettings  = new OA_Admin_Settings();
        $oSettings->settingChange('oxMarket','marketPublicApiUrl',$value);
        if (!$oSettings->writeConfigChange()) {
            OA::debug('openXMarket plugin: Couldn\'t update marketPublicApiUrl, value should be '.$value);
        }
        
        $this->optOutExclusiveCampaigns();
        
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

    /**
     * Opt out Contract Exclusive and Contract with end date set campaigns
     *
     * @return bool
     */
    function optOutExclusiveCampaigns()
    {
        $oDbh = &OA_DB::singleton();
        $aConf = $GLOBALS['_MAX']['CONF'];
        $prefix = $aConf['table']['prefix'];
        $campaignPrefTable = $oDbh->quoteIdentifier($prefix.'ext_market_campaign_pref', true);
        $campaignsTable = $oDbh->quoteIdentifier($prefix.$aConf['table']['campaigns'], true);

        $query = "UPDATE ".$campaignPrefTable."
                  SET is_enabled = 0 
                  WHERE campaignid IN (
                    SELECT campaignid FROM ".$campaignsTable."
                    WHERE priority = -1 OR (priority>0 AND expire_time is NOT NULL))";
        $ret = $oDbh->query($query);

        if (PEAR::isError($ret))
        {
            $this->logError($ret->getUserInfo());
            $this->logOnly('Cannot opt out Contract Exclusive and Contract with end date set campaigns.');
        }
        
        return true;
    }
}

