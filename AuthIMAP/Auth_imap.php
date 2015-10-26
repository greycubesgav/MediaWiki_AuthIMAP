<?php
// vim:sw=2:softtabstop=2:textwidth=80
//
// This program is free software: you can redistribute it and/or modify it
// under the terms of the GNU General Public License as published by the Free
// Software Foundation, either version 3 of the License, or (at your option)
// any later version.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
// FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
// more details.
//
// You should have received a copy of the GNU General Public License along with
// this program.  If not, see <http://www.gnu.org/licenses/>.
//
// Copyright 2007 Rusty Burchfield

// Add these two lines to the bottom of your LocalSettings.php
// require_once('extensions/Auth_imap.php');
// $wgAuth = new Auth_imap();
//
// This plugin requires that your PHP install be compiled with the cclient
// library.  Please see the PHP manual page below for more information.
//
// It is necessary to edit the authenticate method to connect to your IMAP
// server.  Read this page from the PHP manual for more information.
// http://us.php.net/manual/en/function.imap-close.php
//
// You probably want to edit the initUser function to set the users real name
// and email address properly for your configuration.

// Don't let anonymous people do things...
$wgGroupPermissions['*']['createaccount']   = false;
$wgGroupPermissions['*']['read']            = false;
$wgGroupPermissions['*']['edit']            = false;

// The Auth_imap class is an AuthPlugin so make sure we have this included.
// May have to change to require_once('includes/AuthPlugin.php');
require_once('AuthPlugin.php');

class Auth_imap extends AuthPlugin {

  function Auth_imap() {
  }

  /**
   * Disallow password change.
   *
   * @return bool
   */
  function allowPasswordChange() {
    return false;
  }

  /**
   * This should not be called because we do not allow password change.  Always
   * fail by returning false.
   *
   * @param $user User object.
   * @param $password String: password.
   * @return bool
   * @public
   */
  function setPassword($user, $password) {
    return false;
  }

  /**
   * We don't support this but we have to return true for preferences to save.
   *
   * @param $user User object.
   * @return bool
   * @public
   */
  function updateExternalDB($user) {
    return true;
  }

  /**
   * We can't create external accounts so return false.
   *
   * @return bool
   * @public
   */
  function canCreateAccounts() {
    return false;
  }

  /**
   * We don't support adding users to whatever service provides REMOTE_USER, so
   * fail by always returning false.
   *
   * @param User $user
   * @param string $password
   * @return bool
   * @public
   */
  function addUser($user, $password) {
    return false;
  }


  /**
   * Pretend all users exist.  This is checked by authenticateUserData to
   * determine if a user exists in our 'db'.  By returning true we tell it that
   * it can create a local wiki user automatically.
   *
   * @param $username String: username.
   * @return bool
   * @public
   */
  function userExists($username) {
    return true;
  }

  /**
   * Attempt to authenticate the user via IMAP.
   *
   * @param $username String: username.
   * @param $password String: user password.
   * @return bool
   * @public
   */
  function authenticate($username, $password) {
    // Connect to the IMAP server running on port 143 on example.com using tls
    $mbox = imap_open("{example.com:143/tls}INBOX",
                      $username,
                      $password,
                      OP_HALFOPEN);
    if (!$mbox) {
       // Failed to authenticate or connect...
       return false;
    }
    imap_close($mbox);
    return true;
  }

  /**
   * Check to see if the specific domain is a valid domain.
   *
   * @param $domain String: authentication domain.
   * @return bool
   * @public
   */
  function validDomain($domain) {
    return true;
  }

  /**
   * When a user logs in, optionally fill in preferences and such.
   * For instance, you might pull the email address or real name from the
   * external user database.
   *
   * The User object is passed by reference so it can be modified; don't
   * forget the & on your function declaration.
   *
   * @param User $user
   * @public
   */
  function updateUser(&$user) {
    // We only set this stuff when accounts are created.
    return true;
  }

  /**
   * Return true because the wiki should create a new local account
   * automatically when asked to login a user who doesn't exist locally but
   * does in the external auth database.
   *
   * @return bool
   * @public
   */
  function autoCreate() {
    return true;
  }

  /**
   * Return true to prevent logins that don't authenticate here from being
   * checked against the local database's password fields.
   *
   * @return bool
   * @public
   */
  function strict() {
    return true;
  }

  /**
   * When creating a user account, optionally fill in preferences and such.
   * For instance, you might pull the email address or real name from the
   * external user database.
   *
   * @param $user User object.
   * @public
   */
  function initUser(&$user) {
    global $_SERVER;
    $username = $_SERVER['REMOTE_USER'];

    // Using your own methods put the users real name here.
    $user->setRealName('');
    // Using your own methods put the users email here.
    $user->setEmail($username . '@example.com');

    $user->mEmailAuthenticated = wfTimestampNow();
    $user->setToken();

    //turn on e-mail notifications by default
    $user->setOption('enotifwatchlistpages', 1);
    $user->setOption('enotifusertalkpages', 1);
    $user->setOption('enotifminoredits', 1);
    $user->setOption('enotifrevealaddr', 1);

    $user->saveSettings();
  }

  /**
   * Modify options in the login template.  This shouldn't be very important
   * because no one should really be bothering with the login page.
   *
   * @param $template UserLoginTemplate object.
   * @public
   */
  function modifyUITemplate(&$template) {
    //disable the mail new password box
    $template->set('useemail', false);
    $template->set('create', false);
    $template->set('domain', false);
    $template->set('usedomain', false);
  }

  /**
   * Normalize user names to the mediawiki standard to prevent duplicate
   * accounts.
   *
   * @param $username String: username.
   * @return string
   * @public
   */
  function getCanonicalName($username) {
    // lowercase the username
    $username = strtolower($username);
    // uppercase first letter to make mediawiki happy
    $username = ucfirst($username);
    return $username;
  }
}
?>