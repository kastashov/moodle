<?php

/**
 * Single Sign-on library for Moodle.
 *
 * Designed to connect with other sites.
 *
 * @author Morgan Harris
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

global $DB;

/**
 * Registers a site for SSO.
 * 
 * @return site object on success, false on error
 * @param string $name Site name
 * @param string $url URL to reach remote SSO
 * @param string $externalid ID of this server sent to remote server
 * @param string $password Initialisation password
 * @author Morgan Harris
 **/
function sso_register_site($name,$url,$externalid,$password)
{
	//first assert that we are connecting by https
	//if(substr($url,0,8) != "https://")
	//	return false;

	//connect to this site and share the initial key list
	//initial key list is 20 keys
	
	global $DB;
	
	$keys = array();
	for($i = 0; $i < 20; $i ++)
	{
		$keys[$i] = sso_generate_key();
	}
	
	//connect
	$msg = array("action" => "init", "keys" => $keys, "externalid" => $externalid, "password" => $password);
	$retval = sso_send_post($msg,$url);
	
	if($retval['status']=='success')
	{
		//insert into our DB
		$site = new stdClass();
		$site->name = $name;
		$site->url = $url;
		$id = $DB->insert_record('local_sso_sites',$site);
		$site->id = $id;
		foreach($keys as $k)
		{
			try {
			$kobj = new stdClass();
			$kobj->site_id = $id;
			$kobj->pskey = $k;
			$rslt = $DB->insert_record('local_sso_keys',$kobj);
			}
			catch (Exception $e) {
				print_r($e);
				throw $e;
			}
		}
		
		return $site;
	}
	
	return false;
}

/**
 * Determines if site has been registered.
 *
 * @return site record on success, false on failure
 * @param string $url URL to remote SSO
 * @author Morgan Harris
 **/
function sso_site_for_url($url)
{
	global $DB;
	return $DB->get_record('local_sso_sites',array('url' => $url));
}

function sso_site_for_name($name)
{
	global $DB;
	return $DB->get_record('local_sso_sites',array('name' => $name));
}

/**
 * Sign on to the site specified
 *
 * @return void
 * @author Morgan Harris
 **/
function sso_sign_on($site,$externalid,$userid = NULL,$ipaddr = NULL)
{
	global $USER, $DB;
	
	$key = $DB->get_field('local_sso_keys','pskey',array('site_id' => $site->id),IGNORE_MULTIPLE);
	if($userid == NULL)
		$userid = $USER->username;
	if($ipaddr == NULL) {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddr = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ipaddr = $_SERVER['REMOTE_ADDR'];
        }
    }
	$newkey = sso_generate_key($site->id);
		
	$msg = array(
		"action" => "signon",
		"externalid" => $externalid,
		"key" => $key,
		"user" => $userid,
		"ipaddr" => $ipaddr,
		"newkey" => $newkey
	);
	$retval = sso_send_post($msg,$site->url);
	if($retval['exchange']==true)
	{
		sso_exchange_key($site->id,$key,$newkey);
	}
	
	if($retval['status'] == 'success')
	{
		return $retval['ident'];
	}
	else if($retval['status'] == 'wrongkey')
	{
		$DB->delete_records('local_sso_keys',array('pskey' => $key));
		print_error("SSO Error: Wrong key. If you see this error again, please notify an admin. Note that all hack attempts will be investigated.");
	}
	
	return false;
	
}

function sso_api_call($site,$url,$params)
{
	
	global $DB;

	$key = $DB->get_field('local_sso_keys','pskey',array('site_id' => $site->id),IGNORE_MULTIPLE);
	$newkey = sso_generate_key($site->id);
	
	$params['key'] = $key;
	$params['newkey'] = $newkey;

	$retval = sso_send_post($params,$url);
	
	//$retval may be false on error so @-protect these
	if(@$retval['exchange'] == true)
	{
		sso_exchange_key($site->id,$key,$newkey);
	}
	
	if(@$retval['status'] == 'wrongkey')
	{
		$DB->delete_records('local_sso_keys',array('pskey' => $key));
	}
	
	return $retval;
}

/**
 * Replace a used key with a new one.
 *
 * @param int $site The ID of the site.
 * @param string $old The old key.
 * @param string $new The new key. If not set, generates a new key automatically.
 * @return string The new key.
 * @author Morgan Harris
 **/
function sso_exchange_key($site,$old,$new = NULL)
{
	global $DB;
	
	$DB->delete_records('local_sso_keys',array('site_id' => $site, 'pskey' => $old));
	$obj = new Object();
	if($new==NULL)
		$new = sso_generate_key($site);
	$obj->pskey = $new;
	$obj->site_id = $site;
	$DB->insert_record('local_sso_keys',$obj);
	return $new;
}

/**
 * Generates a new key for a specific site.
 *
 * @param int $site The ID of the site. If not set, generates a truly random key.
 * @return void
 * @author Morgan Harris
 **/
function sso_generate_key($site = NULL)
{
	global $DB;
	
	//generate 32 random bytes
	$ret = "";
	$exists = 0;
	do {
		for($i = 0; $i < 32; $i++)
			$ret .= chr(round(mt_rand(0,255)));
		$ret = base64_encode($ret);
		if($site)
			$exists = $DB->count_records("sso_keys", array("site_id" => $site, "pskey" => $ret));
	} while($exists > 0);
	return $ret;
}

function sso_send_post($msg,$url)
{
    $c =  new curl(array('cache' => false));
    $response = $c->post($url, serialize($msg));
    return unserialize($response);
}
