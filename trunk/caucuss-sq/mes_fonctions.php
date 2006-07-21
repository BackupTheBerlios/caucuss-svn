<?php

//change truc@bidule.fr en truc_chez_bidule.fr
function cache_email($texte){
   list($a, $b) = split('@', $texte);
   if($a != '')
    return $a.' chez '.$b;
  else 
    return '';
}

?>
