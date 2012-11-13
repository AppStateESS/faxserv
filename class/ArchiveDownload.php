<?php

/**
 * ArchiveDownload class - The 'view' component for downloading a fax
 */

class ArchiveDownload {
    
    private $tar = NULL; // The archive object we're going to viewing

    public function __construct($fileName)
    {
        $this->tar = $fileName;
    }

    public function show()
    {
        $basePath = PHPWS_Settings::get('faxmaster', 'archive_path');
        if(is_null($basePath) || !isset($basePath)){
            throw new InvalidArgumentException('Please set archive_path setting.');
        }

        header('Content-Type: application/x-gtar');
        header('Content-Disposition: attachment; filename="' . $this->tar . '"');
        header('Content-Length: ' . filesize($basePath . $this->tar));
        
        readfile($basePath . $this->tar);
        exit;
    }
}

?>
