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
        $basePath = PHPWS_Settings::get('faxmaster', 'fax_path');
        if(is_null($basePath) || !isset($basePath)){
            throw new InvalidArgumentException('Please set fax_path setting.');
        }

        header('Content-Disposition: attachment; filename="' . $this->fax->getFileName() . '"');
        readfile($basePath . $this->fax->getFileName());

        exit;
    }
}

?>
