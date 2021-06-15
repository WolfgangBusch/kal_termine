<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juni 2021
*/
$my_package=$this->getPackageId();
$basedir=rex_path::addon($my_package);
require_once $basedir.'/lib/class.kal_termine_config.php';
require_once $basedir.'/lib/class.kal_termine_tabelle.php';
require_once $basedir.'/lib/class.kal_termine_kalender.php';
require_once $basedir.'/lib/class.kal_termine_formulare.php';
require_once $basedir.'/lib/class.kal_termine_menues.php';
require_once $basedir.'/lib/class.kal_termine_module.php';
#
# --- Stylesheet-Datei auch im Backend einbinden
$file=rex_url::addonAssets($my_package).$my_package.'.css';
rex_view::addCssFile($file);
?>
