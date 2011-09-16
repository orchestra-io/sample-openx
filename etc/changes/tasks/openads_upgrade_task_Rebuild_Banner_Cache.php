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
$Id: openads_upgrade_task_Rebuild_Banner_Cache.php 44885 2009-10-16 14:18:40Z bernard.lange $
*/

require_once MAX_PATH . '/www/admin/lib-banner-cache.inc.php';
//$oMessages initialized by runner OA_Upgrade::runPostUpgradeTask

$oMessages->logInfo('Starting Banner Cache Recompilation');
$upgradeTaskResult  = processBanners(true);
if (PEAR::isError($upgradeTaskResult)) {
    $oMessages->logError($upgradeTaskResult->getCode().': '.$upgradeTaskResult->getMessage());
}
$upgradeTaskError[] = ' Banner Cache Recompilation: '.($upgradeTaskResult ? 'Complete' : 'Failed');



?>