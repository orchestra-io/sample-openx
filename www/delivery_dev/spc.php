<?php

/*
+---------------------------------------------------------------------------+
| OpenX  v${RELEASE_MAJOR_MINOR}                                                              |
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
$Id: spc.php 49127 2010-02-18 01:55:43Z chris.nutting $
*/

// Require the initialisation file
require_once '../../init-delivery.php';

// Required files
require_once MAX_PATH . '/lib/max/Delivery/adSelect.php';
require_once MAX_PATH . '/lib/max/Delivery/flash.php';
require_once MAX_PATH . '/lib/max/Delivery/javascript.php';
//require_once MAX_PATH . '/lib/max/Delivery/marketplace.php';

MAX_commonSetNoCacheHeaders();

/*-------------------------------------------------------*/
/* Register input variables                              */
/*-------------------------------------------------------*/

MAX_commonRegisterGlobalsArray(array('zones' ,'source', 'block', 'blockcampaign', 'exclude', 'mmm_fo', 'q', 'nz'));

/*-------------------------------------------------------*/
/* Main code                                             */
/*-------------------------------------------------------*/

// Derive the source parameter
$source = MAX_commonDeriveSource($source);

$spc_output = 'var ' . $conf['var']['prefix'] . 'output = new Array(); ' . "\n";

if(!empty($zones)) {
    $zones = explode('|', $zones);
    foreach ($zones as $thisZone) {
        if (empty($thisZone)) continue;
        // nz is set when "named zones" are being used, this allows a zone to be selected more than once
        if (!empty($nz)) {
            list($zonename,$thisZoneid) = explode('=', $thisZone);
            $varname = $zonename;
        } else {
            $thisZoneid = $varname = $thisZone;
        }
    
        // Clear deiveryData between iterations
        unset($GLOBALS['_MAX']['deliveryData']);
        
        $what = 'zone:'.$thisZoneid;
    
        //OX_Delivery_logMessage('$what='.$what, 7);
        //OX_Delivery_logMessage('$context='.print_r($context,true), 7);
    
        // Get the banner
        $output = MAX_adSelect($what, $clientid, $target, $source, $withtext, $charset, $context, true, $ct0, $GLOBALS['loc'], $GLOBALS['referer']);
    
        //OX_Delivery_logMessage('$block='.@$block, 7);
        //OX_Delivery_logMessage(print_r($output, true), 7);
        //OX_Delivery_logMessage('output bannerid='.(empty($output['bannerid']) ? ' NO BANNERID' : $output['bannerid']), 7);
    
        // Store the html2js'd output for this ad
        $spc_output .= MAX_javascriptToHTML($output['html'], $conf['var']['prefix'] . "output['{$varname}']", false, false) . "\n";
    
        // Block this banner for next invocation
        if (!empty($block) && !empty($output['bannerid'])) {
            $output['context'][] = array('!=' => 'bannerid:' . $output['bannerid']);
        }
        // Block this campaign for next invocation
        if (!empty($blockcampaign) && !empty($output['campaignid'])) {
            $output['context'][] = array('!=' => 'campaignid:' . $output['campaignid']);
        }
        // Pass the context array back to the next call, have to iterate over elements to prevent duplication
        if (!empty($output['context'])) {
            foreach ($output['context'] as $id => $contextArray) {
                if (!in_array($contextArray, $context)) {
                    $context[] = $contextArray;
                }
            }
        }
    }
}
MAX_cookieFlush();

// Setup the banners for this page
MAX_commonSendContentTypeHeader("application/x-javascript", $charset);
header("Content-Size: ".strlen($spc_output));

echo $spc_output;

?>
