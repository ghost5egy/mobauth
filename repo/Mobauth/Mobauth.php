<?php
/**
 * @version    $Id: Mobauth.php 7180 2018-06-02 16:51:53Z jinx $
 * @package    Joomla.Tutorials
 * @subpackage Plugins
 * @license    GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();


/**
 * Example Authentication Plugin.  Based on the example.php plugin in the Joomla! Core installation
 *
 * @package    Joomla.Tutorials
 * @subpackage Plugins
 * @license    GNU/GPL
 */
 
 
 
class plgAuthenticationMobauth extends JPlugin
{
    /**
     * This method should handle any authentication and report back to the subject
     * This example uses simple authentication - it checks if the password is the reverse
     * of the username (and the user exists in the database).
     *
     * @access    public
     * @param     array     $credentials    Array holding the user credentials ('username' and 'password')
     * @param     array     $options        Array of extra options
     * @param     object    $response       Authentication response object
     * @return    boolean
     * @since 1.5
     */
    function onUserAuthenticate( $credentials, $options, &$response ){
        /*
         * Here you would do whatever you need for an authentication routine with the credentials
         *
         * In this example the mixed variable $return would be set to false
         * if the authentication routine fails or an integer userid of the authenticated
         * user if the routine passes
         */
		//require_once JPATH_PLUGINS . '/authentication/joomla/joomla.php';
		$dbM    = JFactory::getDbo();
		$queryM = $dbM->getQuery(true)
			->select('user_id')
			->from('#__user_profiles')
			->where('profile_value = ' . $dbM->quote('"'.$credentials['username'].'"').' AND '.$dbM->quoteName('profile_key').'='.$dbM->quote('profile.phone'));
		$dbM->setQuery($queryM);
		$resultM = $dbM->loadObject();
		
		$db = JFactory::getDbo();
		$query	= $db->getQuery(true)
			->select('id,username,password')
			->from('#__users')
			->where('id=' . $resultM->user_id);
		$db->setQuery($query);
		JFactory::getApplication()->enqueueMessage($query->__toString); 
		$result = $db->loadObject();
		
		$credentials['username'] = $result->username;
		
		if (!$result) {
			$response->status = STATUS_FAILURE;
			$response->error_message = 'User does not exist';
		}else{
			$match = JUserHelper::verifyPassword($credentials['password'], $result->password, $result->id);
			if ($match === true){
				jimport( 'joomla.user.authentication');
		       $auth = & JAuthentication::getInstance();
		       $response = $auth->authenticate($credentials, $options);
			}else{
				$response->status = JAuthentication::STATUS_FAILURE;
				$response->error_message = 'Invalid username and password';
			}
			
		}
	}
}
?>
