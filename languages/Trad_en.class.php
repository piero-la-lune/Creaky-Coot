<?php

class Trad {

		# Mots

	const W_UNREAD = 'unread';
	const W_READ = 'read';
	const W_ARCHIVED = 'archived';

	const W_SECONDE = 'second';
	const W_MINUTE = 'minute';
	const W_HOUR = 'hour';
	const W_DAY = 'day';
	const W_WEEK = 'week';
	const W_MONTH = 'month';
	const W_YEAR = 'year';
	const W_DECADE = 'decade';
	const W_SECONDE_P = 'seconds';
	const W_MINUTE_P = 'minutes';
	const W_HOUR_P = 'hours';
	const W_DAY_P = 'days';
	const W_WEEK_P = 'weeks';
	const W_MONTH_P = 'months';
	const W_YEAR_P = 'years';
	const W_DECADE_P = 'decade';
	const W_EMPTY = 'none';
	const W_ENABLED = 'Enabled';
	const W_DISABLED = 'Disabled';

	const W_SUSPENSION = '…';
	const W_EXTRACT = '“ %text% ”';

	const W_F_REMOVE = 'Remove formatting';
	const W_F_BOLD = 'Bold';
	const W_F_ITALIC = 'Italic';
	const W_F_UNDERLINE = 'Underline';
	const W_F_P = 'Paragraph';
	const W_F_H2 = 'Title 1st level';
	const W_F_H3 = 'Title 2nd level';
	const W_F_H4 = 'Title 3rd level';
	const W_F_PRE = 'Code';
	const W_F_QUOTE = 'Quote';
	const W_F_LEFT = 'Left';
	const W_F_CENTER = 'Center';
	const W_F_RIGHT = 'Right';
	const W_F_JUSTIFY = 'Justify';
	const W_F_LISTU = 'Unordered list';
	const W_F_LISTO = 'Ordered list';
	const W_F_LINK = 'Link';
	const W_F_IMAGE = 'Image';

		# Phrases

	const S_AGO = '%duration% %pediod% ago';
	const S_PUBLISHED = 'Taken from %url% %time%.';
	const S_NOTFOUND = 'The page you are looking for does not exist…';
	const S_NO_LINK = 'No unread article…';
	const S_LOAD_MORE = 'Load more articles…';
	const S_NO_MORE_LINK = 'There are no more article…';
	const S_ADD_POPUP = 'Bookmark to save articles…';
	const S_FILTER_TAG = 'Tag filtering : %tag%.';
	const S_FILTER_FEED = 'Feed filtering : %feed%.';
	const S_FILTER_TYPE = 'Only %type% articles.';
	const S_FILTER_SEARCH = 'Search results : %q%.';

		# Verbes

	const V_LOGIN = 'Log in';
	const V_CONTINUE = 'Continue';
	const V_ADD = 'Add';
	const V_EDIT = 'Edit';
	const V_LINK = 'Original article';
	const V_MARK_READ = 'Mark read';
	const V_MARK_UNREAD = 'Mark unread';
	const V_ARCHIVE = 'Archive';
	const V_DELETE = 'Delete';
	const V_REFRESH = 'Refresh';
	const V_MARK_READ_ALL = 'Mark all read';
	const V_CLEAR = 'Clear';
	const V_IMPORT = 'Import';
	const V_EXPORT = 'Export';
	const V_SAVE = 'Save changes';
	const V_CANCEL = 'Cancel';
	const V_SEARCH = 'Search';
	const V_CONFIGURE = 'Configure';

		# Forms

	const F_USERNAME = 'Username:';
	const F_PASSWORD = 'Password:';
	const F_TITLE = 'Title:';
	const F_URL = 'URL:';
	const F_FEED_URL = 'Feed URL:';
	const F_LINK = 'Articles taken from:';
	const F_P_CONTENT = 'Content:';
	const F_P_COMMENT = 'Comment:';
	const F_P_EMPTY = 'Empty';
	const F_P_RSS = 'Feed';
	const F_P_DLOAD = 'URL specified by the fedd';
	const F_FILTER_HTML = 'HTML filtering rules:';
	const F_LINKS_PER_PAGE = 'Articles per page:';
	const F_URL_REWRITING = 'URL rewriting:';
	const F_OPML_FILE = 'OPML file:';
	const F_COOKIE = 'Security:';
	const F_COOKIE_FALSE = 'Public computer';
	const F_COOKIE_TRUE = 'Private computer (stay logged)';
	const F_COMMENT = 'Comments:';
	const F_TAGS = 'Tags:';
	const F_KEY_WORDS = 'Keywords:';
	const F_AUTO_TAG = 'Automatic tagging:';
	const F_TWITTER_URL = 'Tweets URL:';
	const F_PARAMS = 'Parameters:';
	const F_OPEN_NEW_TAB = 'Open the articles in a new tab:';
	const F_LANGUAGE = 'Language:';
	const F_ADD = 'add…';

