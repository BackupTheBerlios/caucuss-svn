<?php
//
// Librement adapt�du configurateur de Epona de Marc Lebas
// http://www.spip-contrib.net/Squelette-Epona-v2-2?var_recherche=epona
//

function squelette_present(){return file_exists("../caucuss-sq/inc-agenda_min.html");}

//sert �v�ifier si tous les fichiers sont pr�ents
function les_fichiers() {
  global $fichiers;
  $fichiers = array('taust.css','mes_fonctions.php3');
  $gabarit_html = array('events','albums','sites','agenda_annuel', 'inc-top','inc-bottom', 'inc-album_full_vignettes','inc-album_avec_vignettes','inc-album_simple','inc-menu','inc-agenda_min');
  $images = array('email.png','lire_blanc.png', 'logo_taust_favicon.png','agendares.png','agt_back.png','agt_forward.png','albums.png','article.png','attach.png','auteur.png','background.jpg','billet.png','calendar.png','coin-bd.png','coin-bg.png','coin-hd.png','coin-hg.png','download.png','events.png','forum.png','getfirefox.png','info.png','internet.png','liens.png','lire.png','logo_auteur.png','logo_taust.png','logo_taust_petit.png','memerub.png','menu-albums.png','menu-calendar.png','menu-forum.png','menu-liens.png','menu-recherche.png','menu-rub.png','reagir.png','recherche.png','result_articles.png','result_breves.png','result_forum.png','result_liens.png','rss.png','rubriques.png','suite.png','taust-enter.png','titre_auteur.png','titre_forum.png');
  
  $couple = array('index', 'agenda', 'auteur', 'article', 'forum', 'recherche', 'rubrique', 'sommaire', 'mot');
  
  foreach ($couple as $fichier) {$fichiers[]=$fichier.'.html'; $fichiers[]=$fichier.'.php3';}
  foreach ($images as $image) $fichiers[]='IMG/'.$image;
  foreach ($gabarit_html as $gab) $fichiers[]=$gab.'.html';
}

function refus($msg) {
  echo "<b><font color=red>$msg</font></b><br>";
  echo "Action non commenc�; rectifier les conditions initiales avant de reprendre<br>";
}

function copie($src, $dst) {
  if (!copy('../'.$src, '../'.$dst)) {abandon("�hec copie $src vers $dst"); return FALSE;}
  else
  {  echo basename($src)." ";  return TRUE;}
}
function mv($src, $dst) {
  if (!rename('../'.$src, '../'.$dst)) {abandon("�hec d�lacement $src vers $dst"); return FALSE;}
  else
  {  echo basename($src)." ";  return TRUE;}
}



function abandon($msg) {
  echo "<b><font color=red>$msg</font></b><br>";
  echo "Action abandonn� en cours d'��ution<br>";
  echo "Rectifier le probl�e et r�ablir des conditions initiales avant de reprendre<br>";
}

//
// Contr�es avant mise en place fichiers squelette
//
function racine() {
  if (is_writable(".."))  {
  	 return TRUE;
	 }
  else {
  	refus("Pas d'acc� en �riture dans le r�ertoire racine");
	echo "Changer les droits du r�ertoire<br>";
	echo "Si l'h�ergeur ne permet pas cette possibilit� installer en local<br>";
	echo "puis t��harger le squelette sur site<br>";
	return FALSE;
	}
}

function caucuss_sq() {
  if (!file_exists("../caucuss-sq")) {
    refus("R�ertoire caucuss-sq non trouv��la racine du site");
    return FALSE;
  }
  if (!racine()) return FALSE;
  return TRUE;
}

