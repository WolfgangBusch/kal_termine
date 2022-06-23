<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juni 2022
 */
$my_package=$this->getPackageId();
$basedir=rex_path::addon($my_package);
require_once $basedir.'lib/class.kal_termine_install.php';
require_once $basedir.'lib/class.kal_termine_config.php';
#
# --- Erzeugen der AddOn-Tabellen, falls sie nicht schon existieren
kal_termine_install::kal_create_tables();
#
# --- Erzeugen/Aktualisieren der Module
kal_termine_install::build_modules();
#
# --- ggf. zu Anfang eine Default-Konfiguration einrichten
$settings=kal_termine_config::kal_get_config();
if(count($settings)<=0):
  $settings=kal_termine_config::kal_default_config();
  kal_termine_config::kal_set_config($settings);
  endif;
#     zugehoeriges CSS-File schreiben
kal_termine_config::kal_write_css();
?>