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

class OPI_Group implements \OCP\GroupInterface {

	private $opi;

	function __construct()
	{
		$this->opi = OPIBackend::instance();
	}

	function __destruct()
	{
	}

	/**
	 * @brief is user in group?
	 * @param $uid uid of the user
	 * @param $gid gid of the group
	 * @returns true/false
	 *
	 * Checks whether the user is member of a group or not.
	 */
	public function inGroup($uid, $gid)
	{
		list($status, $rep) = $this->opi->getgroupmembers($gid);

		if( $status )
		{
			if( in_array( $uid, $rep["members"]) )
			{
				return $gid;
			}
		}

		return false;
	}

	/**
	 * @brief Get all groups a user belongs to
	 * @param $uid Name of the user
	 * @returns array with group names
	 *
	 * This function fetches all groups a user belongs to.
	 */
	public function getUserGroups($uid)
	{
		$ugroup = array();
		$groups = $this->getGroups();
		foreach( $groups as $group)
		{
			if( $this->inGroup( $uid, $group ) )
			{
				$ugroup[] = $group;
			}
		}
		return $ugroup;
	}

	/**
	 * @brief get a list of all users in a group
	 * @returns array with user ids
	 */
	public function usersInGroup($gid, $search = '', $limit = -1, $offset = 0)
	{
		list($status, $rep) = $this->opi->getgroupmembers($gid);

		if( $status )
		{
			return $rep["members"];
		}

		return array();
	}

	/**
	 * @brief get a list of all groups
	 * @returns array with group names
	 *
	 * Returns a list with all groups
	 */
	public function getGroups($search = '', $limit = -1, $offset = 0)
	{
		list($status, $rep) = $this->opi->getgroups();

		if( $status )
		{
			return $rep["groups"];
		}

		return array();
	}

	public function groupMatchesFilter($group)
	{
		return false;
	}

	/**
	 * check if a group exists
	 * @param string $gid
	 * @return bool
	 */
	public function groupExists($gid)
	{
		list($status, $rep) = $this->opi->getgroups();

		if( $status )
		{
			return in_array($gid, $rep["groups"]);
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
		return false;
	}
}
