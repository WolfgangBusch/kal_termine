<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version April 2019
 */
$my_package=$this->getPackageId();
$basedir=rex_path::addon($my_package);
require_once $basedir.'lib/class.kal_termine_install.php';
require_once $basedir.'lib/class.kal_termine_config.php';
#
# --- Erzeugen der Termintabelle, falls sie nicht schon existiert
kal_termine_install::kal_create_table();
#
# --- Erzeugen/Aktualisieren der Module
kal_termine_install::build_modules($my_package);
#
# --- Default-Konfiguration einrichten und zugehoeriges CSS-File schreiben
$settings=kal_termine_config::kal_get_config();
if(count($settings)<=0):
  $settings=kal_termine_config::kal_get_default_config();
  kal_termine_config::kal_set_config($settings);
  endif;
kal_termine_config::kal_write_css();
?>