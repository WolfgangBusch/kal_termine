<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version September 2021
*/
#
class kal_termine_install {
#
public static function kal_generate_katids($term) {
   #   Generating of category ids to event categories defined in previous add-on versions.
   #   A numbered array (numbering from 1) of replacement data is returned to each event,
   #   structured as associative arrays:
   #     [COL_PID]     Event Id
   #     ['kategorie'] Name of category, according to previous add-on versions (<=2.2.1)
   #     [COL_KATID]   generated Id of that category
   #   $term           numbered array (numbering from 0) of stored events
   #   
   $kats=array();
   $m=0;
   for($i=0;$i<count($term);$i=$i+1):
      $j=$i+1;
      $kats[$j][COL_PID]=$term[$i][COL_PID];
      $kats[$j]['kategorie']=$term[$i]['kategorie'];
      $kats[$j][COL_KATID]=0;
      $neu=TRUE;
      for($k=1;$k<=$i;$k=$k+1)
         if($kats[$k]['kategorie']==$kats[$j]['kategorie']):
           $neu=FALSE;
           $kats[$j][COL_KATID]=$kats[$k][COL_KATID];
           endif;
      if($neu):
        $m=$m+1;
        $kats[$j][COL_KATID]=$m;
        endif;
      endfor;
   return $kats;
   }
public static function kal_generated_katids($kats) {
   #   Returns the currently defined event categories (according to previous add-on
   #   version) and the generated category ids in the form of a numbered array
   #   (numbering from 1). Each array element consists of an associative array
   #   with these parameters:
   #     ['kategorie'] Name of category, according to previous add-on versions (<=2.2.1)
   #     [COL_KATID]   generated Id of that category
   #   $kats           numbered array (numbering from 1) of stored events
   #                   as generated in kal_generate_katids
   #
   $kid=array();
   $m=0;
   for($i=1;$i<=count($kats);$i=$i+1):
      $id=$kats[$i][COL_KATID];
      $neu=TRUE;
      for($k=1;$k<=count($kid);$k=$k+1)
         if($kid[$k][COL_KATID]==$id):
           $neu=FALSE;
           break;
           endif;
      if($neu):
        $m=$m+1;
        $kid[$m][COL_KATID]=$id;
        $kid[$m]['kategorie']=$kats[$i]['kategorie'];
        endif;
      endfor;
   return $kid;
}
public static function kal_create_tables() {
   #   creating the add-on tables if they do not already exist
   #   used functions:
   #      self::kal_generate_katids($term)
   #      self::kal_generated_katids($kats)
   #      kal_termine_config::kal_define_tabellenspalten()
   #
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- create the table of events if it does not already exist
   $columns='';
   for($i=0;$i<count($cols);$i=$i+1)
      $columns=$columns.$keys[$i].' '.$cols[$keys[$i]][0].', ';
   $columns=substr($columns,0,strlen($columns)-2).', PRIMARY KEY ('.$keys[0].')';
   $query='CREATE TABLE IF NOT EXISTS '.TAB_NAME.' ('.$columns.') CHARSET=utf8;';
   $sql=rex_sql::factory();
   $sql->setQuery($query);
   #
   # --- add columns COL_TAGE, COL_WOCHEN and COL_KATID if they do not already exist
   #     (in case of upgrade from version 2.2.1 or older)
   $coln=$sql->getArray('SHOW COLUMNS FROM '.TAB_NAME);
   $ex=FALSE;
   for($i=0;$i<count($coln);$i=$i+1) if($coln[$i]['Field']==COL_TAGE) $ex=TRUE;
   if(!$ex):
     $query='ALTER TABLE '.TAB_NAME.' ADD '.COL_TAGE.' int(11) NOT NULL DEFAULT 1 AFTER '.COL_ENDE;
     $sql->setQuery($query);
     endif;
   $ex=FALSE;
   for($i=0;$i<count($coln);$i=$i+1) if($coln[$i]['Field']==COL_WOCHEN) $ex=TRUE;
   if(!$ex):
     $query='ALTER TABLE '.TAB_NAME.' ADD '.COL_WOCHEN.' int(11) NOT NULL DEFAULT 0 AFTER '.COL_TAGE;
     $sql->setQuery($query);
     endif;
   $ex=FALSE;
   for($i=0;$i<count($coln);$i=$i+1) if($coln[$i]['Field']==COL_KATID) $ex=TRUE;
   if(!$ex):
     $query='ALTER TABLE '.TAB_NAME.' ADD '.COL_KATID.' int(11) NOT NULL DEFAULT 1 AFTER '.COL_KOMM;
     $sql->setQuery($query);
     endif;
   #
   # ---------- upgrade from version 2.2.1 or older ----------
   #
   $coln=$sql->getArray('SHOW COLUMNS FROM '.TAB_NAME);
   $ex=FALSE;
   for($i=0;$i<count($coln);$i=$i+1) if($coln[$i]['Field']=='kategorie') $ex=TRUE;
   if(!$ex) return;   // already Version >= 3.0
   #
   # --- table TAB_NAME: insert category Ids generated from category names
   $term=$sql->getArray('SELECT * FROM '.TAB_NAME);
   $kats=self::kal_generate_katids($term);
   for($i=1;$i<=count($kats);$i=$i+1):
      $pid=$kats[$i][COL_PID];
      $kat_id=$kats[$i][COL_KATID];
      $update='UPDATE '.TAB_NAME.' SET '.COL_KATID.'='.$kat_id.' WHERE '.COL_PID.'='.$pid;
      $sql->setQuery('UPDATE '.TAB_NAME.' SET '.COL_KATID.'='.$kat_id.' WHERE '.COL_PID.'='.$pid);
      endfor;
   #
   # --- take the transferred categories as additional configuration data
   #     default settings
   $settings=kal_termine_config::kal_default_config();
   #     category ids
   $kid=self::kal_generated_katids($kats);
   for($i=1;$i<=count($kid);$i=$i+1):   
      $key=KAL_KAT.$kid[$i][COL_KATID];
      $val=$kid[$i]['kategorie'];
      $settings[$key]=$val;
      endfor;
   kal_termine_config::kal_set_config($settings);
   #
   # --- at least: delete column 'kategorie'
   $sql->setQuery('ALTER TABLE '.TAB_NAME.' DROP COLUMN kategorie');
   }
public static function build_modules() {
   #   creating / updating a number of modules in table rex_module
   #   functions used:
   #      self::define_modules()
   #
   $table='rex_module';
   $modules=self::define_modules();
   for($i=1;$i<=count($modules);$i=$i+1):
      #
      # --- module sources: name input, output 
      #     and string for identifying the input part 
      $name  =$modules[$i]['name'];
      $input =$modules[$i]['input'];
      $output=$modules[$i]['output'];
      $ident =$modules[$i]['ident'];
      $idname='output';   // 'input' or 'output'
      #
      # --- module exists already?
      $sql=rex_sql::factory();
      $where='name LIKE \'%'.PACKAGE.'%\' AND '.$idname.' LIKE \'%'.$ident.'%\'';
      $query='SELECT * FROM '.$table.' WHERE '.$where;
      $mod=$sql->getArray($query);
      if(!empty($mod)):
        #     existing:         update (name unchanged)
        $id=$mod[0]['id'];
        $sql->setQuery('UPDATE '.$table.' SET  input=\''.$input.'\'  WHERE id='.$id);
        $sql->setQuery('UPDATE '.$table.' SET output=\''.$output.'\' WHERE id='.$id);
        else:
        #     not yet existing: insert
        $sql->setQuery('INSERT INTO '.$table.' (name,input,output) '.
              'VALUES (\''.$name.'\',\''.$input.'\',\''.$output.'\')');
        endif;
      endfor;
   }
public static function define_modules() {
   #   defining some module sources and returning them as array:
   #      $mod[$i]['name']    the module's name
   #      $mod[$i]['input']   source of the module's input part
   #      $mod[$i]['output']  source of the module's output part
   #                          ($i = 1, 2, ...)
   #
   $name  =array();
   $in    =array();
   $out   =array();
   $indent=array();
   #
   # --- first module
   $name[1]='Termine verwalten ('.PACKAGE.')';
   $in[1]='<?php
echo "<div>Einfach nur <big>Abbrechen</big> !</div>\n";
?>';
   $out[1]='<?php
echo kal_termine_module::kal_manage_termine();
?>';
   $ident[1]='kal_termine_module::kal_manage_termine';
   #
   # --- second module
   $name[2]='Termine anzeigen ('.PACKAGE.')';
   $in[2]='<?php
$men=REX_VALUE[1];
$von=REX_VALUE[2];
$anztage=REX_VALUE[3];
echo kal_termine_module::kal_terminmenue_in($men,$von,$anztage)
?>';
   $out[2]='<?php
$men=REX_VALUE[1];
$von=REX_VALUE[2];
$anztage=REX_VALUE[3];
echo kal_termine_module::kal_terminmenue_out($men,$von,$anztage);
?>';
   $ident[2]='kal_termine_module::kal_terminmenue';
   #
   # --- returning the modules codes
   $modules=array();
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
