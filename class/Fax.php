<?php

/**
 * The fax class.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class Fax {

    public $id = 0;

    public $senderPhone     = NULL;
    public $fileName        = NULL;
    public $dateReceived    = NULL;  // The date (integer unix timestamp) when the fax was received

    public $state           = NULL;  // values defined in inc/defines.php
    public $printed         = NULL;  // boolean, whether or not the fax has been printed.

    public $tags            = NULL;  // An array of string "tags" for this fax


    /**
     * Constructor for loading an existing fax
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

    /**
     * Loads all the tags associated with this fax
     */
    public function loadTags(){
        # TODO
    }

    /**
     * Associates the given tag name with this fax
     */
    public function addTag($tagName){
        # TODO
    }

    /**
     * Removes the association between this fax and the given tag name
     */
    public function removeTag($tagName){
        # TODO
    }

    /**
     * Returns true if the given tag name is associated with this fax
     */
    public function isTagged($tagName){
        # TODO
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
        $tpl['printed']         = $this->isPrinted() ? '' : 'style="font-weight: bold"';
        //$tpl['new']             = $this->isNew() ? 'style="font-weight: bold"' : '';

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

    public function getTags(){
        return $this->tags;
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
}

?>
