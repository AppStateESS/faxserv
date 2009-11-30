<?php

/**
 * The fax class.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('tag', 'Taggable.php');

class Fax implements Taggable{

    public $id = 0;

    public $senderPhone     = NULL;
    public $fileName        = NULL;
    public $dateReceived    = NULL; // The date (integer unix timestamp) when the fax was received.
    public $numPages        = NULL; // The number of pages included in this fax.

    public $firstName       = NULL; // The first and last name of the student whom this fax belongs to.
    public $lastName        = NULL;
    public $bannerId        = NULL; // The Banner ID number of the student whom this fax belongs to.

    public $state           = NULL; // The state this fax is in. Values defined in inc/defines.php.
    public $printed         = NULL; // boolean, whether or not the fax has been printed.

    public $keyId          = NULL; // The key_id of the Key object corresponding to this object. Used to create the Key object.


    private $key            = NULL; // Holds the Key object for this fax. This field is not stored in the database.


    /**
     * Constructor
     * If passed an ID, it will attempt to load the existing fax which has the given ID. Otherwise,
     * the additional parameters are required in order to create a new Fax object.
     */
    public function __construct($id = 0, $senderPhone = NULL, $fileName = NULL, $dateReceived = NULL)
    {
        /**
         * If the id is non-zero, then we need to load the other member variables 
         * of this object from the database
         */
        if($id != 0){
            $this->id = (int)$id;
            $this->load();
            return;
        }


        /*
         * From here down handles creating a *new* fax object
         */
        if(is_null($senderPhone) || !isset($senderPhone) || $senderPhone == ''){
            $this->senderPhone = '';
        }else{
            $this->senderPhone = $senderPhone;
        }

        $this->fileName = $fileName;

        // If dateReceived param is null, then get the current timestamp
        $this->dateReceived = is_null($dateReceived) ? time() : $dateReceived;

        $this->setState(FAX_STATE_NEW);
        $this->setPrinted(false);

        return;
    }

    /**
     * Loads the Fax object with the corresponding id. Requires that $this->id be non-zero.
     */
    private function load()
    {
        if($this->id == 0){
            return;
        }

        $db = new PHPWS_DB('faxmaster_fax');
        
        if(PHPWS_Error::logIfError($db->loadObject($this))){
            $this->id = 0;
        }

        // Try to load the key for this object as well
        //if(isset($this->getKeyId())){
        //    $this->setKey(new Key($this->getKeyId()));
        //}
    }

    /**
     * Saves this fax object
     */
    public function save()
    {
        $db = new PHPWS_DB('faxmaster_fax');

        if(PHPWS_Error::logIfError($db->saveObject($this))){
            return false;
        }

        return true;
    }

    /**
     * Deletes this fax object
     */
    public function delete($deleteFile = false)
    {
        if(!$this->id){
            return;
        }

        # Delete the fax from the database
        $db = new PHPWS_DB('faxmaster_fax');
        $db->addWhere('id', $this->getId());

        if(PHPWS_Error::logIfError($db->delete())){
            return false;
        }

        return true;

    }

    /**
     * Returns true if the fax is new (unread), false otherwise
     */
    public function isNew(){
        return $this->getState() == FAX_STATE_NEW ? true : false;
    }
    
    /**
     * Marks this fax as having been read, and saves it to the db
     */
    public function markAsRead(){
        $this->setState(FAX_STATE_READ);
        $this->save();
        //TODO error checking here
    }

    /**
     * Marks this fax as being unread, and saves it to the db
     */
    public function markAsUnread(){
        $this->setState(FAX_STATE_NEW);
        $this->save();
        // TODO error checking here
    }

    public function markAsPrinted(){
        $this->setPrinted(true);
        $this->save();
    }

    public function getKey()
    {
       if(is_null($this->key)){
           $key = new Key;
       }else{
            
       }
    }

    /**
     * Returns the DBPager row tags for this fax
     */
    public function pagerRowTags(){
        $tpl = array();

        $tpl['id'] = $this->getID();
        $tpl['senderPhone']     = $this->getSenderPhoneFormatted();
        $tpl['fileName']        = PHPWS_Text::secureLink($this->getFileName(), 'faxmaster', array('op'=>'download_fax', 'id'=>$this->getId()));
        $tpl['dateReceived']    = $this->getDateReceivedFormatted();
        $tpl['bannerId']        = is_null($this->getBannerId()) ? '' : $this->getBannerId();
        $tpl['name']            = $this->getName();

        $tpl['printed']         = $this->isPrinted() ? '' : 'style="font-weight: bold"; color: red;';
        //$tpl['new']             = $this->isNew() ? 'style="font-weight: bold"' : '';

        $tpl['numPages']        = $this->getNumPages();

        $actions[] = "[<a href=\"javascript:showNameDialog({$this->getId()})\">Edit</a>]";
        $actions[] = '[' . PHPWS_Text::secureLink('Mark as Printed', 'faxmaster', array('op'=>'mark_fax_printed', 'id'=>$this->getId())) . ']';

        $tpl['actions']         = implode(' ', $actions);

        return $tpl;
    }

    /***********************
     * Accessor / Mutators *
     ***********************/
    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getSenderPhone(){
        return $this->senderPhone;
    }

    public function getSenderPhoneFormatted(){
        if(preg_match('/^[0-9]{11}$/', $this->senderPhone)){
            // This looks like a valid 11 digit phone number 1-<area code>-, etc...
            $number = substr($this->senderPhone, 0, 1);
            $number .= '('.substr($this->senderPhone, 1, 3).')';
            $number .= substr($this->senderPhone, 2, 3);
            $number .= '-'.substr($this->senderPhone, 5, 4);
        }else if(preg_match('/^[0-9]{10}$/', $this->senderPhone)){
        // Make sure this looks like a valid 10 digit phone number, and format it accordingly
            $number = '('.substr($this->senderPhone, 0, 3).')';
            $number .= substr($this->senderPhone, 3, 3);
            $number .= '-'.substr($this->senderPhone, 6, 4);

        // Or if looks like a valid 3 digit area code
        }else if(preg_match('/^[0-9]{3}$/', $this->senderPhone)){
            $number = '(' . $this->senderPhone . ') - Unknown';

        // Otherwise, we don't know what's going on here
        }else{
            $number = 'Unknown';
        }

        return $number;
    }

    public function setSenderPhone($phone){
        $this->senderPhone = $phone;
    }

    public function getFileName(){
        return $this->fileName;
    }

    /**
     * Returns the full path to the PDF file for this fax object
     */
    public function getFullPath(){
        return FAX_PATH . $this->getFileName();
    }

    public function setFileName($name){
        $this->fileName = $name;
    }

    public function getDateReceived(){
        return $this->dateReceived;
    }

    public function getDateReceivedFormatted(){
        return date('n/d/y g:i A', $this->dateReceived);
    }

    public function setDateReceived($timestamp){
        $this->dateReceived = $timestamp;
    }

    public function getNumPages(){
        return $this->numPages;
    }

    public function setNumPages($num){
        $this->numPages = $num;
    }

    public function getFirstName(){
        return $this->firstName;
    }

    public function setFirstName($name){
        $this->firstName = $name;
    }

    public function getLastName(){
        return $this->lastName;
    }

    public function setLastName($name){
        $this->lastName = $name;
    }

    public function getName(){
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function getBannerId(){
        return $this->bannerId;
    }

    public function setBannerId($id){
        $this->bannerId = $id;
    }

    public function getState(){
        return $this->state;
    }

    public function setState($state){
        $this->state = $state;
    }

    public function isPrinted(){
        return $this->printed;
    }

    public function setPrinted($state){
        $this->printed = (int)$state;
    }

    public function getKeyId(){
        return $this->keyId;
    }

    private function setKeyId($id){
        $this->keyId = $id;
    }

    private function setKey(Key $key){
        $this->key = $key;
    }

    /******************
     * Static methods *
     ******************/

    public static function getFaxInfoByFileName($filename)
    {
        $db = new PHPWS_DB('faxmaster_fax');
        $db->addWhere('fileName', $filename);
        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /**
     * Returns the number of pages in the given fax object
     */
    public static function countPages($fax)
    {
        if(!file_exists($fax->getFullPath())){
            return null;
        }

        //open the file for reading
        $handle = @fopen($fax->getFullPath(), "rb");

        if(!$handle){
            return null;
        }

        $count = 0;
        $i=0;

        while (!feof($handle)) {
            if($i > 0) {
                $contents .= fread($handle,8152);
            }else{
                $contents = fread($handle, 1000);
                //In some pdf files, there is an N tag containing the number of
                //of pages. This doesn't seem to be a result of the PDF version.
                //Saves reading the whole file.
                if(preg_match("/\/N\s+([0-9]+)/", $contents, $found)) {
                    return $found[1];
                }
            }
            $i++;
        }

        fclose($handle);

        //get all the trees with 'pages' and 'count'. the biggest number
        //is the total number of pages, if we couldn't find the /N switch above.                
        if(preg_match_all("/\/Type\s*\/Pages\s*.*\s*\/Count\s+([0-9]+)/", $contents, $capture, PREG_SET_ORDER)) {
            foreach($capture as $c) {
                if($c[1] > $count)
                $count = $c[1];
            }
            return $count;            
        }
        return 0; 
    }
}

?>
