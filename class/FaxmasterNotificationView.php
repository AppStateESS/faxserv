<?php

define('FAX_NOTIFICATION_ERROR',   9);
define('FAX_NOTIFICATION_WARNING', 8);
define('FAX_NOTIFICATION_SUCCESS', 7);

PHPWS_Core::initModClass('notification', 'NQ.php');

class FaxmasterNotificationView
{
    private $notifications = array();
    
    public function popNotifications()
    {
        $this->notifications = NQ::popAll('faxmaster');
    }
    
    public function show()
    {
        if(empty($this->notifications)) {
            return '';
        }
        
        $tpl = array();
        $tpl['NOTIFICATIONS'] = array();
        foreach($this->notifications as $notification) {
            
            if(!$notification instanceof Notification) {
                throw new InvalidArgumentException('Something was pushed onto the NQ that was not a Notification.');
            }
            $type = self::resolveType($notification);
            $tpl['NOTIFICATIONS'][][$type] = $notification->toString();
        }
        
        return PHPWS_Template::process($tpl, 'faxmaster', 'NotificationView.tpl');
    }
    
    protected function resolveType(Notification $notification)
    {
        switch($notification->getType()) {
            case FAX_NOTIFICATION_ERROR:
                return 'ERROR';
            case FAX_NOTIFICATION_WARNING:
                return 'WARNING';
            case FAX_NOTIFICATION_SUCCESS:
                return 'SUCCESS';
            default:
                return 'UNKNOWN';
        }
    }
}
?>
