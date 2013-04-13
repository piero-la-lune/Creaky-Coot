<?php

class Trad {

		# Mots

	const W_SECONDE = 'seconde';
	const W_MINUTE = 'minute';
	const W_HOUR = 'heure';
	const W_DAY = 'jour';
	const W_WEEK = 'semaine';
	const W_MONTH = 'mois';
	const W_YEAR = 'année';
	const W_DECADE = 'décennie';
	const W_SECONDE_P = 'secondes';
	const W_MINUTE_P = 'minutes';
	const W_HOUR_P = 'heures';
	const W_DAY_P = 'jours';
	const W_WEEK_P = 'semaines';
	const W_MONTH_P = 'mois';
	const W_YEAR_P = 'années';
	const W_DECADE_P = 'décennies';
	const W_EMPTY = 'aucun';
	const W_ENABLED = 'Activé';
	const W_DISABLED = 'Désactivé';

	const W_SUSPENSION = '…';
	const W_EXTRACT = '« %text% »';

		# Phrases

	const S_AGO = 'il y a %duration% %pediod%';
	const S_PUBLISHED = 'Sauvegardé %time% depuis %url%.';
	const S_NOTFOUND = 'La page que vous recherchez n\'existe pas...';
	const S_NO_LINK = 'Aucun article non lu...';
	const S_LOAD_MORE = 'Charger plus d\'articles...';
	const S_NO_MORE_LINK = 'Il n\'y a plus d\'articles à afficher...';
	const S_ADD_POPUP = 'Favori pour sauvegarder des articles...';
	const S_FILTER_TAG = 'Filtrage par tag : %tag%.';
	const S_FILTER_FEED = 'Filtrage par flux : %feed%.';
	const S_FILTER_SEARCH = 'Résultats de la recherche : %q%.';

		# Verbes

	const V_LOGIN = 'Se connecter';
	const V_CONTINUE = 'Continuer';
	const V_ADD = 'Ajouter';
	const V_EDIT = 'Modifier';
	const V_LINK = 'Article original';
	const V_MARK_READ = 'Marquer lu';
	const V_MARK_UNREAD = 'Marquer non lu';
	const V_ARCHIVE = 'Archiver';
	const V_DELETE = 'Supprimer';
	const V_REFRESH = 'Actualiser';
	const V_MARK_READ_ALL = 'Tout marquer lu';
	const V_CLEAR = 'Nettoyer';
	const V_IMPORT = 'Importer';
	const V_EXPORT = 'Exporter';
	const V_SAVE = 'Enregistrer les modifications';
	const V_CANCEL = 'Annuler';
	const V_SEARCH = 'Rechercher';

		# Forms

	const F_USERNAME = 'Nom d\'utilisateur :';
	const F_PASSWORD = 'Mot de passe :';
	const F_TITLE = 'Titre :';
	const F_URL = 'URL :';
	const F_FEED_URL = 'URL du flux :';
	const F_LINK = 'Articles tirés de :';
	const F_LINKS_PER_PAGE = 'Nombre d\'articles par page :';
	const F_URL_REWRITING = 'URL rewriting';
	const F_OPML_FILE = 'Fichier OPML :';
	const F_COOKIE = 'Type de connexion :';
	const F_COOKIE_FALSE = 'Ordinateur public';
	const F_COOKIE_TRUE = 'Ordinateur privé (rester connecté)';
	const F_COMMENT = 'Commentaire :';
	const F_TAGS = 'Tags :';
	const F_KEY_WORDS = 'Mots clés :';
	const F_AUTO_TAG = 'Tags automatiques :';

	const F_TIP_PASSWORD = 'Laissez vide pour ne pas le changer.';
	const F_TIP_URL_REWRITING = 'Laissez vide pour désactiver l\'URL rewriting. Sinon, indiquez le chemin du dossier de Creaky Coot (en commençant et terminant par un "/") par rapport au nom de domaine.';
	const F_TIP_AUTO_TAG = 'Les articles provenant d\'un flux recevront automatiquement le nom de ce flux comme tag.';

		# Titres

	const T_404 = 'Erreur 404 – Page non trouvée';
	const T_CONNEXION = 'Connexion';
	const T_INSTALLATION = 'Installation';
	const T_UNREAD = 'Non lus';
	const T_ALL = 'Tous';
	const T_FEEDS = 'Flux suivis';
	const T_ADD_FEED = 'Ajouter un flux';
	const T_SETTINGS = 'Préférences';
	const T_LOGOUT = 'Déconnexion';
	const T_IMPORT_OPML = 'Importer';
	const T_EXPORT_OPML = 'Exporter';
	const T_ADD = 'Sauvegarder cette page';
	const T_TAGS = 'Tags';
	const T_SEARCH = 'Recherche';
	const T_FILTER = 'Filtres';

		# Alertes

	const A_ERROR_LOGIN = 'Mauvais nom d\'utilisateur ou mot de passe.';
	const A_ERROR_LOGIN_WAIT = 'Merci de patienter %duration% %period% avant de réessayer. Ceci est une protection contre les attaques malveillantes.';
	const A_ERROR_UNKNOWN_FEED = 'Ce flux n\'existe pas.';
	const A_ERROR_BAD_FEED = 'Impossible de lire le flux. Êtes-vous sûr de l\'URL ?';
	const A_ERROR_EXISTING_FEED = 'Ce flux est déjà suivi.';
	const A_ERROR_FORM = 'Merci de remplir tous les champs.';
	const A_ERROR_UPLOAD = 'Une erreur s\'est produite lors de la réception du fichier. Merci de réessayer.';
	const A_ERROR_IMPORT = 'Le fichier n\'a pas pu être lu. Êtes-vous certain que c\'est un fichier OPML ?';
	const A_ERROR_BAD_LINK = 'Impossible de lire l\'article. Êtes-vous sûr de l\'URL ?';
	const A_ERROR_EXISTING_LINK = 'Cet article est déjà sauvegardé.';
	const A_ERROR_AJAX = 'Une erreur est survenue. Merci de réessayer.';

	const A_SUCCESS_INSTALL = 'Creaky Coot est maintenant correctement installé. Connectez-vous pour commencer à l\'utiliser.';
	const A_SUCCESS_ADD_FEED = 'Le flux a bien été ajouté.';
	const A_SUCCESS_EDIT_FEED = 'Le flux a bien été modifié.';
	const A_SUCCESS_SETTINGS = 'Les préférences ont bien été enregistrées.';
	const A_SUCCESS_IMPORT = 'Les flux ont bien été ajoutés.';
	const A_SUCCESS_ADD = 'L\'article a bien été sauvegardé.';

	const A_CONFIRM_DELETE_LINK = 'Supprimer définitivement cet article ?';
	const A_CONFIRM_DELETE_FEED = 'Tous les articles non archivés de ce flux seront effacés. Voulez-vous vraiment continuer ?';
	const A_CONFIRM_CLEAR = 'Tous les articles non archivés de cette page seront effacés. Voulez-vous vraiment continuer ?';
	const A_CONFIRM_CLEAR_FEED = 'Tous les articles non archivés de ce flux seront effacés. Voulez-vous vraiment continuer ?';

	const A_ADD_POPUP = 'Glissez-déposez ce lien sur votre barre de favoris, ou choisissez « Ajouter aux favoris... » après un clique-droit sur ce lien.';

	public static $settings = array(
		'validate_url' => 'L\'url n\'est pas valide.'
	);

}

?>