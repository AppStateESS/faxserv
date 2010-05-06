<?php

/**
 * Faxmaster
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class Faxmaster {

    public function __construct(){
        try{
            $this->handleRequest();
        }catch(PermissionException $e){
            PHPWS_Core::initModClass('faxmaster', 'FaxmasterNotificationView.php');
            NQ::simple('faxmaster', FAX_NOTIFICATION_ERROR, $e->getMessage());
            $nv = new FaxmasterNotificationView();
            $nv->popNotifications();
            Layout::add($nv->show());
        }
    }

    /**
     * Controller - handles all requests and determines which control method to call
     */
    private function handleRequest(){
        if(!isset($_REQUEST['op'])){
            $this->showFaxes();
            return;
        }

        switch($_REQUEST['op']){
            case 'new_fax':
                $this->newFax();
                break;
            case 'download_fax':
                $this->downloadFax();
                break;
            case 'mark_fax_printed':
                $this->markFaxPrinted();
                break;
            case 'set_name_id':
                $this->setNameId();
                break;
            case 'mark_fax_hidden':
                $this->markFaxHidden();
                break;
            default:
                $this->showFaxes();
        }
    }

    /**
     * Handles new, incoming faxes. Does some error checking, creates
     * a new Fax object, and saves it to the database
     */
    private function newFax(){
        PHPWS_Core::initModClass('faxmaster', 'Fax.php');

        $fileName       = $_REQUEST['fileName'];
        $senderPhone    = $_REQUEST['senderPhone'];

        # Make sure a fax with the given file name doesn't already exist
        if(Fax::getFaxInfoByFileName($fileName) != NULL){
            # Either the fax already exists, or there was an error checking for it
            # TODO
            exit;
        }

        # Make sure the file actually exists (i.e. it was copied to the FAX_PATH successfully)
        if(!file_exists(FAX_PATH . $fileName)){
            # TODO, the file doesn't exist
            exit;
        }

        $fax = new Fax(0, $senderPhone, $fileName);
        $fax->setNumPages(Fax::countPages($fax));
        $fax->setHidden(0);
        $result = $fax->save();

        # TODO pass the result back to the calling host, and have that host handle any errors
        exit;
    }

    /**
     * A function that shows a db pager of all faxes
     */
    private function showFaxes()
    {
        PHPWS_Core::initModClass('faxmaster', 'FaxPager.php');
        $pager = new FaxPager();
        $pager->show();
    }

    /**
     * Handles the request to download a particlar fax
     */
    private function downloadFax()
    {
        PHPWS_Core::initModClass('faxmaster', 'Fax.php');
        PHPWS_Core::initModClass('faxmaster', 'FaxDownload.php');

        $fax = new Fax($_REQUEST['id']);

        if(!Current_User::allow('faxmaster', 'download')){
            PHPWS_Core::initModClass('faxmaster', 'exception/PermissionException.php');
            throw new PermissionException('Permission denied');
        }

        //TODO make sure that fax actually exists, show an error otherwise

        // Mark the fax as being read
        $fax->markAsRead();

        // Create the necessary view, telling it which fax to show
        $view = new FaxDownload($fax);
        $view->show();
    }

    private function markFaxPrinted()
    {
        PHPWS_Core::initModClass('faxmaster', 'Fax.php');
        PHPWS_Core::initModClass('faxmaster', 'FaxPager.php');

        if(!Current_User::allow('faxmaster', 'markPrinted')){
            PHPWS_Core::initModClass('faxmaster', 'exception/PermissionException.php');
            throw new PermissionException('Permission denied');
        }


        $fax = new Fax($_REQUEST['id']);

        $fax->markAsPrinted();

        $view = new FaxPager();
        $view->show();
    }

    private function setNameId()
    {
        PHPWS_Core::initModClass('faxmaster', 'Fax.php');

        if(!Current_User::allow('faxmaster', 'editSender')){
            PHPWS_Core::initModClass('faxmaster', 'exception/PermissionException.php');
            throw new PermissionException('Permission denied');
        }


        $fax = new Fax($_REQUEST['id']);

        if(isset($_REQUEST['firstName'])){
            $fax->setFirstName($_REQUEST['firstName']);
        }

        if(isset($_REQUEST['lastName'])){
            $fax->setLastName($_REQUEST['lastName']);
        }

        if(isset($_REQUEST['bannerId'])){
            $fax->setBannerId($_REQUEST['bannerId']);
        }

        echo $fax->save();
        exit;
    }

    private function markFaxHidden() {
        PHPWS_Core::initModClass('faxmaster', 'Fax.php');
        PHPWS_Core::initModClass('faxmaster', 'FaxPager.php');

        if(!Current_User::allow('faxmaster', 'markHidden')){
            PHPWS_Core::initModClass('faxmaster', 'exception/PermissionException.php');
            throw new PermissionException('Permission denied');
        }

        $fax = new Fax($_REQUEST['id']);

        $fax->markAsHidden();

        $view = new FaxPager();
        $view->show();
    }
}

?>
