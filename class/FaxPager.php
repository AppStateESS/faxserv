<?php

// Include the DBPager class
PHPWS_Core::initCoreClass('DBPager.php');

class FaxPager {

    private $pager = NULL;

    public function __construct($type='default'){
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('faxmaster', 'Fax.php');

        $this->pager = new DBPager('faxmaster_fax', 'Fax');
        $this->pager->setModule('faxmaster');
        $this->pager->setLink('index.php?module=faxmaster');

        // Zebra stripe the fax list
        $this->pager->addToggle('class="bgcolor1"');
        $this->pager->addToggle('class="bgcolor2"');

        // Don't show hidden faxes
        $this->pager->addWhere('hidden', 0);

        // By default, sort the faxes in reverse chronological order
        $this->pager->setOrder('dateReceived', 'DESC', true);

        if ($type == 'archived') {
            $this->pager->setTemplate('archivePager.tpl');
            $this->pager->setEmptyMessage('No archived faxes found.');
            $this->pager->addRowTags('pagerRowTags', 'archived');
            $this->pager->addWhere('archived', 1);
            $this->pager->setSearch('bannerId', 'firstName', 'lastName', 'whichArchive');
        } else {
            $this->pager->setTemplate('faxPager.tpl');
            $this->pager->setEmptyMessage('No faxes found.');
            $this->pager->addRowTags('pagerRowTags');
            $this->pager->addWhere('archived', 0);
            $this->pager->setSearch('bannerId', 'firstName', 'lastName');
        }
    }

    public function show($archive=FALSE) {
        javascript('/jquery/');
        javascript('/jquery_ui/');

        // Link to stats, archive, and settings pages
        $viewStats      = '<a class="btn btn-default" href="index.php?module=faxmaster&op=show_stats">View Statistics</a>';
        $viewArchive    = '<a class="btn btn-default" href="index.php?module=faxmaster&op=show_archive">View Archive</a>';
        $settings       = '<a class="btn btn-default" href="index.php?module=faxmaster&op=settings">Settings</a>';
        $actionLog      = '<a class="btn btn-default" href="index.php?module=faxmaster&op=showActionLog">Action Log</a>';

        $tpl = array();


        $tpl['PAGER'] = $this->pager->get();

        // Don't show the topbar when viewing the archive list
        if (!$archive) {
            $topBar = array();
            $topBar['UNPRINTED_COUNT'] = Fax::getUnprintedCount();
            $topBar['STATISTICS'] = $viewStats;     // view stats button
            
            // Only show 'View Archive' button if user has permission to view the archive
            if (Current_User::allow('faxmaster', 'viewArchive'))
                $topBar['ARCHIVE'] = $viewArchive;  // view archive button

            // Only show 'Settings' button if user has proper permissions
            if (Current_User::allow('faxmaster', 'settings'))
                $topBar['SETTINGS'] = $settings;    // settings button

	    $topBar['ACTIONLOG'] = $actionLog;
            
            $tpl['topBar'][] = $topBar;
        }

        Layout::add(PHPWS_Template::process($tpl, 'faxmaster', 'faxList.tpl'));
    }
}

?>
