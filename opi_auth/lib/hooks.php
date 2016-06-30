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

namespace OCA\opi_auth;

class Hooks
{

	public static function login($params)
	{

		if( isset($params["uid"]) && isset( $params["password"]) )
		{
			$opi = OPIBackend::instance();
			list($status, $resp) = $opi->login($params["uid"], $params["password"]);

			if( $status )
			{
				$session = \OC::$server->getSession();
				$session->set("opi-token",$resp["token"]);
			}
			else
			{
				log("Failed to login");
			}
		}
		else
		{
			log("Incomplete info to login with");
		}
	}

	public static function logout($params)
	{
		log("Logut from opi");
		$session = \OC::$server->getSession();
		$session->remove("opi-token");
	}
}
