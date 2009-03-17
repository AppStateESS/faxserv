<?php

/**
 * Faxmaster
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class Faxmaster {

    public function __construct(){
       $this->handleRequest(); 
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
            default:
                $this->showFaxes();
        }
    }

    /**
     * Called by the controller when there is no 'op' field defined
     * in the HTTP request, or the value of the field is not recognized
     */
    private function noOp()
    {
        PHPWS_Core::home();
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

        $fax = new Fax();
        $fax->setSenderPhone($senderPhone);
        $fax->setFileName($fileName);
        $fax->markAsUnread();
        $fax->setDateReceived(time());

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

        $fax = new Fax($_REQUEST['id']);

        $fax->markAsPrinted();

        $view = new FaxPager();
        $view->show();
    }
}

?>
