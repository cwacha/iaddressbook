<?php
/**
 * iAddressBook adressbook functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */

class Authorizer {
	var $roles;
	var $roles_default;
	var $access;
	var $access_default;

    function __construct() {
    	$this->roles = array();
    	$this->roles_default = array();
    	$this->access = array();
    	$this->access_default = array();
    }

    function Authorizer() {
        $this->__construct();
    }

    function init() {
    	$this->load_roles();
    	$this->load_access();
	}

	function authorize($accountid, $permission) {
        if($accountid == null) {
            //log.debug("[action=\"{}\" result=\"failed\" account=\"null\"] Access denied. Account is null", permission);
            return false;
        }
        
        if($permission == null || empty($permission) ||
                $permission == "login" ||
                $permission == "/login" ||
                $permission == "logout" ||
                $permission == "/logout") {
            //log.debug("[action=\"{}\" result=\"success\" account=\"{}\"] Access granted. Generic action requested.", permission, account.getAccountId());
            return true;
        }
        
        $permissions = $this->get_permissions($accountid);
        if(in_array("ALL", $permissions) || in_array($permission, $permissions)) {
            //log.debug("[action=\"{}\" result=\"success\" account=\"{}\" permission=\"ALL\"] Access granted. Account has \"ALL\" permission", permission, account.getAccountId());
            return true;
        }

        //log.debug("[action=\"{}\" result=\"failed\" account=\"{}\"] Access denied. No permission filter matched action.", permission, account.getAccountId());
        return false;
	}

	function get_roles($accountid = '') {
		if(empty($accountid))
			return $this->roles;

		if(!array_key_exists($accountid, $this->access))
			return array();

		return $this->access[$accountid];
	}

	function get_permissions($accountid) {
		$roles = $this->get_roles($accountid);

		$permissions = array();
		foreach($roles as $dummy => $role) {
			if(!array_key_exists($role, $this->roles))
				continue;
			$perms = $this->roles[$role];
			$permissions = array_merge($permissions, $perms);
		}
		array_unique($permissions);
		return $permissions;
	}

	function set_roles($accountid, $roles) {
		if(empty($accountid))
			return;
        if(!empty($roles)) {
            $this->access[$accountid] = $roles;
        } else {
            // delete access
            unset($this->access[$accountid]);
        }
	}

	function set_role_permissions($roleid, $permissions) {
		if(empty($roleid) || $roleid == 'all')
			return;

		if(empty($permissions)) {
			// delete role
			unset($this->roles[$roleid]);
		} else {
			if(!is_array($permissions))
				return;

			// save role
			$this->roles[$roleid] = $permissions;
		}
	}

	function load_roles() {
        // load roles (roles and permissions)
        include(AB_BASEDIR.'/lib/default/roles.php');
        $this->roles_default = $this->explode_config($roles);
        $file = AB_CONFDIR.'/roles.php';
        if(file_exists($file))
            include($file);
        $this->roles = $this->explode_config($roles);
	}

	function save_roles() {
		$this->save_php('roles');
	}

	function load_access() {
        // load access (accountid and roles)
        include(AB_BASEDIR.'/lib/default/access.php');
        $this->access_default = $this->explode_config($access);
        $file = AB_CONFDIR.'/access.php';
        if(file_exists($file))
            include($file);
        $this->access = $this->explode_config($access);
	}

	function save_access() {
		$this->save_php('access');
	}

    function explode_config($config) {
        $ret = array();
        foreach($config as $key => $value) {
            $ret[$key] = explode(',', $value);
        }
        return $ret;
    }

    function join_config($config) {
        $ret = array();
        foreach($config as $key => $value) {
            $ret[$key] = join(',', $value);
        }
        return $ret;        
    }

	function save_php($type) {
		$type_default = $type . '_default';
		$config = $this->join_config($this->$type);
        $config_default = $this->join_config($this->$type_default);
        $filename = AB_CONFDIR.'/'.$type.'.php';
    
        if(!is_array($this->$type)) {
            msg("Internal error while saving $type.php: $type array empty", -1);
            return false;
        }

        $new_config = array();
        foreach($config as $key => $value) {
            if(!array_key_exists($key, $config_default) || $config[$key] != $config_default[$key]) {
                $new_config[$key] = $value;
            }
        }
        if(empty($new_config)) {
            unlink($filename);
            return true;
        }

        $header = array();
        $header[] = "/**";
        $header[] = " * This is the AddressBook's $type file";
        $header[] = " * This is a piece of PHP code so PHP syntax applies!";
        $header[] = " *";
        $header[] = " * Automatically generated file. Do not modify!";
        $header[] = " */\n\n";

        $fd = fopen($filename, "w");
        if(!$fd) {
            $in = array('$1', '$2');
            $out = array("$type", "$filename");
            msg(str_replace($in, $out, lang("authorize_error_cannot_write")), -1);

            return false;
        }
        fwrite($fd, "<?php\n\n");
        
        foreach($header as $line) {
            fwrite($fd, $line . "\n");
        }

        $data = array_to_text($new_config, '$'.$type);
        fwrite($fd, $data);

        fwrite($fd, "\n\n?>");
        fclose($fd);
        fix_fmode($filename);

        return true;
	}

}

?>
