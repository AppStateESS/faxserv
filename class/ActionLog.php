<?php

// DB Table
define('ACTION_LOG_TABLE', 'faxmaster_action_log');

class ActionLog
{
    public $id;
    public $faxID;
    public $username;
    public $activity;
    public $timePerformed;

    public function getDb()
    {
        return new PHPWS_DB(ACTION_LOG_TABLE);
    }


    public function __construct($id = 0, $faxID = NULL, $username = NULL, $action = NULL, $timestamp = NULL)
    {
      //if id is not zero, we need to load a premade obj
      if($id != 0){
	$this->id = (int)$id;
	$this->load();
	return;
      }
      else{
	$this->faxID = $faxID;
	$this->username = $username;
	$this->activity = $action;
	$this->timePerformed = $timestamp;
	$this->save();
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
      $tpl['ACTIVITY'] = $this->activity;
      $tpl['TIMEPERFORMED'] = $this->timePerformed;
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
    public function getActivity()
    {
        return $this->activity;
    }
    public function getTimePerformed()
    {
        return $this->timePerformed;
    }
}
?>