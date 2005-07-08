<?php
//
// Librement adapt� du configurateur de Epona de Marc Lebas.
//


function les_fichiers() {
  global $fichiers;
  $fichiers = array('mes_fonctions.php3', 'style.css');
  $images = array();
  $couple = array('album_vignettes_incorp', 'album_simple_incorp', 'pied', 'menu', 'agen_min', 'agen_an', 'agenda', 'auteur', 'article',
  		  'breve', 'forum', 'plan', 'recherche', 'rubrique', 'sommaire', 'top', 'bottom', 'mot');
  
  foreach ($couple as $fichier) {$fichiers[]=$fichier.'.html'; $fichiers[]=$fichier.'.php3';}
  foreach ($images as $image) $fichiers[]='IMG/'.$image;
}

function refus($msg) {
  echo "<b><font color=red>$msg</font></b><br>";
  echo "Action non commenc�e; rectifier les conditions initiales avant de reprendre<br>";
}

function copie($src, $dst) {
  if (!copy('../'.$src, '../'.$dst)) {abandon("�chec copie $src vers $dst"); return FALSE;}
  else
  {  echo basename($src)." ";  return TRUE;}
}



//
// Contr�les avant mise en place fichiers squelette
//
function racine() {
  if (is_writable(".."))  {
  	 return TRUE;
	 }
  else {
  	refus("Pas d'acc�s en �criture dans le r�pertoire racine");
	echo "Changer les droits du r�pertoire<br>";
	echo "Si l'h�bergeur ne permet pas cette possibilit�, installer en local<br>";
	echo "puis t�l�charger le squelette sur site<br>";
	return FALSE;
	}
}

function caucuss_sq() {
  if (!file_exists("../caucuss-sq")) {
    refus("R�pertoire caucuss-sq non trouv� � la racine du site");
    return FALSE;
  }
  if (!racine()) return FALSE;
  return TRUE;
}

/* v�rifie que tous les fichiers � copier sont bien pr�sents */
function pre_install_fichiers() {
  global $fichiers;
  if (!caucuss_sq()) return FALSE;
  if (!file_exists("../IMG")) { refus("../IMG non trouv�."); return FALSE; }
  // Tous les fichiers sont-ils l� ?
  les_fichiers();
  foreach ($fichiers as $fichier) {
     if (!file_exists('../caucuss-sq/'.$fichier)) {
       refus ("Fichier caucuss-sq/".$fichier." non trouv�");
       return FALSE;
     }
  }
  return TRUE;
}

//
// /*Sauvegarde ancien squelette*/ et installation
//
function install_fichiers() {
  global $fichiers;
  /*
  echo "<h3>Sauvegarde des fichiers squelette (en .bak)</h3>";
  foreach ($fichiers as $fichier) {
     if (!file_exists('../'.$fichier)) continue;
     if (!copie($fichier, 'caucuss-sq/'.$fichier.'.bak')) return FALSE;
  }
  */
  echo "<h3>Installation du squelette</h3>";
  foreach ($fichiers as $fichier) {
     if (!copie('caucuss-sq/'.$fichier, $fichier)) return FALSE;
  }
  echo "<br>";
  return TRUE;
}


//
// Contr�les avant restauration fichiers squelette
//
function pre_restaure_fichiers() {
  if (!epona_sq()) return FALSE;
  if (!is_writable("../caucuss-sq")) {
    refus("Pas d'acc�s en �criture dans caucuss-sq");
    echo "Changez les droits du r�pertoire<br>";
    return FALSE;
  } 
  return TRUE;
}

function avertir($msg) { echo "<font color=orange>$msg</font><br>"; }

function restaure_fichiers() {
  global $fichiers;
  les_fichiers();
  echo "<h3>Effacement des fichiers caucuss � la racine</h3>";
  foreach ($fichiers as $fichier) {
     if (!file_exists('../'.$fichier)) {avertir($fichier." non trouv�"); continue;}
     if (!unlink('../'.$fichier)) {abandon("�chec effacement $fichier..."); return FALSE;}
     echo basename($fichier)." ";
  }
  
  echo "<h3>Restauration des fichiers .bak depuis caucuss-sq</h3>";
  foreach ($fichiers as $fichier) {
     if (!file_exists('../caucuss/'.$fichier.'.bak')) continue;
     if (!rename('../epona-sq/'.$fichier.'.bak', '../'.$fichier)) {
       abandon("�chec restauration $fichier...");
       return FALSE; 
     }
     echo basename($fichier)." ";
  }
  echo "<br>";
  return TRUE;
}




