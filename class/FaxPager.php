<?php

// Include the DBPager class
PHPWS_Core::initCoreClass('DBPager.php');

class FaxPager {

    private $pager = NULL;

    public function __construct(){
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('faxmaster', 'Fax.php');

        $this->pager = new DBPager('faxmaster_fax', 'Fax');

        // By default, sort the faxes in reverse chronological order
        $this->pager->setOrder('dateReceived', 'DESC', true);

        $this->pager->setModule('faxmaster');
        $this->pager->setTemplate('faxPager.tpl');
        $this->pager->setLink('index.php?module=faxmaster');

        $this->pager->setEmptyMessage('No faxes found.');

        $this->pager->addToggle('class="bgcolor1"');
        $this->pager->addToggle('class="bgcolor2"');
        $this->pager->addRowTags('pagerRowTags');

        $this->pager->setSearch('bannerId', 'firstName', 'lastName');
    }

    public function show(){
        javascript('/jquery/');
        javascript('/jquery_ui/');
        Layout::add($this->pager->get());
    }
}

?>
