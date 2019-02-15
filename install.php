<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2019
 */
$my_package=$this->getPackageId();
$basedir=rex_path::addon($my_package);
require_once $basedir.'lib/class.kal_termine_install.php';
require_once $basedir.'lib/class.kal_termine_tabelle.php';  // nur fuer kal_define_tabellenspalten()
require_once $basedir.'lib/class.kal_termine_config.php';   // nur fuer kal_write_css()
#
# --- Erzeugen der Termintabelle, falls sie nicht schon existiert
kal_termine_install::kal_create_table();
#
# --- Erzeugen des Moduls zur Verwaltung der Termine
kal_termine_install::update_module($my_package,'Termine verwalten',
   'mod_term_manage_in','mod_term_manage_out');
#
# --- Erzeugen des Moduls zur Auswahl eines Start-Terminmenues
kal_termine_install::update_module($my_package,'Auswahl eines Start-Terminformulars',
   'mod_term_menu_in','mod_term_menu_out');
#
# --- Erzeugen des Moduls zur Anzeige einer Standard-Terminliste
kal_termine_install::update_module($my_package,'Ausgabe einer Standard-Terminliste',
   'mod_term_list_in','mod_term_list_out');
#
# --- bei der allerersten Installation: Default-Konfiguration und zugehoeriges CSS-File
$dir=$basedir.'assets';
if(!file_exists($dir)) mkdir($dir);
$file=$dir.'/'.$my_package.'.css';
if(!file_exists($file)):
  #  -  bei leerer Konfiguration Default-Konfiguration setzen
  $settings=kal_termine_config::kal_get_config();
  if(count($settings)<=0):
    $settings=kal_termine_config::kal_get_default_config();
    kal_termine_config::kal_set_config($settings);
    endif;
  #  -  Default-CSS-File schreiben und in den assets-Ordner kopieren
  kal_termine_config::kal_write_css();
  endif;
?>
