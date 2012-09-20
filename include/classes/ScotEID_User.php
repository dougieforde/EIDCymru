<?php
class ScotEID_User extends ScotEID_ExtendedModel
{
  const WS_ROLE_ID = 13;

  const ROLE_AUTHENTICATED_USER = 'authenticated user';
  const ROLE_MART               = 'mart';
  const ROLE_ABATTOIR           = 'abattoir';
  const ROLE_ACCOUNT_ADMIN      = 'Account Admin';
  const ROLE_FIELDSMAN          = 'Fieldsman';
  const ROLE_LOCAL_AUTH         = 'Local_Auth';
  const ROLE_RPID_USER          = 'RPID_user';
  const ROLE_SUPERUSER          = 'SuperUser';
  const ROLE_PIG_FARM           = 'PigFarm';
  const ROLE_PARTNER_FARM       = 'PartnerFarm';
  const ROLE_BPEX               = 'BPEX';

  const ROLE_SAMU_WS            = "SAMU_WS";

  public static function get($uid) {
    return ScotEID_User::first(array('conditions' => array('uid' => $uid)));
  }
  
  public static function get_database_name() {
    $e = $GLOBALS['_SCOTEID_WEBSERVICES_ENV']['db'];
    if($e && isset($e['drupal_database'])) {
      return $e['drupal_database'];
    }
  }

  public static function get_table_name() {
		return static::get_database_name() . ".users";
	}

	public function get_primary_key() {
		return array(
			'uid' => quote_int($this->get_uid())
		);
	}

	public static function authenticate($username, $password) {
	  $u = static::first(array('conditions' => array(
	   'username'       => $username,
	   'password_hash'  => md5($password)
	  )));

	  if($u) {
	    return $u->get_uid();
	  } else {
	    return false;
	  }
  }
  
  public function get_name() { 
    return $this->get_username();
  }

  protected static function get_attribute_definitions() {
	  return array(
	    'uid'           => array('type' => 'int'),
	    'username'      => array('type' => 'string', 'field' => 'name', 'public' => true),
	    'password_hash' => array('type' => 'string', 'field' => 'pass', 'public' => true),
	    'email'         => array('type' => 'string', 'field' => 'mail', 'public' => true)
	  );
	}
	
	protected function after_save_on_create() {
		$this->set_uid(dbw_insert_id());
		parent::after_save_on_create();
	}
	
	public function set_password($password) {
	  $this->set_password_hash(md5($password));
	}

  public static function add_role($uid, $role) {
    static::$_user_roles[$uid] = null;
    $d     = ScotEID_User::get_database_name();
    $qrole = quote_str($role);
    $insert_sql = "INSERT IGNORE $d.users_roles (rid, uid) VALUES ((SELECT rid FROM $d.role WHERE name = $qrole),$uid)";
    dbw_query($insert_sql);
  }
  
  public static function remove_role($uid, $role) {
    static::$_user_roles[$uid] = null;
    $d     = ScotEID_User::get_database_name();
    $qrole = quote_str($role);
    $delete_sql = "DELETE FROM $d.users_roles WHERE rid = (SELECT rid FROM $d.role WHERE name = $qrole) AND uid = $uid";
    dbw_query($delete_sql);
  }

  private static $_user_roles = array();
    
  public static function user_has_role($uid, $role) {
    $uid = (int) $uid;
    
    if(is_array($role)) {
      $roles = $role;
    } else {
      $roles = array($role);
    }
    
    $d = static::get_database_name();
    
    if(!isset(static::$_user_roles[(int) $uid])) {
      $quid     = quote_int($uid);
      $sql      = "SELECT r.name FROM $d.users_roles ur INNER JOIN $d.role r ON r.rid = ur.rid WHERE ur.uid = $quid";
      $res      = dbw_query($sql);
      
      $result = array();
      
      while($row = dbw_row($res)) {
        $result[] = $row['name'];
      }
      
      static::$_user_roles[$uid] = $result;
    }

    return count(array_intersect(static::$_user_roles[$uid], $roles)) > 0;
  }
    
	public static function get_registered_holdings($uid) {
    $k = ScotEID_KeeperHolding::find(array('conditions' => array('uid' => $uid)));
    $r = array();
    foreach($k as $kh) {
      $r[] = $kh->get_cph();
    }
    return $r;
	}

  public static function is_registered_holding($uid, $check_holding) {
    // TODO: could clean this up to use a count of keeper holdings
    $holdings = ScotEID_User::get_registered_holdings($uid);
		foreach($holdings as $holding) {
		  if($holding->getCPH() == $check_holding->getCPH())
		    return true;
		}
		return false;
  }
}
?>
