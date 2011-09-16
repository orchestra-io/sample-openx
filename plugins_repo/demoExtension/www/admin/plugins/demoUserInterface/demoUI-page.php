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
$Id: demoUI-page.php 30820 2009-01-13 19:02:17Z andrew.hill $
*/

require_once 'demoUI-common.php';

if (isset($_REQUEST['action']) && in_array($_REQUEST['action'],array('1','2','3','4','4-1', '4-2')))
{
    $i = $_REQUEST['action'];

    $message = $GLOBALS['_MAX']['CONF']['demoUserInterface']['message'.$i];
    $menu = 'demo-menu-'.$i;

    switch ($i)
    {
        case '4':
            OA_Permission::enforceAccount(OA_ACCOUNT_ADMIN);
            break;
        case '3':
            OA_Permission::enforceAccount(OA_ACCOUNT_MANAGER);
            break;
        case '2':
            OA_Permission::enforceAccount(OA_ACCOUNT_MANAGER, OA_ACCOUNT_ADMIN);
            break;
        case '4-1':
            OA_Permission::enforceAccount(OA_ACCOUNT_ADMIN);
            $message = 'Dynamic submenu 4-1';
            $menu = 'demo-menu-4';  // PageHeader function needs to know the *parent* menu
            setCurrentLeftMenuSubItem('demo-menu-4-1');
            break;
        case '4-2':
            OA_Permission::enforceAccount(OA_ACCOUNT_ADMIN);
            $message = 'Dynamic submenu 4-2';
            $menu = 'demo-menu-4'; // PageHeader function needs to know the *parent* menu
            setCurrentLeftMenuSubItem('demo-menu-4-2');
            break;
    }

    $colour  = $GLOBALS['_MAX']['PREF']['demoUserInterface_demopref_'.OA_Permission::getAccountType(true)];
    //$image   = 'demoUI'.$i.'.jpg';
    $message = $message;

    addLeftMenuSubItem('demo-menu-4-1', 'demo submenu 4-1', 'plugins/demoUserInterface/demoUI-page.php?action=4-1');
    addLeftMenuSubItem('demo-menu-4-2', 'demo submenu 4-2', 'plugins/demoUserInterface/demoUI-page.php?action=4-2');


    phpAds_PageHeader($menu,'','../../');

    $oTpl = new OA_Plugin_Template('demoUI.html','demoUserInterface');
    //$oTpl->assign('image',$image);
    $oTpl->assign('message',$message);
    $oTpl->assign('colour',$colour);
    $oTpl->display();

    phpAds_PageFooter();
}
else
{
    require_once LIB_PATH . '/Admin/Redirect.php';
    OX_Admin_Redirect::redirect('plugins/demoUserInterface/demoUI-index.php');
}


?>