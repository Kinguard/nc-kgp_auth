<?php

/**
*
* @author Tor Krill
* @copyright 2014 Tor Krill tor@openproducts.se
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

$opi = OCA\opi_auth\OPIBackend::instance();
$session = \OC::$server->getSession();

if( $session->exists("opi-token") )
{
	$opi->token( $session->get("opi-token") );
}

\OCP\Util::connectHook('OC_User', 'pre_login', 'OCA\opi_auth\Hooks', 'login');
\OCP\Util::connectHook('OC_User', 'logout', 'OCA\opi_auth\Hooks', 'logout');

OC_User::useBackend(new OCA\opi_auth\OPI_User(  ) );
\OC::$server->getGroupManager()->addBackend( new OCA\opi_auth\OPI_Group(  ) );

