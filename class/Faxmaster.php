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
            case 'show_stats':
                $this->showStats();
                break;
            case 'csv':
                $this->exportCSV();
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
        $basePath = PHPWS_Settings::get('faxmaster', 'fax_path');
        if(is_null($basePath) || !isset($basePath)){
            throw new InvalidArgumentException('Please set fax_path setting.');
        }

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

        echo $fax->getId();
        exit;
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

        if(!Current_User::allow('faxmaster', 'hide')){
            PHPWS_Core::initModClass('faxmaster', 'exception/PermissionException.php');
            throw new PermissionException('Permission denied');
        }

        $fax = new Fax($_REQUEST['id']);

        $fax->markAsHidden();
        
        echo $fax->getId();
        exit;
    }

    /**
     * Queries the database for monthly statistics about all faxes received,
     * then outputs those stats into a table. Lists the results in descending
     * order by date so that the most recent month's stats are at the top.
     */
    private function showStats() {
        $query = "  select MONTH(FROM_UNIXTIME(dateReceived)) as `Month`,
                    YEAR(FROM_UNIXTIME(dateReceived)) as `Year`, 
                    count(*) as `NumFaxes`,
                    sum(numPages) as `PageCount`
                    from faxmaster_fax 
                    where hidden = 0 
                    group by month, year asc 
                    ORDER BY `Year` DESC, `Month` DESC;";

        $db = new PHPWS_DB('faxmaster_fax');
        $results = $db->select(null, $query);

        $tpl = array();
        if (count($results) == 0) {
            $tpl['empty_table']['EMPTY_MESSAGE'] = "No faxes found.";
        } else {
            $tpl['COUNT'] = count($results);
            $tpl['CSV_BUTTON'] = "<a href='index.php?module=faxmaster&op=csv'><button>Export as CSV</button></a>";
            $toggle = 0;
            foreach ($results as $row) {
                $tpl['repeat_row'][] = array('month'=>$row['Month'],
                                            'year'=>$row['Year'],
                                            'numFaxes'=>$row['NumFaxes'],
                                            'numPages'=>$row['PageCount'],
                                            'TOGGLE'=>$toggle ? 'class="bgcolor1"' : 'class="bgcolor2"');
                $toggle = !$toggle;
            }
        }
        Layout::add(PHPWS_Template::process($tpl, 'faxmaster', 'statistics.tpl'));        
    }

    /**
     * Queries the database for monthly statistics about all faxes received, 
     * then outputs those stats as a CSV file. Lists the results in ascending
     * order by date.
     */
    private function exportCSV() {
        $csv;
        $file = "FaxMasterExport" . date('m-d-Y') . ".csv";  // output filename

        $db = new PHPWS_DB('faxmaster_fax');
        $query = "  select MONTH(FROM_UNIXTIME(dateReceived)) as `Month`,
                    YEAR(FROM_UNIXTIME(dateReceived)) as `Year`, 
                    count(*) as `NumFaxes`,
                    sum(numPages) as `PageCount`
                    from faxmaster_fax 
                    where hidden = 0 
                    group by month, year asc 
                    ORDER BY `Year` ASC, `Month` ASC;";
        $results = $db->select(null, $query);

        if (count($results) == 0) {
            Layout::add("No records found in database");
        } else {
            $cols = array('Month', 'Year', 'Faxes', 'Pages');
            $csv = $this->sputcsv($cols);

            foreach ($results as $row) {
                $reportRow = array($row['Month'], $row['Year'], $row['NumFaxes'], $row['PageCount']);
                $csv .= $this->sputcsv($reportRow);
            }
 
            // Force the browser to open a 'save as' dialogue
            header('Content-Type: text/csv');
            header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
            header('Pragma: public');
            header('Expires: Mon, 17 Sep 2012 05:00:00 GMT'); // Date in the past
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
            header('Content-Length: '.strlen($csv));
            header('Content-Disposition: attachment; filename="' . $file . '";');

            echo $csv;
            exit();
        }
    }

    /**
     * Handles writing an array to a comma-separated string
     * 
     * @param Array $row Array of values to write
     * @param char $delimiter
     * @param char $enclosure
     * @param char $eol
     */
    private static function sputcsv(Array $row, $delimiter = ',', $enclosure = '"', $eol = "\n")
    {
        static $fp = false;
        if ($fp === false)
        {
            $fp = fopen('php://temp', 'r+'); // see http://php.net/manual/en/wrappers.php.php - yes there are 2 '.php's on the end.
            // NB: anything you read/write to/from 'php://temp' is specific to this filehandle
        }
        else
        {
            rewind($fp);
        }

        if (fputcsv($fp, $row, $delimiter, $enclosure) === false)
        {
            return false;
        }

        rewind($fp);
        $csv = fgets($fp);

        if ($eol != PHP_EOL)
        {
            $csv = substr($csv, 0, (0 - strlen(PHP_EOL))) . $eol;
        }

        return $csv;
    }
}

?>
