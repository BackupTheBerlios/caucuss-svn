<?php

//change truc@bidule.fr en truc_chez_bidule.fr
function cache_email($texte){
   list($a, $b) = split('@', $texte);
   return $a.'_chez_'.$b;
}

//GLOBALS['dossier_squelettes'] = 'caucuss-sq';


/*
 *   +----------------------------------+
 *    Nom du Filtre :    pagination                                               
 *   +----------------------------------+
 *    Date : dimanche 22 août 2004
 *    Auteur :  James (klike<at>free.fr)
 *   +-------------------------------------+
 *    Fonctions de ce filtre :
 *     affiche la liste des pages d'une boucle contenant
 *     un critère de limite du type {debut_xxx, yyy}
 *   +-------------------------------------+ 
 *  
 * Pour toute suggestion, remarque, proposition d'ajout
 * reportez-vous au forum de l'article :
 * http://www.uzine.net/spip_contrib/article.php3?id_article=663
*/

function pagination($total, $position=0, $pas=1, $fonction='') {
  global $clean_link;
  global $pagination_item_avant, $pagination_item_apres, $pagination_separateur;
  tester_variable('pagination_separateur', '&nbsp;&middot; ');
  if (ereg('^debut([-_a-zA-Z0-9]+)$', $position, $match)) {
    $debut_lim = "debut".$match[1];
    $position = intval($GLOBALS['HTTP_GET_VARS'][$debut_lim]);
  }
  $nombre_pages = floor(($total-1)/$pas)+1;
  $texte = '';
  if($nombre_pages>1) {
    $i = 0;
    while($i<$nombre_pages) {
      $clean_link->delVar($debut_lim);
      $clean_link->addVar($debut_lim, strval($i*$pas));
      $url = $clean_link->getUrl();
      if(function_exists($fonction)) $item = call_user_func($fonction, $i+1);
      else $item = strval($i+1);
      if(($i*$pas) != $position) {
        if(function_exists('lien_pagination')) $item = lien_pagination($url, $item, $i+1);
        else $item = "<a href=\"".$url."\">".$item."</a>";
      }
      $texte .= $pagination_item_avant.$item.$pagination_item_apres;
      if($i<($nombre_pages-1)) $texte .= $pagination_separateur;
      $i++;
    }
    //Correction bug: $clean_link doit revenir à son état initial
    $clean_link->delVar($debut_lim);
    if($position) $clean_link->addVar($debut_lim, $position);
     return $texte;
  }
  return '';
}

?>
