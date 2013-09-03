<?php

// Include the DBPager class
PHPWS_Core::initCoreClass('DBPager.php');

class ActionLogPager {

    private $pager = NULL;

    public function __construct($type='default'){
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('faxmaster', 'ActionLog.php');

        $this->pager = new DBPager('faxmaster_action_log', 'ActionLog');
        $this->pager->setModule('faxmaster');
        $this->pager->setLink('index.php?module=faxmaster');

        // Zebra stripe the fax list
        $this->pager->addToggle('class="bgcolor1"');
        $this->pager->addToggle('class="bgcolor2"');

        // By default, sort the faxes in reverse chronological order
        $this->pager->setOrder('timestamp', 'DESC', true);

	$this->pager->setTemplate('faxPager.tpl');
	$this->pager->setEmptyMessage('No actions found.');
	$this->pager->addRowTags('pagerRowTags');
	$this->pager->setSearch('username');
    }

    public function show($archive=FALSE) {
        javascript('/jquery/');
        javascript('/jquery_ui/');

        // Link to stats, archive, and settings pages
        $viewStats      = "<a href='index.php?module=faxmaster&op=show_stats'><button>View Statistics</button></a>";
        $viewArchive    = "<a href='index.php?module=faxmaster&op=show_archive'><button>View Archive</button></a>";
        $settings       = "<a href='index.php?module=faxmaster&op=settings'><button>Settings</button></a>";
	$actionLog       = "<a href='index.php?module=faxmaster&op=showActionLog'><button>Action Log</button></a>";

        $tpl = array();

        $tpl['PAGER'] = $this->pager->get();

        // Don't show the topbar when viewing the archive list
	$topBar = array();
	$topBar['STATISTICS'] = $viewStats;     // view stats button
	
	// Only show 'View Archive' button if user has permission to view the archive
	if (Current_User::allow('faxmaster', 'viewArchive'))
	  $topBar['ARCHIVE'] = $viewArchive;  // view archive button
	
	// Only show 'Settings' button if user has proper permissions
	if (Current_User::allow('faxmaster', 'settings'))
	  $topBar['SETTINGS'] = $settings;    // settings button

	$topBar['ACTIONLOG'] = $actionLog;
	
	$tpl['topBar'][] = $topBar;
	
        Layout::add(PHPWS_Template::process($tpl, 'faxmaster', 'actionLogList.tpl'));
    }
}

?>
