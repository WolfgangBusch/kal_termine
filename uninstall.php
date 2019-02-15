<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo4.6
 * @version Februar 2019
 */
#
$my_package=$this->getPackageId();
#
# --- /redaxo/src/addons/AddOn/assets-Ordner loeschen
unlink(rex_path::addon($my_package,'assets/'.$my_package.'.css'));
rmdir(rex_path::addon($my_package,'assets'));
?>
