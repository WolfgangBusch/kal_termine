<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Dezember 2019
 */
#
class kal_termine_install {
#
public static function kal_create_table() {
   #   Erzeugen der Termintabelle (falls noch nicht vorhanden)
   #   benutzte functions:
   #      kal_termine_config::kal_define_tabellenspalten()
   #
   $table='rex_kal_termine';
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $columns='';
   for($i=0;$i<count($cols);$i=$i+1)
      $columns=$columns.$keys[$i].' '.$cols[$keys[$i]][0].', ';
   $columns=substr($columns,0,strlen($columns)-2).', PRIMARY KEY ('.$keys[0].')';
   $query='CREATE TABLE IF NOT EXISTS '.$table.' ('.$columns.') CHARSET=utf8;';
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
public static function build_modules($mypackage) {
   #   creating / updating a number of modules in table rex_module
   #   $mypackage          package name
   #   functions used:
   #      self::define_modules($mypackage)
   #      self::sql_action($sql,$query)
   #
   $table='rex_module';
   $modules=self::define_modules($mypackage);
   for($i=1;$i<=count($modules);$i=$i+1):
      #
      # --- module sources: name input, output 
      #     and string for identifying the input part 
      $name  =$modules[$i]['name'];
      $input =$modules[$i]['input'];
      $output=$modules[$i]['output'];
      $ident =$modules[$i]['ident'];
      #
      # --- module exists already?
      $sql=rex_sql::factory();
      $where='name LIKE \'%'.$mypackage.'%\' AND input LIKE \'%'.$ident.'%\'';
      $query='SELECT * FROM '.$table.' WHERE '.$where;
      $mod=$sql->getArray($query);
      if(!empty($mod)):
        #     existing:         update (name unchanged)
        $id=$mod[0]['id'];
        self::sql_action($sql,'UPDATE '.$table.' SET  input=\''.$input.'\'  WHERE id='.$id);
        self::sql_action($sql,'UPDATE '.$table.' SET output=\''.$output.'\' WHERE id='.$id);
        else:
        #     not yet existing: insert
        self::sql_action($sql,'INSERT INTO '.$table.' (name,input,output) '.
           'VALUES (\''.$name.'\',\''.$input.'\',\''.$output.'\')');
        endif;
      endfor;
   }

public static function define_modules($mypackage) {
   #   defining some module sources and returning them as array:
   #      $mod[$i]['name']    the module's name
   #      $mod[$i]['input']   source of the module's input part
   #      $mod[$i]['output']  source of the module's output part
   #                          ($i = 1, 2, ...)
   #   $mypackage          package name
   #
   # --- first module
   $name[1]='Termine verwalten ('.$mypackage.')';
   $in[1]='<?php
$value[ 1]=REX_VALUE[ 1];
$value[ 2]=REX_VALUE[ 2];
$value[ 3]=REX_VALUE[ 3];
$value[ 4]=REX_VALUE[ 4];
$value[ 5]=REX_VALUE[ 5];
$value[ 6]=REX_VALUE[ 6];
$value[ 7]=REX_VALUE[ 7];
$value[ 8]=REX_VALUE[ 8];
$value[ 9]=REX_VALUE[ 9];
$value[10]=REX_VALUE[10];
$value[11]=REX_VALUE[11];
$value[12]=REX_VALUE[12];
$value[13]=REX_VALUE[13];
$value[14]=REX_VALUE[14];
$value[15]=REX_VALUE[15];
$value[16]=REX_VALUE[16];
$value[17]=REX_VALUE[17];
$value[18]=REX_VALUE[18];
#
# --- Verwaltung der Termine
kal_termine_module::kal_manage_termine($value);
#
# --- Loeschen aller REX-Variablen
$sql=rex_sql::factory();
$upd="UPDATE rex_article_slice SET ";
for($i=1;$i<=COL_ANZAHL;$i=$i+1) $upd=$upd."value".$i."=\"\", ";
$upd=substr($upd,0,strlen($upd)-2);
$upd=$upd." WHERE id=".strval(REX_SLICE_ID);
$sql->setQuery($upd);
?>';
   $out[1]='<?php
if(rex::isBackend())
  echo "<div><span class=\"kal_form_msg\">".
     "Für die Terminverwaltung den Modul editieren!</span></div>";
?>';
   $ident[1]='kal_manage_termine';
   #
   # --- second module
   $name[2]='Auswahl eines Start-Kalendermenüs ('.$mypackage.')';
   $in[2]='<?php
$men="REX_VALUE[1]";
$kategorie="REX_VALUE[2]";
echo kal_termine_module::kal_kalendermenue($men,$kategorie);
?>';
   $out[2]='<?php
$men="REX_VALUE[1]";
$kategorie="REX_VALUE[2]";
if(empty($men)) $men=1;
$menues=kal_termine_menues::kal_define_menues();
$menue=$menues[$men][1];
if(rex::isBackend()):
  $str="<u>aller</u> Kategorien";
  if(!empty($kategorie)) $str="der Kategorie <u>$kategorie</u>";
  echo "<div><span class=\"kal_form_msg\"><u>$menue:</u> ".
     "Termine ".$str."</div>\n";
  else:
  echo kal_termine_menues::kal_menue($kategorie,"",$men);
  endif;
?>';
   $ident[2]='kal_kalendermenue';
   #
   # --- third module
   $name[3]='Ausgabe einer Standard-Terminliste ('.$mypackage.')';
   $in[3]='<?php
$ab=REX_VALUE[1];
$tage=REX_VALUE[2];
$kateg=REX_VALUE[3];
echo kal_termine_module::kal_std_terminliste($ab,$tage,$kateg);
?>';
   $out[3]='<?php
$ab=REX_VALUE[1];
if(empty($ab)) $ab=kal_termine_kalender::kal_heute();
$tage=REX_VALUE[2];
$kateg=REX_VALUE[3];
#
$bis=kal_termine_kalender::kal_datum_vor_nach($ab,$tage);
$term=kal_termine_tabelle::kal_select_termine($ab,$bis,$kateg,"");
if(!rex::isBackend()):
  echo kal_termine_formulare::kal_terminliste($term);
  else:
  $sta=$ab." - ".$bis;
  $stb="alle Kategorien";
  if(!empty($kateg)) $stb="Kategorie \"".$kateg."\"";
  $stc=count($term)." Termine";
  echo "<div><span class=\"kal_form_msg\">".
     $sta." &nbsp; (".$stb."): &nbsp; ".$stc."</span> &nbsp; ".
     "<small>(Terminliste nur im Frontend)</small></div>\n";
  endif;
?>';
   $ident[3]='kal_std_terminliste';
   #
   # --- returning the modules codes
   for($i=1;$i<=count($name);$i=$i+1)
      $modules[$i]=array(
         'name'=>$name[$i],
         'input'=>str_replace('\\','\\\\',$in[$i]),
         'output'=>str_replace('\\','\\\\',$out[$i]),
         'ident'=>$ident[$i]);
   return $modules;
   }
}
?>
