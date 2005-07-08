<?php

//
// Si une version de SPIP est deja installee, exiger l'authentification FTP
//

if (file_exists("ecrire/inc_connect.php3") AND file_exists("ecrire/unpack.php3")) {
	include ("ecrire/inc_version.php3");

	include ("ecrire/inc_connect.php3");
	include ("ecrire/inc_meta.php3");
	include ("ecrire/inc_admin.php3");

	$ok = true;
	if (!$id_auteur) $ok = false;
	else if (!verifier_action_auteur("unpack", $hash, $id_auteur)) $ok = false;
	if (!$ok) {
		@header("Location: ecrire/unpack.php3");
		exit;
	}
	$charger = 'oui';
}
else {
	function feed_globals($table) {
		if (is_array($GLOBALS[$table])) {
		        reset($GLOBALS[$table]);
		        while (list($key, $val) = each($GLOBALS[$table])) {
		                $GLOBALS[$key] = $val;
		        }
		}
	}

	feed_globals('HTTP_GET_VARS');
	feed_globals('HTTP_POST_VARS');
	feed_globals('HTTP_COOKIE_VARS');
	feed_globals('HTTP_SERVER_VARS');
}


function debut_html($titre = "Installation du syst&egrave;me de publication...") {
	?>
	<HTML>

	<HTML>
	<HEAD>
	<TITLE><?php echo $titre; ?></TITLE>
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="cache-control" CONTENT="no-cache,no-store">
	<META HTTP-EQUIV="pragma" CONTENT="no-cache">
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">


	<style>
	<!--
		a {text-decoration: none; }
		A:Hover {color:#FF9900; text-decoration: underline;}
		.forml {width: 100%; background-color: #FFCC66; background-position: center bottom; float: none; color: #000000}
		.formo {width: 100%; background-color: #970038; background-position: center bottom; float: none; color: #FFFFFF}
		.fondl {background-color: #FFCC66; background-position: center bottom; float: none; color: #000000}
		.fondo {background-color: #970038; background-position: center bottom; float: none; color: #FFFFFF}
		.fondf {background-color: #FFFFFF; border-style: solid ; border-width: 1; border-color: #E86519; color: #E86519}
	-->
	</style>
	</HEAD>

	<body bgcolor="#FFFFFF" text="#000000" link="#E86519" vlink="#6E003A" alink="#FF9900" TOPMARGIN="0" LEFTMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0">

	<BR><BR><BR>
	<CENTER>
	<TABLE WIDTH=450>
	<TR><TD WIDTH=450>
	<FONT FACE="Verdana,Arial,Helvetica,sans-serif" SIZE=4 COLOR="#970038"><B><?php
		echo $titre;
	?></B></FONT>
	<FONT FACE="Georgia,Garamond,Times,serif" SIZE=3>
	<?php
}


function fin_html() {
	?>
	</FONT>
	</TD></TR></TABLE>
	</CENTER>
	</BODY>

	</HTML>
	<?php
}


function lire_short($f) {
	global $_fread;
	$bin = $_fread($f, 2);
	$res = unpack('na', $bin);
	return $res['a'];
}

function lire_long($f) {
	global $_fread;
	$bin = $_fread($f, 4);
	$res = unpack('Na', $bin);
	return $res['a'];
}

function lire_chaine($f) {
	global $_fread;
	$n = lire_long($f);
	if (!$n) return '';
	return $_fread($f, $n);
}

// Tester si le fichier est un fichier de configuration
// modifiable par le webmestre (squelettes, etc)

function test_fichier_config($fichier) {
	// Fichiers graphiques modifiables (racine du site)
	if (ereg("^[^/]*\.(gif|jpe?g|png)$", $fichier)) return true;

	// Fichiers .php3 dans le repertoire de base
	if (ereg("^([^/]*)\.php3$", $fichier, $regs)) {
		$racine = $regs[1];
		if ($racine == 'inc-urls') return true;
		if (ereg("^(spip_|inc-)", $racine)) return false;
		return true;
	}

	return false;
}

function unpacker_fichier($f) {
	global $dir_base;
	global $_fread;
	global $chmod;

	$chaine = $_fread($f, 4);
	if ($chaine != 'spip') {
		return false;
	}
	$chmod_dir = $chmod;
	$chmod_file = $chmod & ~0111;
	while (($fichier = lire_chaine($f)) != '') {
		$n = lire_long($f);
		$chemin = "$dir_base$fichier";
		if (!$n) {
			@mkdir($chemin, $chmod_dir);
			@chmod($chemin, $chmod_dir);
		}
		else {
			$bin = $_fread($f, $n);
			if (!(test_fichier_config($fichier) AND file_exists($chemin))) {
				$dest = fopen($chemin, "wb");
				fwrite($dest, $bin, $n);
				fclose($dest);
				@chmod($chemin, $chmod_file);
			}
		}
	}
	return true;
}


//
// Verifier si la ZLib est utilisable
//

$gz = function_exists("gzopen");
if ($gz) {
	$_fwrite = gzwrite;
	$_fread = gzread;
	$_fopen = gzopen;
	$_fclose = gzclose;
}
else {
	$_fwrite = fwrite;
	$_fread = fread;
	$_fopen = fopen;
	$_fclose = fclose;
}


//
// Si le fichier est deja charge, le decompacter
//

if ($fichier AND file_exists($fichier)) {
	$dir_base = "";
	$f = $_fopen($fichier, "rb");
	$ok = unpacker_fichier($f);
	$_fclose($f);
	@unlink($fichier);
	if (!$ok) {
		die ("<h4>Donn&eacute;es incorrectes. Veuillez r&eacute;essayer, ou utiliser l'installation manuelle.</h4>");
	}
	header("Location: ".$dir_base."ecrire/");
	exit;
}


//
// Si pas encore fait, afficher la page de presentation
//

if ($charger != 'oui') {
	debut_html("T&eacute;l&eacute;chargement de SPIP");

	echo "<P><B>Bienvenue dans la proc&eacute;dure d'installation automatique de SPIP.</B>";
	echo "<P>Le syst&egrave;me va d'abord v&eacute;rifier les droits d'acc&egrave;s au r&eacute;pertoire courant, ";
	echo "puis lancer le t&eacute;l&eacute;chargement des donn&eacute;es SPIP &agrave; l'int&eacute;rieur de ce r&eacute;pertoire.";
	echo "<P>Veuillez appuyer sur le bouton suivant pour continuer.";
	echo "<DIV ALIGN='right'>";
	echo "<FORM ACTION='spip_loader.php3' METHOD='get'>";
	echo "<INPUT TYPE='hidden' NAME='charger' VALUE='oui'>";
	echo "<INPUT TYPE='submit' NAME='Valider' VALUE=\"Commencer l'installation >>\">";
	echo "</FORM>";

	fin_html();
	exit;
}


//
// Gestion des droits d'acces
//

$ok = false;
$self = basename($PHP_SELF);
$uid = @fileowner('.');
$uid2 = @fileowner($self);
$gid = @filegroup('.');
$gid2 = @filegroup($self);
$perms = @fileperms($self);

// Comparer l'appartenance d'un fichier cree par PHP
// avec celle du script et du repertoire courant
@rmdir('test');
@unlink('test'); // effacer au cas ou
@touch('test');
if ($uid > 0 && $uid == $uid2 && @fileowner('test') == $uid)
	$chmod = 0700;
else if ($gid > 0 && $gid == $gid2 && @filegroup('test') == $gid)
	$chmod = 0770;
else
	$chmod = 0777;
// Appliquer de plus les droits d'acces du script
if ($perms > 0) {
	$perms = ($perms & 0777) | (($perms & 0444) >> 2);
	$chmod |= $perms;
}
@unlink('test');

//echo "uids: $uid, $uid2<br>gids: $gid, $gid2<br>chmod: ".(($chmod & (7 << 9)) >> 9).(($chmod & (7 << 6)) >> 6).(($chmod & (7 << 3)) >> 3).($chmod & 7)."<br>";

// Verifier que les valeurs sont correctes

@mkdir('test', $chmod);
@chmod('test', $chmod);
$f = @fopen('test/test.php', 'w');
if ($f) {
	@fputs($f, '<?php $ok = true; ?>');
	@fclose($f);
	@chmod('test/test.php', $chmod);
	include('test/test.php');
}
@unlink('test/test.php');
@rmdir('test');

if (!$ok) {
	debut_html("T&eacute;l&eacute;chargement de SPIP");

	echo "<BR><FONT FACE=\"Verdana,Arial,Helvetica,sans-serif\" SIZE=3>Pr&eacute;liminaire : ";
	echo "<B>R&eacute;gler les droits d'acc&egrave;s</B></FONT>";
	echo "<P><B>Le r&eacute;pertoire courant n'est pas accessible en &eacute;criture.</B>";
	echo "<P>Pour y rem&eacute;dier, utilisez votre client FTP afin de r&eacute;gler les droits d'acc&egrave;s ";
	echo "&agrave; ce r&eacute;pertoire (r&eacute;pertoire d'installation de SPIP). ";
	echo "La proc&eacute;dure est expliqu&eacute;e en d&eacute;tail dans le guide d'installation. Au choix&nbsp;:<BR>";
	echo "<UL>";
	echo "<LI><B>Si vous avez un client FTP graphique</B>, r&eacute;glez les propri&eacute;t&eacute;s du répertoire courant ";
	echo "afin qu'il soit accessible en écriture pour tous.<P>";
	echo "<LI><B>Si votre client FTP est en mode texte</B>, changez le mode du r&eacute;pertoire à la valeur 777.<P>";
	echo "<LI><B>Si vous avez un accès Telnet</B>, faites un <I>chmod&nbsp;777&nbsp;repertoire_courant</I>.<P>";
	echo "</UL>";
	echo "<P>Une fois cette manipulation effectu&eacute;e, vous pourrez <B><A HREF='spip_loader.php3?charger=oui&hash=$hash&id_auteur=$id_auteur'>recharger cette page</A></B> ";
	echo "afin de commencer le t&eacute;l&eacute;chargement puis l'installation.";
	echo "<P>Si l'erreur persiste, vous devrez passer par la procédure d'installation classique ";
	echo "(t&eacute;l&eacute;chargement de tous les fichiers par FTP).";

	fin_html();
	exit;
}


//
// Tenter le telechargement HTTP
//

$fichier = ".spip.bin";
if ($gz) $fichier .= ".gz";
$url = "http://www.spip.net/spip-dev/DISTRIB/$fichier";

// Tenter un chargement direct par fopen
$http = @fopen($url, "rb");
if (!$http) {
	// En cas d'echec faire la requete HTTP a la main
	for ($i = 0; $i < 5; $i++) {
		$t = parse_url($url);
		$host = $t['host'];
		if (!($port = $t['port'])) $port = 80;
		if (!($path = $t['path'])) $path = "/";

		$http = @fsockopen($host, $port);
		if (!$http) break;
		@fputs($http, "GET $path HTTP/1.1\nHost: $host\n\n");

		$status = 0;
		$location = '';

		// Passer les en-tetes (termines par une ligne vide)
		$s = trim(fgets($http, 16384));
		if (ereg('^HTTP/[0-9]+\.[0-9]+ ([0-9]+)', $s, $r)) $status = $r[1];
		while ($s = trim(fgets($http, 16384))) {
			if (ereg('^Location: (.*)', $s, $r)) {
				$location = $r[1];
			}
		}
		if ($status != 200) {
			fclose($http);
			unset($http);
		}
		if ($status >= 300 AND $status < 400 AND $location) $url = $location;
		else break;
	}
}

$n = 0;
if ($http) {
	// Sauver le fichier telecharge
	$f = fopen($fichier, "wb");
	while (!feof($http)) {
		$bin = fread($http, 1024);
		fwrite($f, $bin);
	}
	fclose($f);
	fclose($http);
	// Passer a l'etape suivante (desarchivage)
	header("Location: spip_loader.php3?fichier=$fichier&hash=$hash&id_auteur=$id_auteur&chmod=$chmod");
	exit;
}

die ("<h4>Le chargement a &eacute;chou&eacute;. Veuillez r&eacute;essayer, ou utiliser l'installation manuelle.</h4>");

?>
