<?php
/**
    * faxmaster - phpwebsite module
    *
    * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
    *
    * This program is free software; you can redistribute it and/or modify
    * it under the terms of the GNU General Public License as published by
    * the Free Software Foundation; either version 2 of the License, or
    * (at your option) any later version.
    * 
    * This program is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    * GNU General Public License for more details.
    * 
    * You should have received a copy of the GNU General Public License
    * along with this program; if not, write to the Free Software
    * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
    *
    * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
*/

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

# Include configuration and defines
PHPWS_Core::requireInc('faxmaster', 'defines.php');
PHPWS_Core::requireInc('faxmaster', 'errordefines.php');
PHPWS_Core::requireConfig('faxmaster');

/* The user must be logged in to use this module. So, if
 * there's no user session, or the user is not logged
 * in, then return here
 */
if(!isset($_SESSION['User']) || !Current_User::isLogged()){
    return;
}

# Create the Faxmaster
PHPWS_Core::initModClass('faxmaster', 'Faxmaster.php');
$fm = new Faxmaster();

?>
