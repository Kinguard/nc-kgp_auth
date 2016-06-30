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

class OPIBackend
{

    private static $instance;

    public static function instance()  {
        if ( !isset(self::$instance) ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private $sock;
    private $token;
    private $connected = false;

    function __construct($token = "")
    {
        $this->token = $token;
    }

    function _connect()
    {
	if( ! $this->connected )
	{
        	$this->sock = stream_socket_client("unix:///tmp/opib");
		$this->connected = !( $this->sock === FALSE );
	}
        return $this->connected;
    }

    function _processreply( $res )
    {
        if( $res["status"]["value"] == 0 )
        {
            return array(true, $res);
        }
        else
        {
            return array(false, $res["status"]["desc"]);
        }
    }

    function _dorequest($req)
    {
        if( !$this->_connect() )
        {
            return array(false, "Not connected");
        }

        fwrite($this->sock,json_encode($req, JSON_UNESCAPED_UNICODE ));

        $res=json_decode(fgets($this->sock,16384),true);


		fclose( $this->sock);
		$this->connected = FALSE;

        return $this->_processreply($res);
    }

    function token($token)
    {
        $this->token = $token;
    }

    function login($user, $password)
    {
        $req = array();
        $req["cmd"] = "login";
        $req["username"] = $user;
        $req["password"] = $password;

	list($status, $rep) = $this->_dorequest($req);

	if( $status )
	{
            $this->token = $rep["token"];
	}
        return array($status, $rep);
    }

    function authenticate($user, $password)
    {
        $req = array();
        $req["cmd"] = "authenticate";
        $req["username"] = $user;
        $req["password"] = $password;

        return $this->_dorequest($req);
    }


    function createuser($user, $password, $display)
    {
        $req = array();
        $req["cmd"] = "createuser";
        $req["token"] = $this->token;
        $req["username"] = $user;
        $req["password"] = $password;
        $req["displayname"] = $display;

        return $this->_dorequest($req);
    }


    function updateuserpassword($user, $password, $newpassword)
    {
        $req = array();
        $req["cmd"] = "updateuserpassword";
        $req["token"] = $this->token;
        $req["username"] = $user;
        $req["password"] = $password;
        $req["newpassword"] = $newpassword;

        return $this->_dorequest($req);
    }

    function updateuser($user, $display)
    {
        $req = array();
        $req["cmd"] = "updateuser";
        $req["token"] = $this->token;
        $req["username"] = $user;
        $req["displayname"] = $display;

        return $this->_dorequest($req);
    }

    function getuser($user)
    {
        $req = array();
        $req["cmd"] = "getuser";
        $req["token"] = $this->token;
        $req["username"] = $user;

        return $this->_dorequest($req);
    }

    function userexists( $user )
    {
        $req = array();
        $req["cmd"] = "getuserexists";
        $req["username"] = $user;

        return $this->_dorequest($req);
    }

    function getusers()
    {
        $req = array();
        $req["cmd"] = "getusers";
        $req["token"] = $this->token;

        return $this->_dorequest($req);
    }

    function deleteuser($username)
    {
        $req = array();
        $req["cmd"] = "deleteuser";
        $req["token"] = $this->token;
        $req["username"] = $username;

        return $this->_dorequest($req);
    }

    function getgroups()
    {
        $req = array();
        $req["cmd"] = "groupsget";
        $req["token"] = $this->token;

        return $this->_dorequest($req);
    }

    function addgroup($group)
    {
        $req = array();
        $req["cmd"] = "groupadd";
        $req["token"] = $this->token;
        $req["group"] = $group;

        return $this->_dorequest($req);
    }

    function addgroupmember($group, $member)
    {
        $req = array();
        $req["cmd"] = "groupaddmember";
        $req["token"] = $this->token;
        $req["group"] = $group;
        $req["member"] = $member;

        return $this->_dorequest($req);
    }

    function getgroupmembers($group)
    {
        $req = array();
        $req["cmd"] = "groupgetmembers";
        $req["token"] = $this->token;
        $req["group"] = $group;

        return $this->_dorequest($req);
    }

    function deletegroup( $group)
    {
        $req = array();
        $req["cmd"] = "groupremove";
        $req["token"] = $this->token;
        $req["group"] = $group;

        return $this->_dorequest($req);
    }

    function deletegroupmember($group, $member)
    {
        $req = array();
        $req["cmd"] = "groupremovemember";
        $req["token"] = $this->token;
        $req["group"] = $group;
        $req["member"] = $member;

        return $this->_dorequest($req);
    }
}
