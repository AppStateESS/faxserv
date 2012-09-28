<?php

/**
 * update.php - FaxMaster update script
 *
 */

function faxmaster_update(&$content, $currentVersion)
{
    switch($currentVersion){
        case version_compare($currentVersion, '0.1.0', '<'):
            $db = new PHPWS_DB();
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/faxmaster/boost/update-0.1.1.sql');
            if(PEAR::isError($result)){
                return $result;
            }
        case version_compare($currentVersion, '0.1.2', '<'):
            $db = new PHPWS_DB();
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/faxmaster/boost/update-0.1.2.sql');
            if(PEAR::isError($result)){
                return $result;
            }
        case version_compare($currentVersion, '0.1.3', '<'):
            PHPWS_Core::initModClass('users', 'Permission.php');
            Users_Permission::registerPermissions('faxmaster', $content);

        case version_compare($currentVersion, '0.1.5', '<'):
            PHPWS_Settings::set('faxmaster', 'fax_path', '/var/fax/');
            PHPWS_Settings::save('faxmaster');

        case version_compare($currentVersion, '0.1.6', '<'):
            $content[] = '<pre>';
            slcUpdateFiles(array(   'class/FaxPager.php',
                                    'class/Faxmaster.php',
                                    'templates/faxList.tpl',
                                    'templates/style.css',
                                    'templates/statistics.tpl'), $content);
            $content[] = '0.1.6 Changes
---------------
+ Added a statistics page to view monthly fax stats.
+ Added CSV export to the new statistics page.</pre>';
    }
    return true;
}

function slcUpdateFiles($files, &$content) {
    if (PHPWS_Boost::updateFiles($files, 'checkin')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
}

?>
