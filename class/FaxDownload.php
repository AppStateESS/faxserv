<?php

/**
 * FaxDownload class - The 'view' component for downloading a fax
 */

class FaxDownload {
    
    private $fax = NULL; // The fax object we're going to viewing

    public function __construct($fax)
    {
        $this->fax = $fax;
    }

    public function show()
    {
        header('Content-Disposition: attachment; filename="' . $this->fax->getFileName() . '"');
        readfile(FAX_PATH . $this->fax->getFileName());

        exit;
    }
}

?>
