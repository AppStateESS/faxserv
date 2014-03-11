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
        } catch (InstallException $e) { // catches path exceptions
            PHPWS_Core::initModClass('faxmaster', 'FaxmasterNotificationView.php');
            NQ::simple('faxmaster', FAX_NOTIFICATION_ERROR, $e->getMessage());
            
            $settings = "<a href='index.php?module=faxmaster&op=settings'><button>Fix in Settings</button></a>";

            if (!is_dir(PHPWS_Settings::get('faxmaster', 'fax_path'))) {
                NQ::simple('faxmaster', FAX_NOTIFICATION_ERROR, "The fax directory does not exist: <strong>" . PHPWS_Settings::get('faxmaster', 'fax_path') . "</strong> " . $settings);
            }
            
            if (!is_dir(PHPWS_Settings::get('faxmaster', 'archive_path'))) {
                NQ::simple('faxmaster', FAX_NOTIFICATION_ERROR, "The archive directory does not exist: <strong>" . PHPWS_Settings::get('faxmaster', 'archive_path') . "</strong> " . $settings);
            }
            
            $nv = new FaxmasterNotificationView();
            $nv->popNotifications();
            Layout::add($nv->show());
        }
    }

    /**
     * Controller - handles all requests and determines which control method to call
     */
    private function handleRequest() {
        // make sure the fax path and archive path exist
        if (!is_dir(PHPWS_Settings::get('faxmaster', 'fax_path')) || !is_dir(PHPWS_Settings::get('faxmaster', 'archive_path'))) {
            if (isset($_REQUEST['op']) && $_REQUEST['op'] != 'settings') {
                PHPWS_Core::initModClass('faxmaster', 'exception/InstallException.php');
                throw new InstallException('fax_path and/or archive_path directories do not exist!');
            }
        }
        
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
            case 'download_archive':
                $this->downloadArchive();
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
            case 'show_archive':
                $this->showArchive();
                break;
            case 'show_stats':
                $this->showStats();
                break;
            case 'csv':
                $this->exportCSV();
                break;
            case 'archive':
                $this->archiveFaxes();
                break;
            case 'settings':
                $this->changeSettings();
                break;
	        case 'showActionLog':
	            $this->showActionLog();
	            break;
            case 'go_home': // 303 redirect to main module page
                echo(header("HTTP/1.1 303 See Other"));
                echo(header("Location: index.php?module=faxmaster"));
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

        if (!file_exists($basePath . $fileName)) {
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
        $this->addNavLinks();
    }

    /**
     * A function that shows a db pager of all archived faxes
     */
    private function showArchive() {
        PHPWS_Core::initModClass('faxmaster', 'FaxPager.php');

        if (!Current_User::allow('faxmaster', 'viewArchive')) {
            PHPWS_Core::initModClass('faxmaster', 'exception/PermissionException.php');
            throw new PermissionException('Permission denied');
        }
        
        $pager = new FaxPager('archived');
        $pager->show(true);
        $this->addNavLinks();
    }

    /**
      * shows a log of all actions taken and by what user
      */
    private function showActionLog()
    {
        PHPWS_Core::initModClass('faxmaster', 'ActionLogPager.php');
	    $pager = new ActionLogPager();
	    $pager->show(true);
        $this->addNavLinks();
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

    /**
     * Handles the request to download an archive file
     */
    private function downloadArchive() {
        PHPWS_Core::initModClass('faxmaster', 'ArchiveDownload.php');

        if (!Current_User::allow('faxmaster', 'downloadArchive')) {
            PHPWS_Core::initModClass('faxmaster', 'exception/PermissionException.php');
            throw new PermissionException('Permission denied');
        }

        if (!isset($_REQUEST['fileName'])) {
            throw new InvalidArgumentException('fileName not set');
        }

        $view = new ArchiveDownload($_REQUEST['fileName']);
        $view->show();
    }


    //add to action log
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

    //add to action log
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

        //get username, timestamp, activity, and fax id
        $name = Current_User::getUsername();
        $timestamp = time();
        $activity = "changed name or banner id";
        $faxName = $fax->getFileName();
        PHPWS_Core::initModClass('faxmaster','ActionLog.php');
        $action = new ActionLog(0,$faxName,$name,$activity,$timestamp);

        exit;
    }


    //add to action log
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
     * Allows users with sufficient privileges to change the settings associated
     * with the faxmaster module.
     */
    private function changeSettings() {
        // Check user's permissions
        if (!Current_User::allow('faxmaster', 'settings')) {
            PHPWS_Core::initModClass('faxmaster', 'exception/PermissionException.php');
            throw new PermissionException('Permission denied');
        }
        
        $content = array();
        $form = new PHPWS_Form('faxmaster_settings');
        
        // If $_REQUEST data has been given, set the paths
        if (isset($_REQUEST['fax_path']) 
            && !is_null($_REQUEST['fax_path']) 
            && isset($_REQUEST['archive_path']) 
            && !is_null($_REQUEST['archive_path']))
        {
            clearstatcache(true); // is_readable and is_writable cache results, so you need to clear the cache
            $faxRead        = is_readable($_REQUEST['fax_path']);
            $faxWrite       = is_writable($_REQUEST['fax_path']);
            $archiveRead    = is_readable($_REQUEST['archive_path']);
            $archiveWrite   = is_writable($_REQUEST['archive_path']);

            if (!$faxRead || !$faxWrite || !$archiveRead || !$archiveWrite) {
                // Show warnings for invalid paths
                PHPWS_Core::initModClass('faxmaster', 'FaxmasterNotificationView.php');
                if (!$faxRead) {
                    NQ::simple('faxmaster', FAX_NOTIFICATION_ERROR,
                                "The fax directory you specified is not readable or does not exist.");
                }
                if (!$faxWrite) {
                    NQ::simple('faxmaster', FAX_NOTIFICATION_ERROR,
                                "The fax directory you specified is not writable or does not exist.");
                }
                if (!$archiveRead) {
                    NQ::simple('faxmaster', FAX_NOTIFICATION_ERROR,
                                "The archive directory you specified is not readable or does not exist.");
                }
                if (!$archiveWrite) {
                    NQ::simple('faxmaster', FAX_NOTIFICATION_ERROR,
                                "The archive directory you specified is not writable or does not exist.");
                }
                
                $nv = new FaxmasterNotificationView();
                $nv->popNotifications();
                Layout::add($nv->show());
                
                // Show supplied paths
                $form->setAction('index.php?module=faxmaster&op=settings');
                $form->addTplTag('FAX_PATH', $_REQUEST['fax_path']);
                $form->addTplTag('ARCHIVE_PATH', $_REQUEST['archive_path']);
                $form->addSubmit('Try Again');
                
                $tpl = $form->getTemplate();
                Layout::add(PHPWS_Template::process($tpl, 'faxmaster', 'settings.tpl')); 
            } else {
                // new paths were valid, so update settings
                PHPWS_Settings::set('faxmaster', 'fax_path', $_REQUEST['fax_path']);
                PHPWS_Settings::set('faxmaster', 'archive_path', $_REQUEST['archive_path']);
                PHPWS_Settings::save('faxmaster');

                // Show new paths
                $form->setAction('index.php?module=faxmaster&op=go_home');
                $form->addTplTag('SAVED', 'New Settings Saved!');
                $form->addTplTag('FAX_PATH', PHPWS_Settings::get('faxmaster', 'fax_path'));
                $form->addTplTag('ARCHIVE_PATH', PHPWS_Settings::get('faxmaster', 'archive_path'));
                $form->addSubmit('Return to Fax List');
                $tpl = $form->getTemplate();
                Layout::add(PHPWS_Template::process($tpl, 'faxmaster', 'settings.tpl')); 
            }
        } else {
            // Show initial form to change paths
            $form->setAction('index.php?module=faxmaster&op=settings');
            $form->addTplTag('WARNING','<strong>WARNING: </strong> Changing paths does not move files. Files must be moved manually.<br /><br \>');
            $form->addText('fax_path', PHPWS_Settings::get('faxmaster', 'fax_path'));
            $form->setSize('fax_path', 45);
            $form->addText('archive_path', PHPWS_Settings::get('faxmaster', 'archive_path'));
            $form->setSize('archive_path', 45);
            $form->addSubmit('Save Settings');
            $tpl = $form->getTemplate();
            Layout::add(PHPWS_Template::process($tpl, 'faxmaster', 'settings.tpl')); 
        }
        $this->addNavLinks();        
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
        $this->addNavLinks();
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
   
    /**
     * Handles the archiving of faxes. Modifies the filesystem by creating new archive files,
     * and remove existing fax files. Has many opportunities to throw exceptions.
     * 
     * Only accessible by URL of form:
     * .../index.php?module=faxmaster&op=archive&start_date=(UNIX TIMESTAMP)[&end_date=(UNIX TIMESTAMP)]
     */
    private function archiveFaxes() {
        PHPWS_Core::initModClass('faxmaster', 'Fax.php');
    
        // Get date range for archive. Use NOW for end date if one is not supplied.
        $startDate  = $_REQUEST['start_date'];
        $endDate    = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : time();

        // Check user's permissions
        if (!Current_User::allow('faxmaster', 'archive')) {
            PHPWS_Core::initModClass('faxmaster', 'exception/PermissionException.php');
            throw new PermissionException('Permission denied');
        }

        // SELECT id FROM faxmaster_fax WHERE dateReceived >= start_date AND dateReceived < end_date AND archived=0;
        $db = new PHPWS_DB('faxmaster_fax');
        $db->addColumn('id');
        $db->addWhere('archived', 0);                           // only grab unarchived files
        $db->addWhere('dateReceived', $startDate, '>=', 'AND'); // startDate is inclusive
        $db->addWhere('dateReceived', $endDate, '<', 'AND');    // endDate is exclusive
        $results = $db->select();

        // Make archive
        $path = PHPWS_Settings::get('faxmaster', 'archive_path');
        $archiveName = strftime('%m%d%Y', $startDate) . 'to' . strftime('%m%d%Y', $endDate) . '.tar';
        try {
            $archive = new PharData($path . $archiveName);
        } catch (UnexpectedValueException $e) {
            die('Could not open .tar file' . $e->getMessage());
        } catch (BadMethodCallException $e) {
            die('Bad method call' . $e->getMessage());
        }
    
        // Fill the archive
        foreach ($results as $result) {
            $fax = new Fax($result['id']);
            try {
                $archive->addFile($fax->getFullPath(), $fax->getFileName());
            } catch (PharException $e) {
                die($e->getMessage());
            }
        }

        // Compress the archive
        try {
            $archive = $archive->compress(Phar::GZ);
        } catch (BadMethodCallException $e) {
            die($e->getMessage());
        }

        // Remove .tar, leaving only the .tar.gz
        unlink($path . $archiveName);

        // Update each fax in the database, then remove it from the fax directory
        foreach ($results as $result) {
            $fax = new Fax($result['id']);
            $fax->setArchived(1, $archiveName . '.gz');
            $fax->save();
            unlink($fax->getFullPath());
        }
    }


    /**
     * This function adds links to the navigation bar at the top of the page.
     * This function assumes that there is a NAV_LINKS tag in the main theme template.
     */
    private function addNavLinks() { 
        // Link to the pages. One nav button for each link.
        $viewStats      = array("LINK"=>"index.php?module=faxmaster&op=show_stats", "TEXT"=>"View Statistics");
        $viewArchive    = array("LINK"=>"index.php?module=faxmaster&op=show_archive", "TEXT"=>"View Archive");
        $settings       = array("LINK"=>"index.php?module=faxmaster&op=settings", "TEXT"=>"Settings");
        $actionLog      = array("LINK"=>"index.php?module=faxmaster&op=showActionLog", "TEXT"=>"Action Log");

        // Fill the links array
        $links = array();
        $links['repeat_nav_links'][] = $viewStats;     // view stats button

        // Only show 'View Archive' button if user has permission to view the archive
        if (Current_User::allow('faxmaster', 'viewArchive')) {
            $links['repeat_nav_links'][] = $viewArchive;  // view archive button
        }

        // Only show 'Settings' button if user has proper permissions
        if (Current_User::allow('faxmaster', 'settings')) {
            $links['repeat_nav_links'][] = $settings;    // settings button
        }

        $links['repeat_nav_links'][] = $actionLog;

        $links['BRAND'] = 'Fax Server';
        $links['BRAND_LINK'] = 'index.php';

        if (Current_User::isDeity()) {
            $links['CONTROL_PANEL'] = PHPWS_Text::secureLink('Control Panel', 'controlpanel');
            $links['ADMIN_OPTIONS'] = ''; //dummy tag to show dropdown menu in template
        }

        $links['USER_FULL_NAME'] = Current_User::getDisplayName();

        $auth = Current_User::getAuthorization();
        $links['LOGOUT_URI'] = $auth->logout_link;

        // Plug the navlinks into the navbar
        $navLinks = PHPWS_Template::process($links, 'faxmaster', 'navLinks.tpl');
        Layout::plug($navLinks, 'NAV_LINKS');
    }
}
?>
