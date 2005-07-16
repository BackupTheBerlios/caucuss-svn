#!/bin/env bash


if [ "$1" != "" ]; then
    cp -rf caucuss-sq/ $1;
    #echo "cp -rf caucuss-sq/ $1"
    cp -rf ecrire/ $1;
    #echo "cp -rf ecrire/ $1"
    #chmod 777 -R $1
    #echo "chmod -R $1"
    echo "suivre ensuite http://.../racinespip/ecrire/caucuss_conf.php3"
else
    echo "usage : install.sh rep_racine_de_spip"
fi


