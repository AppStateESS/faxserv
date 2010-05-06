<?php
/**
    * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
*/

$use_permissions  = true;
$item_permissions = true;

$permissions['download']    = dgettext('faxmaster', 'Download/view faxes');
$permissions['editSender']  = dgettext('faxmaster', 'Edit fax sender names');
$permissions['markPrinted'] = dgettext('faxmaster', 'Mark faxes as printed');
$permissions['hide']        = dgettext('faxmaster', 'Mark faxes as hidden');

# Permissions for the general queue (i.e. new, un-assigned faxes)
/*
$permissions['unassigned_download'] = dgettext('faxmaster', 'View faxes in un-assigned fax queue');
$permissions['unassigned_assign']   = dgettext('faxmaster', 'Assign faxes in the un-assigned fax queue');
$permissions['unassigned_mark_new'] = dgettext('faxmaster', 'Mark un-assigned faxes as new');
$permissions['unassigned_delete']   = dgettext('faxmaster', 'Delete un-assigned faxes');

# Permissions for viewing/editing a user's own faxes (i.e. assigned to the current user)
$permissions['own_download']        = dgettext('faxmaster', 'View faxes in his/her fax queue');
$permissions['own_assign']          = dgettext('faxmaster', 'Assign faxes in his/her fax queue');
$permissions['own_mark_new']        = dgettext('faxmaster', 'Mark un-assigned faxes as new');
$permissions['own_delete']          = dgettext('faxmaster', 'Delete un-assigned faxes');

# Permissions for viewing/edit other user's faxes (i.e. not assigned to the current user)
$permissions['other_download']      = dgettext('faxmaster', 'View faxes in un-assigned fax queue');
$permissions['other_assign']        = dgettext('faxmaster', 'Assign faxes in the un-assigned fax queue');
$permissions['other_mark_new']      = dgettext('faxmaster', 'Mark un-assigned faxes as new');
*/

?>