/* v�ifie que tous les fichiers �copier sont bien pr�ents */
function pre_install_fichiers() {
  global $fichiers;
  if (!caucuss_sq()) return FALSE;
  if (!file_exists("../IMG")) { refus("../IMG non trouv�"); return FALSE; }
  // Tous les fichiers sont-ils l�?
  les_fichiers();
  foreach ($fichiers as $fichier) {
     if (!file_exists('../caucuss-sq/'.$fichier)) {
       refus ("Fichier caucuss-sq/".$fichier." non trouv�);
       return FALSE;
     }
  }
  return TRUE;
}

//
// /*Sauvegarde ancien squelette (en .bak dans caucuss-sq) et */
// installation
//
function install_fichiers() {
  global $fichiers;
  /*
  echo "<h3>Sauvegarde des fichiers de l'ancien squelette (en .bak)</h3>";
  foreach ($fichiers as $fichier) {
     if (!file_exists('../'.$fichier)) continue;
     if (!copie($fichier, 'caucuss-sq/'.$fichier.'.bak')) return FALSE;
  }
  */
  //sauvegarde mes_options.php3 s'il existe deja
  //if (!file_exists('ecrire/'.'mes_options.php3')) continue;
  //if (!copie($fichier, 'caucuss-sq/'.$fichier.'.bak')) return FALSE;

  echo "<h3>Installation du squelette</h3>";//avec #DOSSIER_SQUELETTE on copie qu'un seul fichier :) (spip 1.8.2)

  if (!copie('caucuss-sq/'.'page.php3', 'page.php3')) return FALSE; else {avertir("<br/>remplacement page.php3 par page.php3 patch�svn");}
  //comme on remplace le index index.php3 de spip on le sauvegarde avant pour une restauration lors de la desinstall
  backup("index.php3");

  if(!copie('caucuss-sq/'.'index.php3', 'index.php3')) return FALSE;

  //foreach ($fichiers as $fichier) {
  //   if (!copie('caucuss-sq/'.$fichier, $fichier)) return FALSE;
  //}

  echo "<br/>";
  return TRUE;
}

function backup($f)
{
	if (file_exists("../$f")) 
	{	
		avertir( "<br/>backup de $f en $f.bak dans caucuss-sq/</br>");
		if(!mv($f,"caucuss-sq/$f.bak")) return FALSE;
	}
}

//
// Contr�es d'acc� en �riture avant restauration fichiers squelette
//
/*
function pre_restaure_fichiers() {
  if (!caucuss_sq()) return FALSE;
  if (!is_writable("../caucuss-sq")) {
    refus("Pas d'acc� en �riture dans caucuss-sq");
    echo "Changez les droits du r�ertoire<br>";
    return FALSE;
  } 
  return TRUE;
}
*/

function avertir($msg) { echo "<font color=orange>$msg</font><br>"; }

/* efface les fichiers du squelette de la racine */
/*
function efface_fichiers() {
  global $fichiers;
  les_fichiers();
  echo "<h3>Effacement des fichiers caucuss �la racine</h3>";
  foreach ($fichiers as $fichier) {
     if (!file_exists('../'.$fichier)) {avertir($fichier." non trouv�); continue;}
     if (!unlink('../'.$fichier)) {abandon("�hec effacement $fichier..."); return FALSE;}
     echo basename($fichier)." ";
	}  

//  echo "<h3>Restauration des fichiers .bak depuis caucuss-sq</h3>";
//  foreach ($fichiers as $fichier) {
//     if (!file_exists('../caucuss/'.$fichier.'.bak')) continue;
//     if (!rename('../caucuss-sq/'.$fichier.'.bak', '../'.$fichier)) {
//        abandon("�hec restauration $fichier...");
//        return FALSE; 
//      }
     echo basename($fichier)." ";
  }

  echo "<br/>";
  return TRUE;
}
*/

//
// Fonctions pour mot-cl�
//
function id_groupe($titre) {
  $result = spip_query("SELECT id_groupe FROM spip_groupes_mots WHERE titre='$titre'");
  if ($row = spip_fetch_array($result)) return $row['id_groupe'];
  return 0;
}

//unseul='oui' ou unseul='non'
function active_groupe($groupe, $mots, $unseul) {
  $id_groupe=id_groupe($groupe);
  if ($id_groupe != 0) return FALSE;
  
  if($unseul == 'oui') {
  spip_query("REPLACE spip_meta (nom, valeur) VALUES ('config_precise_groupes', 'oui')");
  }
  
  //Cr�tion groupe + mots cl�  spip_query("INSERT INTO spip_groupes_mots SET titre='$groupe', unseul='$unseul', obligatoire='non',
                 articles='oui', breves='non', rubriques='non', syndic='non',
                  minirezo='oui', comite='oui', forum='non'");
  $id_groupe = spip_insert_id();

  foreach ($mots as $mot) {
    spip_query("INSERT INTO spip_mots (type, titre, id_groupe) VALUES ('$groupe', '$mot', '$id_groupe')");
    echo " $mot";
  }
  echo "(groupe mots $groupe cr�)<br>";
  return TRUE;
}

function pre_desactive_groupe($titre) {
  // seulement pour v�ifier que le groupe est libre
  $rcode = TRUE;
  $id_groupe=id_groupe($titre);
  if ($id_groupe == 0) return TRUE;
  $result = spip_query("SELECT id_mot, titre FROM spip_mots WHERE id_groupe=$id_groupe");
  while ($row = spip_fetch_array($result)) {
    if (!pre_desactive_mot($row['id_mot'], $row['titre'])) $rcode = FALSE;
  }
  return $rcode;
}

function desactive_groupe($titre) {
  // aucun contr�e d'attachement; d��fait par pre_desactive_groupe
  $id_groupe=id_groupe($titre);
  if ($id_groupe == 0) {
    avertir("Groupe de mot-cl�$titre non trouv�);
    return FALSE;
  }
  $result = spip_query("SELECT id_mot, titre FROM spip_mots WHERE id_groupe=$id_groupe");
  while ($row = spip_fetch_array($result)) {
    spip_query("DELETE FROM spip_mots WHERE id_mot='".$row['id_mot']."'");
    spip_query("DELETE FROM spip_mots_articles WHERE id_mot='".$row['id_mot']."'");
    spip_query("DELETE FROM spip_index_mots WHERE id_mot='".$row['id_mot']."'");
    echo $row['titre'].' ';
  }
  spip_query("DELETE FROM spip_groupes_mots WHERE titre='$titre'");
  echo " (groupe mots $titre effac�<br>";
  return TRUE;
}


function pre_desactive_mot($id_mot, $titre) {
  // pour v�ifier qu'un mot est libre
  $rcode = TRUE;
  foreach (array('breve', 'article', 'rubrique', 'forum', 'syndic') as $elem) {
    $result = spip_query("SELECT id_".$elem." FROM spip_mots_".$elem."s WHERE id_mot=$id_mot");
    if ($row = spip_fetch_array($result)) {
      avertir("Le mot cl�$titre est attach�($elem)");
      $rcode = FALSE;
    }
  }
  return $rcode;
}

function desinstall() {
  echo "<h1>Desinstallation (fichiers squelettes + mot clefs)</h1>";
  //if (!pre_restaure_fichiers()) return;
  if (!pre_desactive_groupe('Album')) return;
  if (!pre_desactive_groupe('Agenda')) return;
  if (!pre_desactive_groupe('Meta')) return;
  //if (!efface_fichiers()) return;

  if (squelette_off()) return;

  echo "<h3>Effacement en base</h3>";
  desactive_groupe('Album');
  desactive_groupe('Agenda');
  desactive_groupe('Meta');
  echo "<h1>Ex�ution compl�e</h1>";
}


function squelette_on() {
  //spip_query("REPLACE spip_meta (nom, valeur) VALUES ('config_precise_groupes', 'oui')");
  echo "<h1>Installation du squelette caucuss</h1>";
  if (!pre_install_fichiers()) return;
  if (!install_fichiers()) return;
  global $motsAgenda;	
  global $motsAlbum;
  echo "<br/>";
  active_groupe('Agenda',$motsAgenda, 'non');
  active_groupe('Album', $motsAlbum, 'oui');
  active_groupe('Album', array('cacher'), 'non');
  
  active_antidatage();
  active_preview();
  
  echo "<h1>Ex�ution compl�e</h1>";
}


function restaureFichier($f)
{
if(file_exists('../caucuss-sq/'.$f.'.bak'))
		{
			avertir('restauration de '.$f.' �la racine.');
			if(!mv('caucuss-sq/'.$f.'.bak',$f)) avertir("restauration impossible");
		}
	else
		{avertir('y avait pas de sauvegarde du '.$f.' dans caucuss-sq');}
}

function squelette_off() {
  echo "<h1>D�installation des fichiers du squelette caucuss plac��la racine (sans modif des mots clefs)</h1>";
  //if (!pre_restaure_fichiers()) return;

  restaureFichier('index.php3');


  //restaure mes_options.php3.bak si il existait 
//   if (!unlink('../ecrire/mes_options.php3'))
// 	{abandon("�hec effacement mes_options.php3..."); return FALSE;}
  /*if (file_exists('../ecrire/mes_options.php3.bak')) 
	{ if (!mv('../ecrire/mes_options.php3.bak', '../ecrire/mes_options.php3'))
			{abandon("�hec effacement mes_fontions.php3..."); return FALSE;}
	}*/ 

  //if (!efface_fichiers()) return;
  echo "<h1>Ex�ution compl�e</h1>";
}


function active_antidatage() {
spip_query("REPLACE spip_meta (nom, valeur) VALUES ('articles_redac', 'oui')");
echo "<h2>activation des date anterieures</h2><br>";
}

function active_preview() {
spip_query("REPLACE spip_meta (nom, valeur) VALUES ('creer_preview', 'oui')");
echo "<h2>activation des miniatures</h2><br>";
}

//
// Main
//
$groupes_mots = array('Agenda', 'Album', 'Meta');
$motsAgenda =   array('clowns','Impros');
$motsAlbum =    array('full_vignettes','simple','avec_vignettes');



if (!file_exists("inc.php3")) {
  refus("inc.php3 non trouv� Le fichier caucuss_conf.php3 a-t-il ��plac�dans le r�ertoire ecrire?");
  exit;
}

include ("inc.php3");
include ("inc_presentation.php3");



if (($connect_statut != "0minirezo"))/*0minirezo correspond �l'admin*/ {
  install_debut_html(_T('info_acces_refuse'));
  install_fin_html();
  exit;
}


install_debut_html("Configurateur");

switch ($action) {
  case 'squelette_on' : squelette_on(); break;
  case 'squelette_off' : squelette_off(); break;
  case 'desinstall' : desinstall(); break;
}

echo "<hr>";
echo '<a href="../sommaire.php3?recalcul=oui">Page d\'accueil</a>';
echo "<br><a href=.>Espace priv�/a><br><br>";



if(squelette_present())
{
  echo "<a href=\"caucuss_conf.php3?action=desinstall\">Tout d�installer (fichiers+mot clefs)</a><br>";
  echo "<a href=\"caucuss_conf.php3?action=squelette_off\">D�installer les fichiers du squelette</a>";
} 
else {
  echo "<a href=\"caucuss_conf.php3?action=squelette_on\">Installer le squelette</a>";
}

echo "<br><a href=caucuss_conf.php3?action=squelette_on>R�nstaller le squelette</a>";

echo "<ul>";
//echo "<li>Les choix propos� ci-dessus d�endent de l'�at de la configuration</li>";
echo "<li>Toute action doit se terminer par 'Ex�ution compl�e'</li>";
echo "<li>Tous les contr�es sont faits avant ex�ution</li>";
echo "<li>En cas d'erreur, la proc�ure est stopp� avec un message explicite</li>";
//echo "<li>Les sauvegardes (.bak) de fichiers vont dans le r�ertoire caucuss-sq</li></ul>";
echo "</ul>";


echo "<h2>Etat de la configuration:</h2>";

if (!squelette_present()) 
  echo "Squelette : absent<br>";
  	else 
  Echo "Squelette : pr�ent<br>";


foreach ($groupes_mots as $titre) {
  $id_groupe=id_groupe($titre);
  if ($id_groupe == 0) {echo  "Groupe ".$titre." : absent<br>";} else {
    echo  "Groupe ".$titre." : pr�ent<br>";
    pre_desactive_groupe($titre);
  }
}

install_fin_html();
?>
