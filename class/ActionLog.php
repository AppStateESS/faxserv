<?php

// DB Table
define('ACTION_LOG_TABLE', 'faxmaster_action_log');

class ActionLog
{
    public $id;
    public $faxName;
    public $username;
    public $activity;
    public $timePerformed;

    public function getDb()
    {
        return new PHPWS_DB(ACTION_LOG_TABLE);
    }


    public function __construct($id = 0, $faxName = NULL, $username = NULL, $action = NULL, $timestamp = NULL)
    {
      //if id is not zero, we need to load a premade obj
      if($id != 0){
	$this->id = (int)$id;
	$this->load();
      }
      else{
	$this->faxName = $faxName;
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

      $tpl['FAXNAME'] = $this->faxName;
      $tpl['USERNAME'] = $this->username;
      $tpl['ACTIVITY'] = $this->activity;
      $tpl['TIMEPERFORMED'] = date('Y-m-d h:i:s',$this->timePerformed);
      return $tpl;
    }

    /**
     * Getters...
     */
    public function getFaxName()
    {
        return $this->faxName;
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

class RestoredActionLog extends ActionLog
{
  public function __construct()
  {
  }
}
?>