<?php

// DB Table
define('ACTION_LOG_TABLE', 'faxmaster_action_log');

class ActionLog
{
    public $id;
    public $FaxID;
    public $username;
    public $action;
    public $timestamp;

    public function getDb()
    {
        return new PHPWS_DB(ACTION_LOG_TABLE);
    }


    public function __construct($id = 0)
    {
        if(!is_null($id) && is_numeric($id)){
            $this->id = $id;
            
            $result = $this->load();

            if(!$result){
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
    }
    
    public function getId(){
        return $this->id;
    }

    public function load()
    {
        if(is_null($this->id) || !is_numeric($this->id))
            return false;

        $db = $this->getDb();
        $db->addWhere('id', $this->id);
        $result = $db->loadObject($this);
        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('faxmaster', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
        
        return $result;
    }

    public function save()
    {
        $db = $this->getDb();
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('faxmaster', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        // return new id
        return $result;
    }


    // For ActionLogView
    public function rowTags()
    {

      $tpl      = array();

      //this is from noomination, change it to reflect our action log
      $tpl['ID'] = $this->faxID;
      $tpl['USERNAME'] = $this->username;
      $tpl['ACTION'] = $this->action;
      $tpl['TIMESTAMP'] = $this->timestamp;
      return $tpl;
    }

    /**
     * Getters...
     */
    public function getFaxID()
    {
        return $this->faxID;
    }
    public function getUsername()
    {
        return $this->username;
    }
    public function getAction()
    {
        return $this->action;
    }
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
?>