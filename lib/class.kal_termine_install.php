<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2019
 */
#
class kal_termine_install {
#
public static function kal_create_table() {
   #   Erzeugen der Termintabelle (falls noch nicht vorhanden)
   #   benutzte functions:
   #      kal_termine_tabelle::kal_define_tabellenspalten()
   #
   $table='rex_kal_termine';
   $cols=kal_termine_tabelle::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $columns='';
   for($i=0;$i<count($cols);$i=$i+1)
      $columns=$columns.$keys[$i].' '.$cols[$keys[$i]][0].', ';
   $columns=substr($columns,0,strlen($columns)-2).', PRIMARY KEY ('.$keys[0].')';
   $query='CREATE TABLE IF NOT EXISTS '.$table.' ('.$columns.') '.
      'ENGINE=MyISAM  DEFAULT CHARSET=utf8;';
   $sql=rex_sql::factory();
   $sql->setQuery($query);
   }
public static function sql_action($sql,$query) {
   #   performing an SQL action using setQuery()
   #   including error message if fails
   #   $sql               SQL handle
   #   $query             SQL action
   #
   try {
        $sql->setQuery($query);
        $error='';
         } catch(rex_sql_exception $e) {
        $error=$e->getMessage();
        }
   if(!empty($error)) echo rex_view::error($error);
   }
public static function update_module($my_package,$name,$funin,$funout) {
   #   Insert oder Update eines Moduls
   #   $name              Teil 1 des Modul-Namens
   #   $my_package        AddOn-Name (= Teil 2 des Modul-Namens)
   #   $funin             Funktion, die den code des Input-Teils des Moduls zurueck gibt
   #   $funout            Funktion, die den code des Output-Teils des Moduls zurueck gibt
   #   used functions:
   #      self::sql_action($sql,$query)
   #
   # --- Read the codes of the module's sections
   $input=self::$funin();
   $output=self::$funout();
   #
   # --- Insert/update the module in table rex_module
   $table='rex_module';
   $fullname=$name.' ('.$my_package.')';
   $sql=rex_sql::factory();
   $query='SELECT * FROM '.$table.' WHERE name LIKE \'%'.$name.'%\'';
   $mod=$sql->getArray($query);
   if(count($mod[0])>0):
     # --- Module exists: update
     self::sql_action($sql,'UPDATE '.$table.' SET input=\''.$input.'\' '.
        'WHERE id='.$mod[0][id]);
     self::sql_action($sql,'UPDATE '.$table.' SET output=\''.$output.'\' '.
        'WHERE id='.$mod[0][id]);
     else:
     # --- Module does not exist: insert
     self::sql_action($sql,'INSERT INTO '.$table.' (name,input,output) '.
        'VALUES (\''.$fullname.'\',\''.$input.'\',\''.$output.'\')');
     endif;
   }
public static function mod_term_manage_in() {
   #   Code of the input section of the term manage module
   #
   $str='
<?php
$value[ 1]="REX_VALUE[ 1]";
$value[ 2]="REX_VALUE[ 2]";
$value[ 3]="REX_VALUE[ 3]";
$value[ 4]="REX_VALUE[ 4]";
$value[ 5]="REX_VALUE[ 5]";
$value[ 6]="REX_VALUE[ 6]";
$value[ 7]="REX_VALUE[ 7]";
$value[ 8]="REX_VALUE[ 8]";
$value[ 9]="REX_VALUE[ 9]";
$value[10]="REX_VALUE[10]";
$value[11]="REX_VALUE[11]";
$value[12]="REX_VALUE[12]";
$value[13]="REX_VALUE[13]";
$value[14]="REX_VALUE[14]";
$value[15]="REX_VALUE[15]";
$value[16]="REX_VALUE[16]";
$value[17]="REX_VALUE[17]";
$value[18]="REX_VALUE[18]";
$value[19]="REX_VALUE[19]";
$value[20]="REX_VALUE[20]";
#
$cols=kal_termine_tabelle::kal_define_tabellenspalten();
$nzcols=count($cols);
$arr=explode(":",$value[$nzcols]);
$action=$arr[0];
$pid=$arr[1];
#
# --- Startformular
if(empty($value[$nzcols]) or empty($value[1]) or $action==ACTION_START)
  echo kal_termine_formulare::kal_formular_startauswahl("");
#
# --- Eingabeformular
if($value[1]==ACTION_INSERT or $action==ACTION_INSERT):
  if($value[1]==ACTION_INSERT) $value[1]="";
  echo kal_termine_formulare::kal_formular_eingeben($value);
  endif;
#
# --- Suchformular
if($action==ACTION_SEARCH or
   $value[1]==ACTION_DELETE or $value[1]==ACTION_UPDATE or
   $value[1]==ACTION_COPY):
  if($value[1]==ACTION_DELETE or $value[1]==ACTION_UPDATE or
     $value[1]==ACTION_COPY):
    $value[5]=$value[1];
    $value[1]="";
    endif;
  echo kal_termine_formulare::kal_formular_suchen($value);
  endif;
#
# --- Loeschformular
if($action==ACTION_DELETE and $pid>0)
  echo kal_termine_formulare::kal_formular_loeschen($pid);
#
# --- Korrekturformular
if($action==ACTION_UPDATE and $pid>0):
  if($value[5]==ACTION_UPDATE)
    for($i=1;$i<=5;$i=$i+1) $value[$i]="";
  echo kal_termine_formulare::kal_formular_korrigieren($pid,$value);
  endif;
#
# --- Kopierformular
if($action==ACTION_COPY and $pid>0):
  if($value[5]==ACTION_COPY):
    for($i=1;$i<=5;$i=$i+1) $value[$i]="";
    $kop=0;
    $anz="";
    else:
    $kop=$value[1];
    $anz=$value[2];
    endif;
  echo kal_termine_formulare::kal_formular_kopieren($pid,$kop,$anz);
  endif;
#
# --- Loeschen aller REX-Variablen
$sql=rex_sql::factory();
$upd="UPDATE rex_article_slice SET ";
for($i=1;$i<=$nzcols;$i=$i+1) $upd=$upd."value".$i."=\"\", ";
$upd=substr($upd,0,strlen($upd)-2);
$upd=$upd." WHERE id=".strval(REX_SLICE_ID);
$sql->setQuery($upd);
?>';
   return str_replace("\\","\\\\",utf8_encode($str));
   }
public static function mod_term_manage_out() {
   #   Code of the output section of the term manage module
   #
   $str='
<?php
if(rex::isBackend())
  echo "<div>F&uuml;r die Terminverwaltung den Modul editieren!</div>";
?>';
   return str_replace("\\","\\\\",utf8_encode($str));
   }
public static function mod_term_menu_in() {
   #   Code of the input section of the term menu module
   #
   $str='
<?php
$menue="REX_VALUE[1]";
$kategorie="REX_VALUE[2]";
#
# --- Auswahl des Terminmenues
$stx="style=\"padding-left:20px; vertical-align:top;\"";
$start=kal_termine_menues::kal_define_menues();
echo "<table class=\"kal_table\">\n".
   "    <tr><td ".$stx.">Start-Terminmen&uuml;:</td>\n".
   "        <td ".$stx."><select name=\"REX_INPUT_VALUE[1]\">\n";
for($i=1;$i<=count($start);$i=$i+1)
   $st[$i]=utf8_encode($start[$i][1]);
for($i=1;$i<=count($st);$i=$i+1)
   if($menue==$st[$i]):
     echo "                ".
        "<option selected=\"selected\">".$st[$i]."</option>\n";
     else:
     echo "                <option>".$st[$i]."</option>\n";
     endif;
echo "            </select></td>\n".
     "        <td ".$stx.">Die Zeitabschnitte enthalten ".
     "zun&auml;chst<br/>immer den heutigen Tag.</td></tr>\n";
#
# --- Auswahl der Terminkategorie
$kat=kal_termine_config::kal_get_terminkategorien();
for($i=1;$i<=count($kat);$i=$i+1) $kat[$i]=utf8_encode($kat[$i]);
$kat[0]="";
echo "    <tr><td colspan=\"3\">&nbsp;</td></tr>\n".
   "    <tr><td ".$stx.">Terminkategorie:</td>\n".
   "        <td ".$stx."><select name=\"REX_INPUT_VALUE[2]\">\n";
for($i=0;$i<count($kat);$i=$i+1)
   if($kategorie==$kat[$i]):
     echo "                ".
        "<option selected=\"selected\">".$kat[$i]."</option>\n";
     else:
     echo "                <option>".$kat[$i]."</option>\n";
     endif;
echo "            </select></td>\n".
   "        <td ".$stx.">Es werden nur die Termine der ".
   "gew&auml;hlten<br/>Kategorie ausgefiltert und angezeigt<br/>".
   "(keine Angabe: Termine aller Kategorien)</td></tr>\n".
   "</table>\n";
?>';
   return str_replace("\\","\\\\",utf8_encode($str));
   }
public static function mod_term_menu_out() {
   #   Code of the output section of the term menu module
   #
   $str='
<?php
$menue="REX_VALUE[1]";
$kategorie="REX_VALUE[2]";
#
if(rex::isBackend()):
  if(empty($kategorie)):
    echo "<u>$menue:</u> Termine aller Kategorien\n";
    else:
    echo "<u>$menue:</u> Termine der Kategorie $kategorie\n";
    endif;
  else:
  # --- Ausgabe-Seite = aktuelle Seite
  $start=kal_termine_menues::kal_define_menues();
  for($i=1;$i<=count($start);$i=$i+1)
     if(utf8_encode($start[$i][1])==$menue) $men=$i;
  if(empty($men)) $men=1;
  echo kal_termine_menues::kal_menue($kategorie,"",$men);
  endif;
?>';
   return str_replace("\\","\\\\",utf8_encode($str));
   }
public static function mod_term_list_in() {
   #   Code of the input section of the term list module
   #
   $str='
<?php
$tage=REX_VALUE[1];
$kategorie=REX_VALUE[2];
#
# --- Ueberschrift
$strtage="";
if($tage>0) $strtage="und für die nächsten ".$tage." Tage ";
echo "<div><b>Alle Termine ab heute ".$strtage.
   "in Form einer Liste</b><br/>&nbsp;</div>\n".
   "<table class=\"kal_table\">\n";
$stx="style=\"padding-left:20px; white-space:nowrap;\"";
#
# --- Zeitraum in Anzahl Tage
echo "    <tr><td ".$stx.">Zeitraum für die Termine:</td>\n".
   "        <td ".$stx."><input type=\"text\" ".
   "style=\"width:50px; text-align:right;\" ".
   "name=\"REX_INPUT_VALUE[1]\" value=\"".$tage."\" /></td>".
   "        <td ".$stx.">(Anzahl Tage ab heute)</td></tr>\n";
#
# --- Auswahl der Terminkategorie
echo "    <tr><td ".$stx.">Termine dieser Kategorie:</td>\n".
   "        <td ".$stx."><select name=\"REX_INPUT_VALUE[2]\">\n";
$kat=kal_termine_config::kal_get_terminkategorien();
for($i=1;$i<=count($kat);$i=$i+1) $kat[$i]=utf8_encode($kat[$i]);
$kat[0]="";
echo "            ";
for($i=0;$i<count($kat);$i=$i+1)
   if($kategorie==$kat[$i]):
     echo "<option selected=\"selected\">".$kat[$i]."</option>\n";
     else:
     echo "<option>".$kat[$i]."</option>\n";
     endif;
echo "</select></td>".
   "        <td ".$stx.">(keine Angabe: Termine aller Kategorien)</td></tr>\n".
   "</table>\n";
?>';
   return str_replace("\\","\\\\",utf8_encode($str));
   }
public static function mod_term_list_out() {
   #   Code of the output section of the term list module
   #
   $str='
<?php
$tage=REX_VALUE[1];
$kategorie=REX_VALUE[2];
$von=kal_termine_kalender::kal_heute();
$bis=kal_termine_kalender::kal_datum_vor_nach($von,$tage);
$term=kal_termine_tabelle::kal_select_termine($von,$bis,$kategorie,"");
if(!rex::isBackend()):
  echo kal_termine_formulare::kal_terminliste($term);
  else:
  $st="";
  if(!empty($kategorie)) $st=", Kategorie \"".$kategorie."\"";
  echo "<p>Ausgabe der Termine nur im Frontend ".
     "<small>(".$von." - ".$bis.$st.")</small></p>\n";
  endif;
?>';
   return str_replace("\\","\\\\",utf8_encode($str));
   }
}
?>
