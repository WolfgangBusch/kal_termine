<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
 */
class cms_interface {
#
#----------------------------------------- Methoden
#   cms
#      backend()
#   Tabellennamen
#      name_termin_tabelle()
#         intern_name_module_tabelle()
#         intern_name_slice_tabelle()
#         intern_name_user_tabelle()
#         intern_name_role_tabelle()
#   Pfade
#      path_main()
#      path_assets()
#      url_assets()
#   Datenbankzugriff
#         intern_db_zugriff()
#   Termintabelle
#      create_table($columns)
#      add_column_monate($key,$type)
#      select_termin($where,$order)
#      insert_termin($keyval)
#      delete_termin($keypid,$pid)
#      update_termin($keyval,$keypid,$pid)
#   Konfiguration
#      config_removeNamespace()
#      config_set($key,$value)
#      config_get($key,$default)
#   Zugriffserlaubnis auf Terminkategorien
#         intern_perm($kat_id)
#         intern_role($ident,$kat_id)
#         intern_insert_user_roles($kats,$ident)
#         intern_select_user_roles($ident)
#         intern_delete_user_role($id)
#      set_roles()
#      allowed_terminkategorien($art_id)
#      register_perm_kategorien()
#   Module
#      create_module($ident)
#         intern_get_modulid()
#      read_menue_data($artid)
#      write_menue_data($key,$value)
#
#----------------------------------------- Konstanten
const this_addon=kal_termine::this_addon;   // Name des AddOns
#
#----------------------------------------- Variable
public static $FRONTEND_EDIT=FALSE;         // KEINE Termineingabe im Frontend
#
#----------------------------------------- cms
public static function backend() {
   #   Wird das aktuelle PHP-Skript im CMS-Backend ausgefuehrt?
   #
   return rex::isBackend();
   }
#
#----------------------------------------- Tabellennamen
public static function name_termin_tabelle() {
   #   Rueckgabe des Namens der Termin-Tabelle.
   #
   return rex::getTablePrefix().self::this_addon;
   }
public static function intern_name_module_tabelle() {
   #   Rueckgabe des Namens der Module-Tabelle.
   #
   return rex::getTablePrefix().'module';
   }
public static function intern_name_slice_tabelle() {
   #   Rueckgabe des Namens der Slice-Tabelle.
   #
   return rex::getTablePrefix().'article_slice';
   }
public static function intern_name_user_tabelle() {
   #   Rueckgabe des Namens der User-Tabelle.
   #
   return rex::getTablePrefix().'user';
   }
public static function intern_name_role_tabelle() {
   #   Rueckgabe des Namens der User-Role-Tabelle.
   #
   return rex::getTablePrefix().'user_role';
   }
#
#----------------------------------------- Pfade
public static function path_main() {
   #   Rueckgabe des vollstaendigen Pfades zum Haupt-Verzeichnis.
   #
   return rex_path::addon(self::this_addon);
   }
public static function path_assets() {
   #   Rueckgabe des vollstaendigen Pfades zum Assets-Verzeichnis.
   #
   return rex_path::addonAssets(self::this_addon);
   }
public static function url_assets() {
   #   Rueckgabe des URLs zum Assets-Verzeichnis.
   #
   $url=rex_url::addonAssets(self::this_addon);
   #     fuehrenden Punkt entfernen (./assets/addons/kal_termine/...)
   $pos=strpos($url,DIRECTORY_SEPARATOR);
   return substr($url,$pos);
   }
#
#----------------------------------------- Datenbankzugriff
public static function intern_db_zugriff() {
   #   Zugriff auf die Datenbank, die die Termintabelle enthaelt.
   #   Rueckgabe des Datenbank-Handles.
   #
   return rex_sql::factory();
   }
#
#----------------------------------------- Termintabelle
public static function create_table($columns) {
   #   Einrichten der Termin-Tabelle. Rueckgabe der Spaltennamen der Tabelle
   #   als nummeriertes Array (Nummerierung ab 0).
   #   $columns          nummeriertes Array mit den SQL-CREATE-Kommandos zur
   #                     Definition der Tabellenspalten (Nummerierung ab 0)
   #
   $table=self::name_termin_tabelle();
   $sql=self::intern_db_zugriff();
   #
   # --- Character-Set (Collation), abgeleitet aus der Tabelle 'rex_article'
   $col=$sql->getArray('SHOW TABLE STATUS where name like \'rex_article\'');
   $arr=explode('_',$col[0]['Collation']);
   $charset=$arr[0];
   #
   # --- Primaerschluessel 'pid'
   $primary=explode(' ',$columns[0])[0];
   #
   # --- Erzeugen der Tabelle
   $cols='';
   for($i=0;$i<count($columns);$i=$i+1)
      $cols=$cols.$columns[$i].', ';
   $cols=$cols.'CONSTRAINT PK_'.$table.' PRIMARY KEY ('.$primary.')';
   $query='CREATE TABLE IF NOT EXISTS '.$table.' ('.$cols.') CHARSET='.$charset.';';
   $sql->setQuery($query);
   #
   # --- zur Kontrolle: Rueckgabe der Spaltennamen
   $coln=$sql->getArray('SHOW COLUMNS FROM '.$table);
   $field=array();
   for($i=0;$i<count($coln);$i=$i+1) $field[$i]=$coln[$i]['Field'];
   return $field;
   }
public static function add_column_monate($keymon,$type) {
   #   Hinzufuegen der Spalte 'monate' zur Termin-Tabelle, falls nicht schon
   #   vorhanden (Update auf Vers. 3.5 oder hoeher von aelteren Versionen)
   #   und Rueckgabe einer Erfolgsmeldung.
   #   $keymon           ='monate' (Spaltennamen)
   #   $type             zugehoeriger Datentyp
   #
   $table=self::name_termin_tabelle();
   $sql=self::intern_db_zugriff();
   $coln=$sql->getArray('SHOW COLUMNS FROM '.$table);
   for($i=0;$i<count($coln);$i=$i+1)
      if($coln[$i]['Field']==$keymon) return 'Spalte "'.$keymon.'" existiert schon';
   #
   # --- Spalte hinzufuegen und Default auf 0 setzen
   $res=$sql->setQuery('ALTER TABLE '.$table.' ADD '.$keymon.' '.$type);
   if($res===null):
     return FALSE;
     else:
     return TRUE;
     endif;
   }
public static function select_termin($where,$order) {
   #   Rueckgabe von Terminen aus der Datenbanktabelle, gefiltert durch eine
   #   WHERE-Bedingung und sortiert (ASC) nach 2 Schluesseln.
   #   $where            Text der WHERE-Bedingung
   #   $order            Spaltenname, nach dem das Abfrageergebnis sortiert
   #                     werden soll, ggf. inkl. ' ASC' oder ' DESC'
   #
   $table=self::name_termin_tabelle();
   $sql=self::intern_db_zugriff();
   $query='SELECT * FROM '.$table.' WHERE '.$where;
   if(!empty($order)) $query=$query.' ORDER BY '.$order;
   return $sql->getArray($query);
   }
public static function insert_termin($keyval) {
   #   Eintragen eines neuen Termins in die Datenbanktabelle.
   #   $keyval           Array der Wertepaare (Schluessel,Wert) des Termins
   #                        $keyval[$i]['key']   Schluessel (Spaltenname der Tabelle)
   #                        $keyval[$i]['value'] Wert
   #                     (Nummerierung ab 1)
   #
   $table=self::name_termin_tabelle();
   $sql=self::intern_db_zugriff();
   $cols='';
   $vals='';
   for($i=1;$i<=count($keyval);$i=$i+1):
      $cols=$cols.','.$keyval[$i]['key'];
      $val=$keyval[$i]['value'];
      if(!is_numeric($val)):
        $st="'";
        if(str_contains($val,"'")) $st='"';
        $val=$st.$val.$st;
        endif;
      $vals=$vals.','.$val;
      endfor;
   $insert='INSERT INTO '.$table.' ('.substr($cols,1).') VALUES ('.substr($vals,1).')';
   #    offenbar implizit: $val=html_entity_decode($val);
   $ret=$sql->setQuery($insert);
   return $ret;
   }
public static function delete_termin($keypid,$pid) {
   #   Loeschen eines Termins in der Datenbanktabelle.
   #   $keypid           Schluessel des Parameters fuer die Termin-Id
   #   $pid              Wert des Parameters fuer die Termin-Id
   #
   $table=self::name_termin_tabelle();
   $sql=self::intern_db_zugriff();
   $delete='DELETE FROM '.$table.' WHERE '.$keypid.'='.$pid;
   $ret=$sql->setQuery($delete);
   return $ret;
   }
public static function update_termin($keyval,$keypid,$pid) {
   #   Korrigieren eines Termins in der Datenbanktabelle.
   #   $keyval           Array der Wertepaare (Schluessel,Wert) des Termins
   #                        $keyval[$i]['key']   Schluessel (Spaltenname der Tabelle)
   #                        $keyval[$i]['value'] Wert
   #                     (Nummerierung ab 1)
   #   $keypid           Schluessel fuer die Termin-Id
   #   $pid              Wert fuer die Termin-Id
   #
   $table=self::name_termin_tabelle();
   $sql=self::intern_db_zugriff();
   $set='';
   for($i=1;$i<=count($keyval);$i=$i+1):
      $val=$keyval[$i]['value'];
      if(!is_numeric($val)):
        $st="'";
        if(str_contains($val,"'")) $st='"';
        $val=$st.$val.$st;
        endif;
      $set=$set.','.$keyval[$i]['key'].'='.$val;
      endfor;
   $update='UPDATE '.$table.' SET '.substr($set,1).' WHERE '.$keypid.'='.$pid;
   #    offenbar implizit: $val=html_entity_decode($val);
   $ret=$sql->setQuery($update);
   return $ret;
   }
#
#----------------------------------------- Konfiguration
public static function config_removeNamespace() {
   #   Loeschen aller Konfigurationsparameter eines AddOns.
   #
   rex_config::removeNamespace(self::this_addon);
   }
public static function config_set($key,$value=null) {
   #   Setzen eines Konfigurationsparameters zum AddOn.
   #
   rex_config::set(self::this_addon,$key,$value);
   rex_config::save();
   }
public static function config_get($key=null,$default=null) {
   #   Auslesen von Konfigurationsparametern zum AddOn.
   #
   rex_config::refresh();
   return rex_config::get(self::this_addon,$key,$default);
   }
#
#----------------------------------------- Zugriffserlaubnis auf Terminkategorien
public static function intern_perm($kat_id) {
   #   Rueckgabe des Permission-Namens zu einer Terminkategorie.
   #   $kat_id           Id der Terminkategorie
   #
   return self::this_addon.'['.$kat_id.']';
   }
public static function intern_role($ident,$kat_id) {
   #   Rueckgabe des Rollennamens zu einer Terminkategorie.
   #   $ident            Stammstring ('Terminkategorie') im Rollenname
   #   $kat_id           Id der Terminkategorie
   #
   return $ident.' '.$kat_id;
   }
public static function intern_insert_user_roles($kats,$ident) {
   #   Setzen der Benutzerrollen fuer die Berechtigung zum Zugriff auf
   #   die Terminkategorien. Rueckgabe der Rollen als nummeriertes Array
   #   (Nummerierung ab 1), wobei jede Rolle ein assoziatives Array ist.
   #   Die Registrierung der Permissions in der Gruppe EXTRAS erfolgt in
   #   der Datei boot.php (funktioniert hier nicht!).
   #   $kats             nummeriertes Array der Terminkategorien (Nummerierung
   #                     ab 0), dabei ist jede Kategorie ein assoziatives
   #                     Array mit den Schluesseln 'id' und 'name'.
   #   $ident            Stammstring ('Terminkategorie') im Rollenname
   #
   $table1=self::intern_name_user_tabelle();
   $table2=self::intern_name_role_tabelle();
   $sql=self::intern_db_zugriff();
   #
   # --- Administrator-Benutzer
   $query='SELECT * FROM '.$table1.' WHERE role=\'\'';
   $us=$sql->getArray($query);
   $admin=$us[0]['login'];
   #
   # --- heutiges Datum
   $dat=date('d.m.Y');
   $heute=substr($dat,6).'-'.substr($dat,3,2).'-'.substr($dat,0,2).' 00:00:00';
   #
   # --- Rollen in die Datenbanktabelle rex_user_role schreiben
   $roles=array();
   for($i=0;$i<count($kats);$i=$i+1):
      $ii=$i+1;
      $kat_id=$kats[$i]['id'];
      $perm=self::intern_perm($kat_id);
      $role=self::intern_role($ident,$kat_id);
      #     Rolle einrichten, falls sie nicht schon eingerichtet ist
      $permis=json_encode(["general"=>null, "options"=>null,"extras"=>"|".$perm."|","clang"=>null,"media"=>null,"structure"=>null,"modules"=>null]);
      $query='SELECT * FROM '.$table2.' WHERE name=\''.$role.'\'';
      $arr=$sql->getArray($query);
      if(count($arr)<=0):
        $qpar='name,perms,createuser,updateuser,createdate,updatedate';
        $qval='\''.$role.'\',\''.$permis.'\',\''.$admin.'\',\''.$admin.'\',\''.$heute.'\',\''.$heute.'\'';
        $sql->setQuery('INSERT INTO '.$table2.' ('.$qpar.') VALUES ('.$qval.')');
        #     neue Rolle direkt wieder auslesen
        $arr=$sql->getArray($query);
        endif;
      $roles[$ii]=$arr[0];
      endfor;
   return $roles;
   }
public static function intern_select_user_roles($ident) {
   #   Rueckgabe der Benutzerrollen, deren Name einen gegebenen String enthalten,
   #   als nummeriertes Array (Nummerierung ab 1). Jede Benutzerrolle ist ein
   #   assoziatives Array mit allen Parametern gemaess Tabelle rex_user_role.
   #   $ident            Stammstring ('Terminkategorie') im Rollenname
   #
   $table=self::intern_name_role_tabelle();
   $sql=self::intern_db_zugriff();
   $query='SELECT * FROM '.$table.' WHERE name LIKE \'%'.$ident.'%\'';
   $rol=$sql->getArray($query);
   $roles=array();
   for($i=0;$i<count($rol);$i=$i+1) $roles[$i+1]=$rol[$i];
   return $roles;
   }
public static function intern_delete_user_role($id) {
   #   Loeschen einer Redaxo-Benutzerrolle.
   #   $id               Id der zu loeschenden Benutzerrolle
   #
   $table=self::intern_name_role_tabelle();
   $sql=self::intern_db_zugriff();
   return $sql->setQuery('DELETE FROM '.$table.' WHERE id='.$id);
   }
public static function set_roles() {
   #   Einrichtung von je einer Benutzerrolle pro konfigurierter Terminkategorie.
   #   Zur Kontrolle werden die Rollen in Form eines nummerierten Arrays zurueck
   #   gegeben (Nummerierung ab 1, jede Rolle als assoziatives Array).
   #
   $addon=self::this_addon;
   #
   # --- Benutzerrollen fuer die konfigurierten Terminkategorien setzen
   $kats=$addon::kal_conf_terminkategorien();
   $roles=self::intern_insert_user_roles($kats,$addon::ROLE_KAT);
   #
   # --- geloeschte Terminkategorien (Konfiguration), zugehoerige Rollen entfernen
   $idmax=$kats[count($kats)-1]['id'];
   $rollen=self::intern_select_user_roles($addon::ROLE_KAT);
   $pos=strlen($addon::ROLE_KAT)+1;
   for($i=1;$i<=count($rollen);$i=$i+1):
      $id=$rollen[$i]['id'];
      $name=$rollen[$i]['name'];
      $katid=intval(substr($name,$pos));
      if($katid>$idmax) self::intern_delete_user_role($id);
      endfor;
   #
   # --- Rueckgabe der definierten Terminkategorie-Rollen
   return $roles;
   }
public static function allowed_terminkategorien($art_id=0) {
   #   Rueckgabe der Ids der konfigurierten Terminkategorien, die ein Redakteur
   #   verwenden darf, als nummeriertes Array (Nummerierung ab 1).
   #   Der Redakteur wird aus dem Parameter 'createuser' (login) eines Artikels
   #   (i.d.R. des aktuellen Artikels) ermittelt. Der Administrator hat Zugriff
   #   auf alle konfigurierten Terminkategorien.
   #   $art_id           Id des Artikels (=0: Id des aktuellen Artikels)
   #                     (>0: nur zu Testzwecken)
   #
   $addon=self::this_addon;
   $table1=self::intern_name_user_tabelle();
   $table2=self::intern_name_role_tabelle();
   $sql=self::intern_db_zugriff();
   #
   # --- erlaubte Terminkategorien
   $kats=$addon::kal_conf_terminkategorien();
   #
   # --- Redakteur als create_user des Artikels (login)
   $aid=$art_id;
   if($aid<=0) $aid=rex_article::getCurrentId();
   $article=rex_article::get($aid);
   if($article==null) $article=rex_article::getCurrent();
   $login=$article->getCreateUser();
   #
   # --- CMS-User
   $users=$sql->getArray('SELECT * FROM '.$table1.' WHERE login=\''.$login.'\'');
   $admin=$users[0]['admin'];
   $role =$users[0]['role'];
   $katids='';
   if($admin>0):
     #
     # --- Administrator
     for($i=0;$i<count($kats);$i=$i+1) $katids=$katids.','.$kats[$i]['id'];
     else:
     #
     # --- Redakteur: Heraussuchen der Rollen 'Zugriff auf Terminkategorien'
     $ids=explode(',',$role);
     for($i=0;$i<count($ids);$i=$i+1):
        $id=$ids[$i];
        $roles=$sql->getArray('SELECT * FROM '.$table2.' WHERE id='.$id);
        #     Bestimmung der Kategorie-Id
        if(substr($roles[0]['name'],0,strlen($addon::ROLE_KAT))==$addon::ROLE_KAT):
          $perms=$roles[0]['perms'];
          $extras=json_decode($perms,TRUE)['extras'];     // = |kal_termine[id]|
          $extras=substr($extras,1,strlen($extras)-3);    // = kal_termine[id
          $kat_id=substr($extras,strpos($extras,'[')+1);  // = id
          $katids=$katids.','.$kat_id;
          endif;
        endfor;
     endif;
   if(!empty($katids)) $katids=substr($katids,1);
   #
   # --- Id-String in ein Array umspeichern
   $kids=array();
   if(!empty($katids)):
     $kid=explode(',',$katids);
     for($i=1;$i<=count($kid);$i=$i+1) $kids[$i]=$kid[$i-1];
     endif;
   return $kids;
   }
public static function register_perm_kategorien() {
   #   Registrieren der Zugriffserlaubnis auf die konfigurierten Terminkategorien
   #   in der Gruppe EXTRAS ('kal_termine[1]', 'kal_termine[2]',...). Zur
   #   Kontrolle Rueckgabe aller Permissions dieser Gruppe.
   #   Benutzt nur in der boot.php.
   #
   $addon=self::this_addon;
   $kats=$addon::kal_conf_terminkategorien();
   for($i=0;$i<count($kats);$i=$i+1):
      $perm=self::intern_perm($kats[$i]['id']);
      rex_perm::register($perm,$kats[$i]['name'],rex_perm::EXTRAS);      
      endfor;
   #
   # --- zur Kontrolle: Rueckgabe der Permissions der Gruppe EXTRAS
   return rex_perm::getAll(rex_perm::EXTRAS);
   }
#
#----------------------------------------- Module
public static function create_module($ident) {
   #   Einrichten/Aktualisieren eines Moduls in der Tabelle rex_module.
   #   Rueckgabe eines Erfolgs-Strings.
   #   $ident            einer der beiden folgenden Modul-Identifikations-Strings:
   #                     $addon::MODUL1_IDENT bzw. $addon::MODUL2_IDENT
   #
   $addon=self::this_addon;
   $table=self::intern_name_module_tabelle();
   $sql=self::intern_db_zugriff();
   $input='<?php
echo "<div>Einfach nur <big>Abbrechen</big> !</div>";
?>';
   if(str_contains($ident,'manage')):
     $name='Termine verwalten ('.$addon.')';
     else:
     $name='Termine anzeigen ('.$addon.')';
     endif;
   $output='<?php
'.$ident.';
?>';
   #
   # --- existiert der Modul schon?
   $where='name LIKE \'%'.$addon.'%\' AND output LIKE \'%'.$ident.'%\'';
   $mod=$sql->getArray('SELECT * FROM '.$table.' WHERE '.$where);
   $ret='Modul \''.$name.'\'';
   if(!empty($mod)):
     #     existiert schon:      update (name unveraendert)
     $id=$mod[0]['id'];
     $retin =$sql->setQuery('UPDATE '.$table.' SET  input=\''.$input.'\'  WHERE id='.$id);
     $retout=$sql->setQuery('UPDATE '.$table.' SET output=\''.$output.'\' WHERE id='.$id);
     if($retin and $retout) $ret=$ret.' aktualisiert';
     ;else:
     #     existiert noch nicht: insert
     $retupd=$sql->setQuery('INSERT INTO '.$table.' (name,input,output) '.
           'VALUES (\''.$name.'\',\''.$input.'\',\''.$output.'\')');
     if($retupd) $ret=$ret.' eingefügt';
     endif;
   return $ret;
   }
public static function intern_get_modulid() {
   #   Rueckgabe der Id des Moduls 'Termine anzeigen'.
   #
   $addon=self::this_addon;
   $table=self::intern_name_module_tabelle();
   $sql=self::intern_db_zugriff();
   $where='name LIKE \'%'.$addon.'%\' AND output LIKE \'%'.$addon::MODUL2_IDENT.'%\'';
   $mod=$sql->getArray('SELECT * FROM '.$table.' WHERE '.$where);
   if(!empty($mod)) return $mod[0]['id'];
   }
public static function read_menue_data($artid=0) {
   #   Rueckgabe der 4 Parameter fuer die Darstellung der Termine in Form eines
   #   assoziativen Arrays mit diesen Keys:
   #        ['men']      Nummer des Kalendermenues (nur 1, 6, 7 kommen infrage)
   #        ['datum']    Startdatum der Termine (tt.mm.jjjj),
   #                     im Falle der Terminliste: falls leer, heutiges Datum
   #        ['anztage']  Anzahl Tage, an denen die Termine liegen,
   #                     im Falle der Terminliste: falls leer, Anzahl Tage im Jahr
   #        ['kat_id']   Id einer Terminkategorie oder 0,
   #                     >0: die Termine sind beschraenkt auf die Kategorie mit dieser Id
   #                     =0: es werden alle Termine aller erlaubten Kategorien angezeigt
   #   Die Parameter sind im Slice des aktuellen Artikels gespeichert, der mit
   #   dem Modul 'Termine anzeigen' gebildet wurde.
   #   $artid            Id des Artikels (nicht der aktuelle Artikel: nur zu Testzwecken)
   #                     falls <=0: es wird der aktuelle Artikel angenommen
   #
   $table=self::intern_name_slice_tabelle();
   $sql=self::intern_db_zugriff();
   #
   # --- Modul-Id
   $mod_id=self::intern_get_modulid();
   #
   # --- Block im aktuellen Artikel, der mit diesem Modul gebildet wurde
   $art_id=$artid;
   if($art_id<=0) $art_id=rex_article::getCurrentId();
   $where='article_id='.$art_id.' AND module_id='.$mod_id;
   $slic=$sql->getArray('SELECT * FROM '.$table.' WHERE '.$where);
   if(empty($slic)) return array();
   #
   # --- Parameter auslesen und zurueck geben
   $param=array();
   $param['men']    =$slic[0]['value1'];   // Nummer des Kalendermenues (1, 6, 7)
   $param['datum']  =$slic[0]['value2'];   // Startdatum der Termine (leer: ab erstem Termin)
   $param['anztage']=$slic[0]['value3'];   // Anzahl Tage (0 = unbegrenzt)
   $param['kat_id'] =$slic[0]['value4'];   // Kategorie-Id (0 = alle Kategorien)
   return $param;
   }
public static function write_menue_data($key,$value) {
   #   Ueberschreiben eines Parameters fuer die Darstellung der Termine. 
   #   $key              Key des Parameters
   #   $value            neuer Wert des Parameters:
   #                     Nummer des Kalendermenues (1, 6, 7)      [$key = 'men']
   #                     Startdatum der Termine                   [$key = 'datum']
   #                     Anzahl Tage, an denen die Termine liegen [$key = 'anztage']
   #                     Id einer Terminkategorie oder 0          [$key = 'kat_id']
   #   Die Parameter werden im Slice des aktuellen Artikels gespeichert,
   #   der mit dem Modul 'Termine anzeigen' gebildet wurde.
   #
   $table=self::intern_name_slice_tabelle();
   $sql=self::intern_db_zugriff();
   #
   # --- Modul-Id
   $mod_id=self::intern_get_modulid();
   #
   # --- Slice im aktuellen Artikel, der mit diesem Modul gebildet wurde
   $art_id=rex_article::getCurrentId();
   $where='article_id='.$art_id.' AND module_id='.$mod_id;
   $slic=$sql->getArray('SELECT * FROM '.$table.' WHERE '.$where);
   if(empty($slic)) return array();
   $slice_id=$slic[0]['id'];
   #
   # --- Nummer des valueXX-Keys
   if($key=='men')     $keynr=1;
   if($key=='datum')   $keynr=2;
   if($key=='anztage') $keynr=3;
   if($key=='kat_id')  $keynr=4;
   #
   # --- Slice-Parameter aktualisieren
   if($keynr<=0 or $keynr>=5):
     return FALSE;
     else:
     if(is_numeric($value)):
       $update='UPDATE '.$table.' SET value'.$keynr.'='.$value.' WHERE id='.$slice_id;
       else:
       $update='UPDATE '.$table.' SET value'.$keynr.'=\''.$value.'\' WHERE id='.$slice_id;
       endif;
     return $sql->setQuery($update);
     endif;
   }
}
?>
