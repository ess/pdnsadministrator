<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2011 Roger Libiez http://www.iguanadons.net
 *
 * Based on Quicksilver Forums
 * Copyright (c) 2005-2011 The Quicksilver Forums Development Team
 *  http://code.google.com/p/quicksilverforums/
 * 
 * Based on MercuryBoard
 * Copyright (c) 2001-2005 The Mercury Development Team
 *  http://www.mercuryboard.com/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 **/

if (!defined('PDNSADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

/**
 * English language module
 *
 * @author Roger Libiez [Samson] http://www.iguanadons.net
 * @since 1.0
 **/
class en
{
	function admin()
	{
		$this->admin_add_templates = 'Add HTML templates';
		$this->admin_add_user = 'Add a user';
		$this->admin_cp_denied = 'Access Denied';
		$this->admin_cp_warning = 'The Admin CP is disabled until you delete your <b>install</b> directory, as it poses a serious security risk.';
		$this->admin_create_group = 'Create a group';
		$this->admin_create_skin = 'Create a skin';
		$this->admin_db = 'Database';
		$this->admin_db_backup = 'Backup the database';
		$this->admin_db_conn = 'Edit connection settings';
		$this->admin_db_optimize = 'Optimize the database';
		$this->admin_db_query = 'Execute an SQL query';
		$this->admin_db_repair = 'Repair the database';
		$this->admin_db_restore = 'Restore a backup';
		$this->admin_delete_group = 'Delete a group';
		$this->admin_delete_template = 'Delete HTML template';
		$this->admin_delete_user = 'Delete a user';
		$this->admin_edit_group_name = 'Edit a group\'s name';
		$this->admin_edit_group_perms = 'Edit a group\'s permissions';
		$this->admin_edit_settings = 'Edit settings';
		$this->admin_edit_skin = 'Edit or delete a skin';
		$this->admin_edit_templates = 'Edit HTML templates';
		$this->admin_edit_user = 'Edit a user';
		$this->admin_edit_user_perms = 'Edit a user\'s permissions';
		$this->admin_export_skin = 'Export a skin';
		$this->admin_groups = 'Groups';
		$this->admin_heading = 'PDNS-Admin Administrative Control Panel';
		$this->admin_install_skin = 'Install a skin';
		$this->admin_logs = 'View logs';
		$this->admin_phpinfo = 'View PHP information';
		$this->admin_settings = 'Settings';
		$this->admin_settings_add = 'Add new setting';
		$this->admin_supermasters = 'Set domain supermasters';
		$this->admin_skins = 'Skins';
		$this->admin_upgrade_skin = 'Upgrade a Skin';
		$this->admin_users = 'Users';
		$this->admin_your_board = 'Your Console';
	}

	function backup()
	{
		$this->backup = 'Backup';
		$this->backup_add = 'Add';
		$this->backup_add_complete = 'Add complete';
		$this->backup_create = 'Backup Database';
		$this->backup_created = 'Backup successfully created in';
		$this->backup_createfile = 'Backup and create a file on server';
		$this->backup_done = 'The database has been backed up to the packages directory.';
		$this->backup_download = 'Backup and download (recommended)';
		$this->backup_failed = 'Failed to create backup.';
		$this->backup_found = 'The following backups were found in the packages directory';
		$this->backup_import_fail = 'Failed to import backup.';
		$this->backup_invalid = 'The backup does not appear to be valid. No changes were made to your database.';
		$this->backup_no_packages = 'Failed to locate packages directory.';
		$this->backup_noexist = 'Sorry, that backup does not exist.';
		$this->backup_none = 'No backups were found in the packages directory.';
		$this->backup_options = 'Database Backup Options';
		$this->backup_output = 'Output';
		$this->backup_restore = 'Restore Backup';
		$this->backup_restore_done = 'The database has been restored successfully.';
		$this->backup_statements = 'statements';
		$this->backup_uncheck = 'Unchecking this will NOT empty the database tables before restoring the backup!';
		$this->backup_warning = '<b>Warning:</b> This will overwrite all existing data used by PDNS-Admin.';
	}

	function cp()
	{
		$this->cp_already_user = 'The email address you entered is already assigned to a user.';
		$this->cp_been_updated = 'Your profile has been updated.';
		$this->cp_been_updated_prefs = 'Your preferences have been updated.';
		$this->cp_changing_pass = 'Editing Password';
		$this->cp_cp = 'Control Panel';
		$this->cp_editing_profile = 'Editing Profile';
		$this->cp_email = 'Email';
		$this->cp_email_invaid = 'The email address you entered is invalid.';
		$this->cp_err_updating = 'Error Updating Profile';
		$this->cp_header = 'User Control Panel';
		$this->cp_label_edit_pass = 'Edit Password';
		$this->cp_label_edit_prefs = 'Edit Preferences';
		$this->cp_label_edit_profile = 'Edit Profile';
		$this->cp_language = 'Language';
		$this->cp_login_first = 'You must be logged in to access your control panel.';
		$this->cp_new_notmatch = 'The new passwords you entered do not match.';
		$this->cp_new_pass = 'New Password';
		$this->cp_old_notmatch = 'The old password you entered does not match the one in our database.';
		$this->cp_old_pass = 'Old Password';
		$this->cp_pass_notvaid = 'Your password is not valid. Make sure it uses only valid characters such as letters, numbers, dashes, underscores, or spaces.';
		$this->cp_preferences = 'Changing Preferences';
		$this->cp_repeat_pass = 'Repeat New Password';
		$this->cp_skin = 'Skin';
		$this->cp_updated = 'Profile Updated';
		$this->cp_updated_prefs = 'Preferences Updated';
		$this->cp_valided = 'Your password was validated and changed.';
		$this->cp_welcome = 'Welcome to the user control panel. From here, you can configure your account. Please choose from the options above.';
	}

	function db_repair()
	{
		$this->repair_db = 'Repair Database';
		$this->repaired_db = 'The tables in the database have been repaired.';
	}

	function domains()
	{
		$this->domains = 'Domains';
		$this->domains_add_default_ns = 'Add default NS records';
		$this->domains_clone_create = 'Create New Domain Clone';
		$this->domains_clone_domain_to_clone = 'Domain to clone';
		$this->domains_delete = 'Delete Domain';
		$this->domains_delete_confirm = 'Are you sure you want to PERMANENTLY DELETE the domain';
		$this->domains_delete_not_permitted = 'You are not permitted to delete domains you do not own.';
		$this->domains_deleted = 'The domain has been successfully deleted.';
		$this->domains_delte_this = 'Delete this domain';
		$this->domains_edit = 'Edit Domain';
		$this->domains_edit_not_permitted = 'You are not permitted to edit domains you do not own.';
		$this->domains_exists = 'That domain name already exists.';
		$this->domains_id_invalid = 'A valid domain ID is required to edit.';
		$this->domains_info = 'Domain Info';
		$this->domains_invalid = 'The domain name entered was invalid!';
		$this->domains_invalid_cname = 'A CNAME record may not duplicate the contents of an MX or NS record.';
		$this->domains_invalid_mx = 'An MX record cannot point to a CNAME record.';
		$this->domains_invalid_mx2 = 'An MX record cannot point to the parent domain name.';
		$this->domains_invalid_ns = 'An NS record cannot point to a CNAME record.';
		$this->domains_invalid_ns2 = 'An NS record cannot point to the parent domain name.';
		$this->domains_invalid_ptr = 'You have entered an invalid PTR record.';
		$this->domains_ip_invalid = 'The IP address entered was invalid!';
		$this->domains_ip_required = 'An IP address is required.';
		$this->domains_login_first = 'You must be logged in to access the domain control panel.';
		$this->domains_master_ip_change = 'Change Master IP';
		$this->domains_master_ip_required = 'A Master IP is required for Slave domains.';
		$this->domains_new = 'Create New Domain';
		$this->domains_new_cant_create = 'You cannot create new domains.';
		$this->domains_new_created = 'New domain created.';
		$this->domains_new_ip = 'IP Address';
		$this->domains_new_cname_record = 'Add default CNAME record';
		$this->domains_new_master_ip = 'Master IP (Only for Slave domains)';
		$this->domains_new_mx_record = 'Add default MX record';
		$this->domains_new_ns_records = 'Add default NS records';
		$this->domains_new_name = 'Domain Name';
		$this->domains_new_reverse = 'Create New Reverse Domain';
		$this->domains_owner = 'Owner';
		$this->domains_owner_cant_change = 'You cannot change ownership of domains.';
		$this->domains_owner_change = 'Change Owner';
		$this->domains_owner_changed = 'Ownership has been changed.';
		$this->domains_record_add = 'Add Domain Record';
		$this->domains_record_added = 'Record Added.';
		$this->domains_record_content = 'Content';
		$this->domains_record_delete = 'Delete Domain Record';
		$this->domains_record_delete2 = 'Delete this record';
		$this->domains_record_delete_confirm = 'Are you sure you wish to delete the <b>%s</b> record <b>%s</b> containing <b>%s</b>?';
		$this->domains_record_delete_soa = 'You cannot delete the SOA record for a domain!';
		$this->domains_record_deleted = 'Record Deleted.';
		$this->domains_record_delete_wrong = 'That record is not part of this domain.';
		$this->domains_record_edit = 'Edit Domain Record';
		$this->domains_record_edit2 = 'Edit this record';
		$this->domains_record_edited = 'Record Edited.';
		$this->domains_record_new = 'New Record';
		$this->domains_record_new2 = 'Add new domain record';
		$this->domains_record_required = 'A domain record type is required.';
		$this->domains_record_required2 = 'A valid domain record must be chosen.';
		$this->domains_record_required3 = 'The name field cannot be left blank.';
		$this->domains_record_type = 'Record Type';
		$this->domains_record_wrong = 'Record does not match domain ID.';
		$this->domains_records = 'Domain Records';
		$this->domains_required = 'A domain name is required.';
		$this->domains_type = 'Domain Type';
		$this->domains_type_cant_change = 'You cannot change the domain type.';
		$this->domains_type_change = 'Change Domain Type';
		$this->domains_type_changed = 'Type has been changed.';
		$this->domains_type_required = 'A domain type of MASTER, SLAVE, or NATIVE is required.';
		$this->domains_unknown = 'You cannot do that to a domain?!';
		$this->domains_user_invalid = 'A valid user is required!';
		$this->domains_user_mismatch = 'User ID mismatch in new domain submission.';
		$this->domains_user_not_exist = 'The selected user does not exist.';
	}

	function groups()
	{
		$this->groups_based_on = 'based on';
		$this->groups_create = 'Create Group';
		$this->groups_create_new = 'Create a new user group named';
		$this->groups_created = 'Group Created';
		$this->groups_delete = 'Delete Group';
		$this->groups_deleted = 'Group Deleted.';
		$this->groups_edit = 'Edit Group';
		$this->groups_edited = 'Group Edited.';
		$this->groups_formatting = 'Display Formatting';
		$this->groups_i_confirm = 'I confirm that I want to delete this user group.';
		$this->groups_name = 'Group Name:';
		$this->groups_no_action = 'No action was taken.';
		$this->groups_no_delete = 'There are no custom groups to delete.<br />The core groups are necessary for PDNS-Admin to function, and cannot be deleted.';
		$this->groups_no_group = 'No group was specified.';
		$this->groups_no_name = 'No group name was given.';
		$this->groups_only_custom = 'Note: You can only delete custom user groups. The core groups are necessary for PDNS-Admin to function.';
		$this->groups_the = 'The group';
		$this->groups_to_edit = 'Group to edit';
		$this->groups_type = 'Group Type';
		$this->groups_will_be = 'will be deleted.';
		$this->groups_will_become = 'Users from the deleted group will become';
	}

	function home()
	{
		$this->home_choose = 'Choose a task to begin.';
		$this->home_menu_title = 'Admin CP Menu';
	}

	function login()
	{
		$this->login_cant_logged = 'You could not be logged in. Check to see that your user name and password are correct.<br /><br />They are case sensitive, so \'UsErNaMe\' is different from \'Username\'. Also, check to see that cookies are enabled in your browser.';
		$this->login_cookies = 'Cookies must be enabled to login.';
		$this->login_forgot_pass = 'Forgot your password?';
		$this->login_header = 'Logging In';
		$this->login_logged = 'You are now logged in.';
		$this->login_now_out = 'You are now logged out.';
		$this->login_out = 'Logging Out';
		$this->login_pass = 'Password';
		$this->login_pass_no_id = 'There is no user with the user name you entered.';
		$this->login_pass_request = 'To complete the password reset, please click on the link sent to the email address associated with your account.';
		$this->login_pass_reset = 'Reset Password';
		$this->login_pass_sent = 'Your password has been reset. The new password has been sent to the email address associated with your account.';
		$this->login_sure = 'Are you sure you wish to logoff from \'%s\'?';
		$this->login_user = 'User Name';
	}

	function logs()
	{
		$this->logs_action = 'Action';
		$this->logs_change_owner = 'Change domain owner';
		$this->logs_change_type = 'Change domain type';
		$this->logs_clone_domain = 'Cloned a domain';
		$this->logs_delete_a_record = 'Delete A record';
		$this->logs_delete_cname_record = 'Delete CNAME record';
		$this->logs_delete_domain = 'Delete domain';
		$this->logs_delete_loc_record = 'Delete LOC record';
		$this->logs_delete_mx_record = 'Delete MX record';
		$this->logs_delete_naptr_record = 'Delete NAPTR record';
		$this->logs_delete_ns_record = 'Delete NS record';
		$this->logs_delete_ptr_record = 'Delete PTR record';
		$this->logs_delete_spf_record = 'Delete SPF record';
		$this->logs_delete_srv_record = 'Delete SRV record';
		$this->logs_delete_txt_record = 'Delete TXT record';
		$this->logs_delete_url_record = 'Delete URL record';
		$this->logs_edit_a_record = 'Edit A record';
		$this->logs_edit_cname_record = 'Edit CNAME record';
		$this->logs_edit_loc_record = 'Edit LOC record';
		$this->logs_edit_mx_record = 'Edit MX record';
		$this->logs_edit_naptr_record = 'Edit NAPTR record';
		$this->logs_edit_ns_record = 'Edit NS record';
		$this->logs_edit_ptr_record = 'Edit PTR record';
		$this->logs_edit_soa_record = 'Edit SOA record';
		$this->logs_edit_spf_record = 'Edit SPF record';
		$this->logs_edit_srv_record = 'Edit SRV record';
		$this->logs_edit_txt_record = 'Edit TXT record';
		$this->logs_edit_url_record = 'Edit URL record';
		$this->logs_id = 'IDs';
		$this->logs_new_a_record = 'New A record';
		$this->logs_new_cname_record = 'New CNAME record';
		$this->logs_new_domain = 'New domain';
		$this->logs_new_loc_record = 'New LOC record';
		$this->logs_new_mx_record = 'New MX record';
		$this->logs_new_naptr_record = 'New NAPTR record';
		$this->logs_new_ns_record = 'New NS record';
		$this->logs_new_ptr_record = 'New PTR record';
		$this->logs_new_reverse_domain = 'New reverse domain';
		$this->logs_new_spf_record = 'New SPF record';
		$this->logs_new_srv_record = 'New SRV record';
		$this->logs_new_txt_record = 'New TXT record';
		$this->logs_new_url_record = 'New URL record';
		$this->logs_time = 'Time';
		$this->logs_user = 'User';
		$this->logs_view = 'View Logs';
	}

	function main()
	{
		$this->main_admincp = 'Admin CP';
		$this->main_banned = 'You have been banned from viewing any portion of this console.';
		$this->main_clone_forward = 'Clone Domain';
		$this->main_code = 'Code';
		$this->main_cp = 'User CP';
		$this->main_create_forward = 'Create New Domain';
		$this->main_create_reverse = 'Create New Reverse Domain';
		$this->main_delete_domain = 'Delete this domain';
		$this->main_domain_name = 'Domain Name';
		$this->main_domain_owner = 'Domain Owner';
		$this->main_domains = 'Domains';
		$this->main_edit_domain = 'Edit this domain';
		$this->main_full = 'Full';
		$this->main_load = 'load';
		$this->main_login = 'Login';
		$this->main_logout = 'Logout';
		$this->main_max_load = 'We are sorry, but %s is currently unavailable, due to a massive amount of connected users.';
		$this->main_new = 'new';
		$this->main_next = 'next';
		$this->main_prev = 'prev';
		$this->main_queries = 'queries';
		$this->main_records = 'Records';
		$this->main_search = 'Search Domains';
		$this->main_statistics = 'Statistics';
		$this->main_title = 'PowerDNS Administration Console';
		$this->main_users = 'Users';
		$this->main_welcome = 'Welcome';
		$this->main_welcome2 = 'Welcome to the PowerDNS Administration Console. Please log on. If you have not been provided with an account, you may not belong here.';
		$this->main_welcome_guest = 'Welcome!';
		$this->main_your_board = 'Your Console';
	}

	function optimize()
	{
		$this->optimize = 'Optimize Database';
		$this->optimized = 'The tables in the database have been optimized for maximum performance.';
	}

	function perms()
	{
		$this->perm = 'Permission';
		$this->perms = 'Permissions';
		$this->perms_create_domains = 'Can create new domains.';
		$this->perms_delete_domains = 'Can delete existing domains they do not own.';
		$this->perms_do_anything = 'Can access PDNS-Admin. Should never be unchecked!';
		$this->perms_edit_domains = 'Can edit existing domains they do not own.';
		$this->perms_edit_for = 'Edit permissions for';
		$this->perms_for = 'Permissions For';
		$this->perms_group = 'Group';
		$this->perms_guest1 = 'Removing Guest user access will prevent anyone from logging in!';
		$this->perms_guest2 = 'Removing Guest group access will prevent anyone from logging in!';
		$this->perms_is_admin = 'Can access the admin control panel. This is an all or nothing setting.';
		$this->perms_only_user = 'Use only group permissions for this user';
		$this->perms_override_user = 'This will override the group permissions for this user.';
		$this->perms_site_view = 'Can view the index page.';
		$this->perms_title = 'User Group Control';
		$this->perms_update = 'Update Permissions';
		$this->perms_updated = 'Permissions have been updated.';
		$this->perms_user = 'User';
		$this->perms_user_inherit = 'The user will inherit the group\'s permissions.';
	}

	function php_info()
	{
		$this->php_error = 'Error';
		$this->php_error_msg = 'phpinfo() can not be executed. It appears that your host has disabled this feature.';
	}

	function query()
	{
		$this->query = 'Query Interface';
		$this->query_fail = 'failed.';
		$this->query_success = 'executed successfully.';
		$this->query_your = 'Your query';
	}

	function settings()
	{
		$this->settings = 'Settings';
		$this->settings_allow = 'Allow';
		$this->settings_basic = 'Edit Settings';
		$this->settings_cookie = 'Cookie Settings';
		$this->settings_cookie_domain = 'Cookie Domain';
		$this->settings_cookie_path = 'Cookie Path';
		$this->settings_cookie_prefix = 'Cookie Prefix';
		$this->settings_cookie_secure = 'Cookie Security';
		$this->settings_cookie_secured = 'Is your site SSL secured?';
		$this->settings_cookie_time = 'Time to Remain Logged In';
		$this->settings_db = 'Edit Connection Settings';
		$this->settings_db_file_write = 'Unable to write new database settings. Please CHMOD the settings.php file to 0666 and try again.';
		$this->settings_db_host = 'Database Host';
		$this->settings_db_leave_blank = 'Leave blank for none.';
		$this->settings_db_name = 'Database Name';
		$this->settings_db_password = 'Database Password';
		$this->settings_db_port = 'Database Port';
		$this->settings_db_socket = 'Database Socket';
		$this->settings_db_username = 'Database Username';
		$this->settings_debug_mode = 'Template Debugging Mode';
		$this->settings_default_lang = 'Default Language';
		$this->settings_default_no = 'Default No';
		$this->settings_default_skin = 'Default Skin';
		$this->settings_default_yes = 'Default Yes';
		$this->settings_disabled = 'Disabled';
		$this->settings_domain = 'Domain Settings';
		$this->settings_domain_master = 'Master IP for SLAVE domains';
		$this->settings_domain_ns1 = 'Primary Nameserver';
		$this->settings_domain_ns2 = 'Secondary Nameserver';
		$this->settings_domain_ns3 = 'Tertiary Nameserver';
		$this->settings_domain_ns4 = 'Quaternary Nameserver';
		$this->settings_domain_ns5 = 'Quinary Nameserver';
		$this->settings_domain_ns6 = 'Senary Nameserver';
		$this->settings_domain_ns7 = 'Septenary Nameserver';
		$this->settings_domain_ns8 = 'Octonary Nameserver';
		$this->settings_domain_soa_refresh = 'Default SOA refresh value for new domains';
		$this->settings_domain_soa_retry = 'Default SOA retry value for new domains';
		$this->settings_domain_soa_expire = 'Default SOA expiry value for new domains';
		$this->settings_domain_ttl = 'Default TTL value for new domains and records';
		$this->settings_domains_per_page = 'Number of domains to display per page';
		$this->settings_email_fake = 'For display only. Should not be a real e-mail address.';
		$this->settings_email_from = 'E-mail From Address';
		$this->settings_email_real = 'Should be a real e-mail address.';
		$this->settings_email_reply = 'E-mail Reply-To Address';
		$this->settings_email_smtp = 'SMTP Mail Server';
		$this->settings_enabled = 'Enabled';
		$this->settings_general = 'General Settings';
		$this->settings_new = 'New Setting';
		$this->settings_new_add = 'Add New Setting';
		$this->settings_new_added = 'New settings added.';
		$this->settings_new_exists = 'That setting already exists. Choose another name for it.';
		$this->settings_new_name = 'New setting name';
		$this->settings_new_required = 'The new setting name is required.';
		$this->settings_new_value = 'New setting value';
		$this->settings_no_allow = 'Do Not Allow';
		$this->settings_nodata = 'No data was sent from POST';
		$this->settings_one_per = 'One per line';
		$this->settings_records_per_page = 'Number of records to display per page';
		$this->settings_server = 'Server Settings';
		$this->settings_server_maxload = 'Maximum Server Load';
		$this->settings_server_maxload_msg = 'Disables site under excessive server strain. Enter 0 to disable.';
		$this->settings_site_location = 'Site URL';
		$this->settings_site_name = 'Site Name';
		$this->settings_timezone = 'Server Timezone';
		$this->settings_updated = 'Settings have been updated.';
		$this->settings_users = 'User Settings';
	}

	function supermaster()
	{
		$this->supermaster_account = 'Account';
		$this->supermaster_add = 'Add Supermaster';
		$this->supermaster_added = 'Supermaster record has been added.';
		$this->supermaster_confirm_delete = 'Are you sure you wish to <b>PERMANENTLY DELETE</b> the supermaster record for this name server: ';
		$this->supermaster_delete = 'Delete Supermaster';
		$this->supermaster_deleted = 'Supermaster record deleted.';
		$this->supermaster_exists = 'A supermaster record with that name server already exists!';
		$this->supermaster_ip = 'IP Address';
		$this->supermaster_ip_invalid = 'The IP address supplied is not valid!';
		$this->supermaster_new_ip = 'IP of the master server sending the notifications';
		$this->supermaster_new_ns = 'Name server to look for from master notifications';
		$this->supermaster_ns = 'Name Server';
		$this->supermaster_ns_invalid = 'The name server supplied is not valid!';
		$this->supermaster_ns_unknown = 'There is no supermaster record matching that name server.';
		$this->supermasters = 'Domain Supermasters';
		$this->supermasters_none = 'There are no domain supermasters configured for this PDNS server.';
	}

	function templates()
	{
		$this->add = 'Add HTML Templates';
		$this->add_in = 'Add template to:';
		$this->all_fields_required = 'All fields are required to add a template';
		$this->choose_css = 'Choose CSS Template';
		$this->choose_set = 'Choose a template set';
		$this->choose_skin = 'Choose a skin';
		$this->confirm1 = 'You are about to delete the';
		$this->confirm2 = 'template from';
		$this->create_new = 'Create a new skin named';
		$this->create_skin = 'Create Skin';
		$this->credit = 'Please do not remove our only credit!';
		$this->css_edited = 'CSS file has been updated.';
		$this->css_fioerr = 'The file could not be written to, you will need to CHMOD the file manually.';
		$this->deleted = 'Template Deleted';
		$this->delete_template = 'Delete Template';
		$this->directory = 'Directory';
		$this->display_name = 'Display Name';
		$this->edit_css = 'Edit CSS';
		$this->edit_skin = 'Edit Skin';
		$this->edit_templates = 'Edit Templates';
		$this->export_done = 'Skin exported to the skins directory.';
		$this->export_select = 'Select a skin to export';
		$this->export_skin = 'Export Skin';
		$this->install_done = 'The skin has been installed successfully.';
		$this->install_exists1 = 'It appears that the skin';
		$this->install_exists2 = 'is already installed.';
		$this->install_overwrite = 'Overwrite';
		$this->install_skin = 'Install Skin';
		$this->menu_title = 'Select a template section to edit';
		$this->no_file = 'No such file.';
		$this->only_skin = 'There is only one skin installed. You may not delete this skin.';
		$this->or_new = 'Or create new template set named:';
		$this->select_skin = 'Select a Skin';
		$this->select_skin_edit = 'Select a skin to edit';
		$this->select_skin_edit_done = 'Skin successfully edited.';
		$this->select_template = 'Select Template';
		$this->skin_chmod = 'A new directory could not be created for the skin. Try to CHMOD the skins directory to 775.';
		$this->skin_created = 'Skin created.';
		$this->skin_deleted = 'Skin successfully deleted.';
		$this->skin_dir_name = 'You must enter a skin name and directory name.';
		$this->skin_dup = 'A skin with a duplicate directory name was found. The skin\'s directory was changed to';
		$this->skin_name = 'You must enter a skin name.';
		$this->skin_none = 'There are no skins available to install.';
		$this->skin_set = 'Skin Set';
		$this->skins_found = 'The following skins were found in the skins directory';
		$this->template_about = 'About Variables';
		$this->template_about2 = 'Variables are pieces of text that are replaced with dynamic data. Variables always begin with a dollar sign, and are sometimes enclosed in {braces}.';
		$this->template_add = 'Add';
		$this->template_added = 'Template added.';
		$this->template_clear = 'Clear';
		$this->template_confirm = 'You have made changes to the templates. Do you want to save your changes?';
		$this->template_description = 'Template Description';
		$this->template_html = 'Template HTML';
		$this->template_name = 'Template Name';
		$this->template_position = 'Template Position';
		$this->template_set = 'Template Set';
		$this->template_title = 'Template Title';
		$this->template_universal = 'Universal Variable';
		$this->template_universal2 = 'Some variables can be used in any template, while others can only be used in a single template. Properties of the object $this can be used anywhere.';
		$this->template_updated = 'Template updated.';
		$this->templates = 'Templates';
		$this->temps_admin = '<b>AdminCP Universal</b>';
		$this->temps_backup = 'AdminCP Database Backup';
		$this->temps_cp = 'User Control Panel';
		$this->temps_email = 'Email A User';
		$this->temps_groups = 'AdminCP Groups';
		$this->temps_login = 'Logging In/Out';
		$this->temps_logs = 'AdminCP Moderator Logs';
		$this->temps_main = '<b>Site Universal</b>';
		$this->temps_settings = 'AdminCP Settings';
		$this->temps_supermasters = 'Supermasters';
		$this->temps_templates = 'AdminCP Template Editor';
		$this->temps_user_control = 'AdminCP User Control';
		$this->temps_users = 'Users List';
		$this->upgrade_skin = 'Upgrade Skin';
		$this->upgrade_skin_already = 'was already upgraded. Nothing to do.';
		$this->upgrade_skin_detail = 'Skins upgraded using this method will still require template editing afterwards.<br />Select a skin to upgrade';
		$this->upgrade_skin_upgraded = 'skin has been upgraded.';
		$this->upgraded_templates = 'The following templates were added or upgraded';
	}

	function universal()
	{
		$this->based_on = 'Based on';
		$this->board_by = 'By';
		$this->charset = 'utf-8';
		$this->content = 'Content';
		$this->continue = 'Continue';
		$this->date_long = 'M j, Y';
		$this->date_short = 'n/j/y';
		$this->delete = 'Delete';
		$this->delete_selected = 'Delete Selected';
		$this->direction = 'ltr';
		$this->edit = 'Edit';
		$this->email = 'Email';
		$this->gmt = '[GMT] Greenwich Mean Time';
		$this->gmt_nev1 = '[GMT-1:00] Azores, Cape Verde';
		$this->gmt_nev10 = '[GMT-10:00] Hawaii, Aleutian Islands';
		$this->gmt_nev11 = '[GMT-11:00] Midway Island, Samoa';
		$this->gmt_nev12 = '[GMT-12:00] International Date Line West';
		$this->gmt_nev2 = '[GMT-2:00] Mid-Atlantic';
		$this->gmt_nev3 = '[GMT-3:00] Buenos Aires, Greenland';
		$this->gmt_nev35 = '[GMT-3:30] Newfoundland';
		$this->gmt_nev4 = '[GMT-4:00] Atlantic Time Canada';
		$this->gmt_nev5 = '[GMT-5:00] Eastern Time US & Canada';
		$this->gmt_nev6 = '[GMT-6:00] Central Time US & Canada';
		$this->gmt_nev7 = '[GMT-7:00] Mountain Time US & Canada';
		$this->gmt_nev8 = '[GMT-8:00] Pacific Time US & Canada';
		$this->gmt_nev9 = '[GMT-9:00] Alaska';
		$this->gmt_pos1 = '[GMT+1:00] Amsterdam, Berlin, Rome, Paris';
		$this->gmt_pos10 = '[GMT+10:00] Melbourne, Sydney, Guam';
		$this->gmt_pos11 = '[GMT+11:00] Magadan, New Caledonia';
		$this->gmt_pos12 = '[GMT+12:00] Auckland, Fiji';
		$this->gmt_pos2 = '[GMT+2:00] Athens, Cairo, Jerusalem';
		$this->gmt_pos3 = '[GMT+3:00] Baghdad, Moscow, Nairobi';
		$this->gmt_pos35 = '[GMT+3:30] Tehran';
		$this->gmt_pos4 = '[GMT+4:00] Abu Dhabi, Muscat, Tbilisi';
		$this->gmt_pos45 = '[GMT+4:30] Kabul';
		$this->gmt_pos5 = '[GMT+5:00] Islamabad, Karachi';
		$this->gmt_pos55 = '[GMT+5:30] Bombay, Calcutta, New Delhi';
		$this->gmt_pos6 = '[GMT+6:00] Almaty, Dhaka';
		$this->gmt_pos7 = '[GMT+7:00] Bangkok, Jakarta';
		$this->gmt_pos8 = '[GMT+8:00] Beijing, Hong Kong, Singapore';
		$this->gmt_pos9 = '[GMT+9:00] Tokyo, Seoul';
		$this->gmt_pos95 = '[GMT+9:30] Adelaide, Darwin';
		$this->invalid_token = 'The security validation token used to verify you are authorized to perform this action is either invalid or expired. Please try again.';
		$this->master = 'Master';
		$this->name = 'Name';
		$this->no = 'No';
		$this->powered = 'Powered by';
		$this->priority = 'Priority';
		$this->seconds = 'Seconds';
		$this->select_all = 'Select All';
		$this->sep_decimals = '.';
		$this->sep_thousands = ',';
		$this->slave = 'Slave';
		$this->submit = 'Submit';
		$this->time_long = ', g:i a';
		$this->time_only = 'g:i a';
		$this->today = 'Today';
		$this->type = 'Type';
		$this->unselect_all = 'Unselect All';
		$this->yes = 'Yes';
		$this->yesterday = 'Yesterday';
	}

	function user_control()
	{
		$this->mc = 'User Control';
		$this->mc_add = 'Add User';
		$this->mc_confirm = 'Are you sure you want to delete';
		$this->mc_delete = 'Delete User';
		$this->mc_deleted = 'User Deleted.';
		$this->mc_edit = 'Edit User';
		$this->mc_edited = 'User Updated';
		$this->mc_email_invaid = 'The email address you entered is invalid.';
		$this->mc_err_updating = 'Error Updating Profile';
		$this->mc_find = 'Find users with names containing';
		$this->mc_found = 'The following users were found. Please select one.';
		$this->mc_not_found = 'No users were found matching';
		$this->mc_user_created = 'Account Created';
		$this->mc_user_email = 'Email Address';
		$this->mc_user_email_required = 'An email address is required!';
		$this->mc_user_group = 'Group';
		$this->mc_user_id = 'User ID';
		$this->mc_user_language = 'Language';
		$this->mc_user_lastlogon = 'Last Logon';
		$this->mc_user_name = 'Name';
		$this->mc_user_name_exists = 'A user named %s already exists.';
		$this->mc_user_name_required = 'A user name is required!';
		$this->mc_user_new = 'User added. New password has been sent via email.';
		$this->mc_user_skin = 'Skin';
	}

	function users()
	{
		$this->users_action_forbidden = 'Action Not Allowed';
		$this->users_all = 'all';
		$this->users_contact = 'Contact Information';
		$this->users_created = 'Created';
		$this->users_domains = 'Domains';
		$this->users_email_address = 'Email Address';
		$this->users_group = 'Group';
		$this->users_list = 'User List';
		$this->users_no_domains = 'This user has no domains.';
		$this->users_not_allowed = 'You are not permitted to perform that action.';
		$this->users_owns_domains = 'This user owns %d domains.';
		$this->users_profile = 'User profile for';
		$this->users_user = 'User';
	}
}
?>