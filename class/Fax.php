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

    public $state           = NULL;  // values defined in inc/defines.php

    public $tags            = NULL;  // An array of string "tags" for this fax


    public function __construct($id = 0)
    {
        if(!$id){
            return;
        }

        $this->id = (int)$id;
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
        $tpl['senderPhone'] = $this->getSenderPhone();
        $tpl['fileName']    = $this->getFileName();
        $tpl['state']       = $this->getState() == 0 ? "Read" : "New";

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

    public function setSenderPhone($phone){
        $this->senderPhone = $phone;
    }

    public function getFileName(){
        return $this->fileName;
    }

    public function setFileName($name){
        $this->fileName = $name;
    }

    public function getState(){
        return $this->state;
    }

    public function setState($state){
        $this->state = $state;
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
