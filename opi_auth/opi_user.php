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

function log($msg)
{
	\OCP\Util::writeLog('opi_auth', $msg, \OCP\Util::DEBUG);
}

class OPI_User implements \OCP\UserInterface {

	private $opi;
	private $ncConfig;

	function __construct()
	{
		$this->opi = OPIBackend::instance();
        $this->ncConfig = \OC::$server->getConfig();

	}

	function __destruct()
	{
	}


	/**
	 * Sync the user's E-Mail address with the address stored by NextCloud.
	 *
	 * @param string $uid The user's ID to sync
	 * @return bool Success or Fail
	 */
	private function doEmailSync($uid)
	{
		$currMail = $this->ncConfig->getUserValue(    $uid,
					'settings',
					'email', '');

		$secopMail = $this->getEMailAddress($uid);

		if($currMail !== $secopMail)
		{
			$this->ncConfig->setUserValue(	$uid,
					'settings',
					'email',
					$secopMail);
		}
		return true;
	}


	/**
	 * @brief Check if the password is correct
	 * @param $uid The username
	 * @param $password The password
	 * @returns true/false
	 *
	 * Check if the password is correct without logging in the user
	 */
	public function checkPassword($uid, $password)
	{
		list($status, $rep) = $this->opi->authenticate($uid,$password);
		return $status?$uid:false;
	}

	/**
	 * @brief Get a list of all users
	 * @returns array with all uids
	 *
	 * Get a list of all users.
	 */
	public function getUsers($search = '', $limit = 10, $offset = 0)
	{
		list($status, $rep) = $this->opi->getusers();
		if( ! $status )
		{
			return array();
		}

		$users = array();
		
		foreach( $rep["users"] as $user)
		{
			$users[]=$user["username"];
		}

		return $users;
	}

	/**
	 * @brief check if a user exists
	 * @param string $uid the username
	 * @return boolean
	 */
	public function userExists($uid)
	{
		list($status, $rep) = $this->opi->userexists($uid);
		return $status?$rep["exists"]:$status;
	}

	/**
	* @brief delete a user
	* @param $uid The username of the user to delete
	* @returns true/false
	*
	* Deletes a user
	*/
	public function deleteUser($uid)
	{
		return false;
	}

	/**
	* @brief get the user's home directory
	* @param string $uid the username
	* @return boolean
	*/
	public function getHome($uid)
	{
		return false;
	}

	/**
	 * @brief get display name of the user
	 * @param $uid user ID of the user
	 * @return display name
	 */
	public function getDisplayName($uid)
	{
		list($status, $rep) = $this->opi->getuser($uid);
		if( $status )
		{
			// sync email
			$this -> doEmailSync($uid);
			return $rep["displayname"];
		}
		return false;
	}

	/**
	 * get the users email address
	 *
	 * @return string|null
	 * @since 9.0.0
	 */
	public function getEMailAddress($uid)
	{
		list($status, $rep) = $this->opi->getuser($uid);
		if( $status )
		{
			return $rep["defaultemail"];
		}
		return false;
	}

	/**
	 * @brief Get a list of all display names
	 * @returns array with  all displayNames (value) and the correspondig uids (key)
	 *
	 * Get a list of all display names and user ids.
	 */
	public function getDisplayNames($search = '', $limit = null, $offset = null)
	{
		list($status, $rep) = $this->opi->getusers();
		if( $status )
		{
			$users = array();
			foreach( $rep["users"] as $user)
			{
				$users[$user["username"]] = $user["displayname"];
			}
			return $users;
		}
		return false;
	}

	/**
	* @brief Check if backend implements actions
	* @param $actions bitwise-or'ed actions
	* @returns boolean
	*
	* Returns the supported actions as int to be
	* compared with OC_USER_BACKEND_CREATE_USER etc.
	*/
	public function implementsActions($actions)
	{
		return (bool)((\OC\User\Backend::CHECK_PASSWORD
			| \OC\User\Backend::GET_DISPLAYNAME
			| \OC\User\Backend::COUNT_USERS)
			& $actions);
	}

	/**
	 * @return bool
	 */
	public function hasUserListings()
	{
		return true;
	}

	/**
	 * counts the users in LDAP
	 *
	 * @return int | bool
	 */
	public function countUsers()
	{
		list($status, $rep) = $this->opi->getusers();
		if( $status )
		{
			return count( $rep["users"]);
		}
		return false;
	}
}
