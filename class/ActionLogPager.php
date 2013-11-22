<?php

// Include the DBPager class
PHPWS_Core::initCoreClass('DBPager.php');

class ActionLogPager {

    private $pager = NULL;

    public function __construct($type='default'){
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('faxmaster', 'ActionLog.php');

        $this->pager = new DBPager('faxmaster_action_log', 'RestoredActionLog');
        $this->pager->setModule('faxmaster');
        $this->pager->setLink('index.php?module=faxmaster');

        // Zebra stripe the fax list
        $this->pager->addToggle('class="bgcolor1"');
        $this->pager->addToggle('class="bgcolor2"');

        // By default, sort the faxes in reverse chronological order
        $this->pager->setOrder('timePerformed', 'DESC', true);

	$this->pager->setTemplate('actionLogList.tpl');
	$this->pager->setEmptyMessage('No actions found.');
	$this->pager->addRowTags('rowTags');
	$this->pager->setSearch('username');
    }

    public function show($archive=FALSE) {
        javascript('/jquery/');
        javascript('/jquery_ui/');

        $tpl = array();
        $tpl['PAGER'] = $this->pager->get();

        Layout::add(PHPWS_Template::process($tpl, 'faxmaster', 'actionLogPage.tpl'));
    }
}
?>
