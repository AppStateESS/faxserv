<?php

// Include the DBPager class
PHPWS_Core::initCoreClass('DBPager.php');

class FaxPager {

    private $pager = NULL;

    public function __construct(){
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('faxmaster', 'Fax.php');

        $this->pager = new DBPager('faxmaster_fax', 'Fax');

        $this->pager->setModule('faxmaster');
        $this->pager->setTemplate('faxPager.tpl');
        $this->pager->setLink('index.php?module=faxmaster');

        $this->pager->addToggle('class="toggle1"');
        $this->pager->addToggle('class="toggle2"');
        $this->pager->addRowTags('pagerRowTags');
    }

    public function show(){
        Layout::add($this->pager->get());
    }
}

?>