//
// Fonctions pour mot-cl�s
//
function id_groupe($titre) {
  $result = spip_query("SELECT id_groupe FROM spip_groupes_mots WHERE titre='$titre'");
  if ($row = spip_fetch_array($result)) return $row['id_groupe'];
  return 0;
}
//seul='oui' un 'non'
function active_groupe($groupe, $mots, $unseul) {
  $id_groupe=id_groupe($groupe);
  if ($id_groupe != 0) return FALSE;
  
  if($unseul == 'oui') {
  spip_query("REPLACE spip_meta (nom, valeur) VALUES ('config_precise_groupes', 'oui')");
  }
  
  //Cr�ation groupe + mots cl�
  spip_query("INSERT INTO spip_groupes_mots SET titre='$groupe', unseul='$unseul', obligatoire='non',
                 articles='oui', breves='non', rubriques='non', syndic='non',
                  0minirezo='oui', 1comite='oui', 6forum='non'");
  $id_groupe = spip_insert_id();
  foreach ($mots as $mot) {
    spip_query("INSERT INTO spip_mots (type, titre, id_groupe) VALUES ('$groupe', '$mot', '$id_groupe')");
    echo " $mot";
  }
  echo " (groupe mots $groupe cr��)<br>";
  return TRUE;
}

function pre_desactive_groupe($titre) {
  // seulement pour v�rifier que le groupe est libre
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
  // aucun contr�le d'attachement; d�j� fait par pre_desactive_groupe
  $id_groupe=id_groupe($titre);
  if ($id_groupe == 0) {
    avertir("Groupe de mot-cl� $titre non trouv�");
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
  echo " (groupe mots $titre effac�)<br>";
  return TRUE;
}

function pre_desactive_mot($id_mot, $titre) {
  // pour v�rifier qu'un mot est libre
  $rcode = TRUE;
  foreach (array('breve', 'article', 'rubrique', 'forum', 'syndic') as $elem) {
    $result = spip_query("SELECT id_".$elem." FROM spip_mots_".$elem."s WHERE id_mot=$id_mot");
    if ($row = spip_fetch_array($result)) {
      avertir("Le mot cl� $titre est attach� ($elem)");
      $rcode = FALSE;
    }
  }
  return $rcode;
}

/*
function desinstall() {
  echo "<h1>Restauration compl�te</h1>";
  if (!pre_restaure_fichiers()) return;
  if (!pre_desactive_groupe('Album')) return;
  if (!pre_desactive_groupe('Agenda')) return;
  if (!restaure_fichiers()) return;
  echo "<h3>Effacement en base</h3>";
  desactive_groupe('Album');
  desactive_groupe('Agenda');
  echo "<h1>Ex�cution compl�te</h1>";
}
*/

function squelette_on() {spip_query("REPLACE spip_meta (nom, valeur) VALUES ('config_precise_groupes', 'oui')");
  echo "<h1>Installation du squelette caucuss</h1>";
  if (!pre_install_fichiers()) return;
  if (!install_fichiers()) return;
  
  active_groupe('Agenda', array('Clowns','Impros','Com�die Musicale'), 'non');
  active_groupe('Album', array('simple','avec_vignettes'), 'oui');
  
  
  active_antidatage();
  
  echo "<h1>Ex�cution compl�te</h1>";
}

/*
function squelette_off() {
  echo "<h1>D�sinstallation du squelette caucuss</h1>";
  if (!pre_restaure_fichiers()) return;
  if (!restaure_fichiers()) return;
  echo "<h1>Ex�cution compl�te</h1>";
}
*/

function active_antidatage() {
spip_query("REPLACE spip_meta (nom, valeur) VALUES ('articles_redac', 'oui')");
echo "<h2>ajout des date anterieures</h2><br>";
}


//
// Main
//
$groupes_mots = array('Agenda', 'Album');
if (!file_exists("inc.php3")) {
  refus("inc.php3 non trouv�. Le fichier caucuss_conf.php3 a-t-il �t� plac� dans le r�pertoire ecrire ?");
  exit;
}

include ("inc.php3");
include ("inc_presentation.php3");

if (($connect_statut != "0minirezo"))/*0minirezo correspond � l'admin*/ {
  install_debut_html(_T('info_acces_refuse'));
  install_fin_html();
  exit;
}


install_debut_html("Configurateur");

switch ($action) {
  case 'squelette_on' : squelette_on(); break;
//  case 'squelette_off' : squelette_off(); break;
//  case 'desinstall' : desinstall(); break;
}

echo "<hr>";
echo '<a href="../sommaire.php3?recalcul=oui">Page d\'accueil</a><br>';


if(file_exists("../agenda.php3"))
{
  echo "<a href=\"epona_conf.php3?action=desinstall\">Tout d�sinstaller</a><br>";
  ;
} 
else {
  echo "<a href=\"caucuss_conf.php3?action=squelette_on\">Installer le squelette</a><br>";
}

echo "<ul>";
echo "<li>Les choix propos�s ci-dessus d�pendent de l'�tat de la configuration</li>";
echo "<li>Toute action doit se terminer par 'Ex�cution compl�te'</li>";
echo "<li>Tous les contr�les sont faits avant ex�cution</li>";
echo "<li>En cas d'erreur, la proc�dure est stopp�e avec un message explicite</li>";
echo "<li>Les sauvegardes (.bak) de fichiers vont dans le r�pertoire caucuss-sq</li>";

echo "<h3>Etat de la configuration:</h3>";

foreach ($groupes_mots as $titre) {
  $id_groupe=id_groupe($titre);
  if ($id_groupe == 0) {echo  "Groupe ".$titre." : absent<br>";} else {
    echo  "Groupe ".$titre." : pr�sent<br>";
    pre_desactive_groupe($titre);
  }
}

echo "<br><a href=.>espace priv�e</a><br>";
echo "<br><a href=caucuss_conf.php3?action=squelette_on>r�installer le squelette</a><br>";

install_fin_html();
?>
