<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
 */
class kal_termine_install {
#
#----------------------------------------- Methoden
#   create_table()
#   add_column_monate()
#   build_modules()
#
#----------------------------------------- Konstanten
const this_addon=kal_termine::this_addon;   // Name des AddOns
#
#----------------------------------------- Methoden
public static function create_table() {
   #   Einrichten des Terminkalenders als Redaxo-Tabelle. Falls die Tabelle schon
   #   existiert, wird sie aktualisiert. Rueckgabe der Spaltennamen in der aktuellen
   #   Reihenfolge als nummeriertes Array (Nummerierung ab 0).
   #
   $addon=self::this_addon;
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $columns=array();
   for($i=0;$i<count($cols);$i=$i+1) $columns[$i]=$keys[$i].' '.$cols[$keys[$i]][0];
   return cms_interface::create_table($columns);
   }
public static function add_column_monate() {
   #   Erweiterung der Terminkalender-Tabelle um die Spalte 'monate', falls diese noch
   #   nicht existiert (Update von aelteren Versionen auf Version 3.5 oder hoeher).
   #   Es wird eine Erfolgsmeldung zurueck gegeben.
   #
   $addon=self::this_addon;
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $keymon=$keys[8];   // key monate
   $type=$cols[$keymon][0];   // 'int(11) NOT NULL DEFAULT 0';
   $res=cms_interface::add_column_monate($keymon,$type);
   if(strlen($res)>2) return $res;
   if($res):
     return 'key "'.$keymon.'" eingefügt';
     else:
     return 'key "'.$keymon.'" konnte nicht eingefügt werden';
     endif;
   }
public static function build_modules() {
   #   Einrichtung bzw. Aktualisierung der beiden Module.
   #
   $addon=self::this_addon;
   $modules=array(1=>$addon::MODUL1_IDENT, 2=>$addon::MODUL2_IDENT);
   for($i=1;$i<=count($modules);$i=$i+1)
      cms_interface::create_module($modules[$i]);
   }
}
?>
