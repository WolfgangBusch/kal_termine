<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2024
 */
$addon=$this->getPackageId();
$basedir=rex_path::addon($addon);
require_once $basedir.'/lib/class.kal_termine.php';
require_once $basedir.'/lib/class.kal_termine_kalender.php';
require_once $basedir.'/lib/class.kal_termine_config.php';
require_once $basedir.'/lib/class.kal_termine_tabelle.php';
require_once $basedir.'/lib/class.kal_termine_formulare.php';
require_once $basedir.'/lib/class.kal_termine_menues.php';
require_once $basedir.'/lib/class.kal_termine_module.php';
#
# --- Stylesheet-Datei auch im Backend einbinden
$file=rex_url::addonAssets($addon).$addon.'.css';
rex_view::addCssFile($file);
#
# --- Terminkategorien als Rollen einrichten
$addon::kal_set_roles();
?>