	const F_TIP_PASSWORD = 'Leave it empty if you don\'t want to change it.';
	const F_TIP_URL_REWRITING = 'Leave this field empty to disable URL rewriting. Otherwise, it should contain the path to the Creaky Coot folder (started and ended with a "/"), relative to the domain name.';
	const F_TIP_AUTO_TAG = 'Articles from a feed will be automatically tagged with the name of this feed.';
	const F_TIP_PARAMS = 'Example : « q=keywords,count=4 ».';
	const F_TIP_TWITTER = '<p>Example of use:</p>
		<ul>
			<li>“statuses/home_timeline” and nothing</li>
			<li>“statuses/user_timeline” and “screen_name=piero_la_lune”</li>
			<li>“search/tweets” and “q=Creaky Coot”</li>
		</ul>
	';
	const F_TIP_FILTER_HTML = 'When the content is taken from the URL given by the feed, the tags whose attributes “class” or “id” contain on of these word will be entirely removed. A “-” before a word will authorize it (some are blacklisted by default). Use a coma to separate different words.';
	const F_TIP_OPEN_NEW_TAB = 'Only links in article lists will be impacted.';

		# Titres

	const T_404 = 'Error 404 – Not Found';
	const T_LOGIN = 'Log in';
	const T_LOGOUT = 'Log out';
	const T_INSTALLATION = 'Setup';
	const T_UNREAD = 'Unread';
	const T_ALL = 'All';
	const T_FEEDS = 'Feeds';
	const T_ADD_FEED = 'Add a feed';
	const T_SETTINGS = 'Preferences';
	const T_IMPORT_OPML = 'Import';
	const T_EXPORT_OPML = 'Export';
	const T_ADD = 'Save this page';
	const T_TAGS = 'Tags';
	const T_SEARCH = 'Search';
	const T_ARTICLES = 'Articles';
	const T_FILTER = 'Filters';
	const T_TWITTER = 'Twitter';
	const T_NEW = 'New';
	const T_GLOBAL_SETTINGS = 'Global settings';
	const T_ARTICLES_SETTINGS = 'Articles';
	const T_USER_SETTINGS = 'User';

		# Alertes

	const A_ERROR_LOGIN = 'Wrong username or password.';
	const A_ERROR_LOGIN_WAIT = 'Please wait %duration% %period% before trying again. This is a protection against malicious attacks..';
	const A_ERROR_UNKNOWN_FEED = 'This feed does\'nt exist.';
	const A_ERROR_BAD_FEED = 'Unable to read the feed. Are you sure the URL is correct?';
	const A_ERROR_EXISTING_FEED = 'You are already following this feed.';
	const A_ERROR_FORM = 'Please fill all the fields.';
	const A_ERROR_UPLOAD = 'An error occurred while reading the file. Please try again.';
	const A_ERROR_IMPORT = 'Unable to read the file. Are you sure this is a correct OPML file?';
	const A_ERROR_BAD_LINK = 'Unable to read the article. Are you sure the URL is correct?';
	const A_ERROR_EXISTING_LINK = 'This article is already in the database.';
	const A_ERROR_AJAX = 'An error occurred. Please try again.';
	const A_ERROR_AJAX_LOGIN = 'You are not logged. Refresh this page, log in, then try again.';
	const A_ERROR_TWITTER = 'An error occurrend during the dialogue with Twitter. Please try again.';

	const A_SUCCESS_INSTALL = 'Creaky Coot is now completely configured. Log in to start using it.';
	const A_SUCCESS_ADD_FEED = 'The feed was added.';
	const A_SUCCESS_EDIT_FEED = 'The feed was updated.';
	const A_SUCCESS_SETTINGS = 'The preferences were updated.';
	const A_SUCCESS_IMPORT = 'The feeds were added.';
	const A_SUCCESS_ADD = 'The article was saved.';
	const A_SUCCESS_TWITTER = 'The access to Twitter is now configured.';

	const A_CONFIRM_DELETE_LINK = 'Permanently delete this article?';
	const A_CONFIRM_DELETE_FEED = 'All unarchived articles of this feed will be deleted. Are you sure you want to continue?';
	const A_CONFIRM_CLEAR = 'All unarchived articles displayed on this page will be deleted. Are you sure you want to continue?';
	const A_CONFIRM_CLEAR_FEED = 'All unarchived articles of this feed will be deleted. Are you sure you want to continue?';

	const A_ADD_POPUP = 'Drag and drop this link on your bookmarks bar';

	const A_ENTER_URL = 'Enter the URL :';

	public static $settings = array(
		'validate_url' => 'The URL is not valid.'
	);

}

?>