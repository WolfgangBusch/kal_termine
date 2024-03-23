<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2024
 */
class kal_termine_install {
#
#   create_table()
#   add_column_monate()
#   define_modules()
#   build_modules()
#
const this_addon=kal_termine::this_addon;   // Name des AddOns
#
public static function create_table() {
   #   Creating the table of events if it does not already exist.
   #   Called in install.php, only.
   #   used functions:
   #      $addon::kal_define_tabellenspalten()
   #
   $addon=self::this_addon;
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   $table=rex::getTablePrefix().self::this_addon;
   $columns='';
   for($i=0;$i<count($cols);$i=$i+1)
      $columns=$columns.$keys[$i].' '.$cols[$keys[$i]][0].', ';
   $columns=substr($columns,0,strlen($columns)-2).', PRIMARY KEY ('.$keys[0].')';
   $query='CREATE TABLE IF NOT EXISTS '.$table.' ('.$columns.') CHARSET=utf8;';
   $sql=rex_sql::factory();
   $sql->setQuery($query);
   }
public static function add_column_monate() {
   #   Adding the table column 'monate' if not yet exists (update to version 3.5
   #   or higher from older versions).
   #   Called in install.php, only.
   #
   $addon=self::this_addon;
   $MONATE=$addon::TAB_KEY[8];   // key monate
   $table=rex::getTablePrefix().self::this_addon;
   $sql=rex_sql::factory();
   $coln=$sql->getArray('SHOW COLUMNS FROM '.$table);
   for($i=0;$i<count($coln);$i=$i+1)
      if($coln[$i]['Field']==$MONATE) return 'key "'.$MONATE.'" already exists';
   #
   # --- add column and set values on 0
   $alter='ALTER TABLE '.$table.' ADD '.$MONATE.' int(11) NOT NULL DEFAULT 0';
   $res=$sql->setQuery($alter);
   if($res):
     return 'key "'.$MONATE.'" inserted';
     else:
     return 'key "'.$MONATE.'" could not be inserted';
     endif;
   }
public static function define_modules() {
   #   Defining some module sources and returning them as array:
   #      $mod[$i]['name']    the module's name
   #      $mod[$i]['input']   source of the module's input part
   #      $mod[$i]['output']  source of the module's output part
   #                          ($i = 1, 2, ...)
   #
   $addon=self::this_addon;
   $name =array();
   $in   =array();
   $out  =array();
   $ident=array();
   #
   # --- first module
   $name[1] ='Termine verwalten ('.self::this_addon.')';
   $ident[1]=$addon.'_module::kal_manage_termine()';
   $in[1]   ='<?php
echo "<div>Einfach nur <big>Abbrechen</big> !</div>\n";
?>';
   $out[1]  ='<?php
echo '.$ident[1].';
?>';
   #
   # --- second module
   $name[2] ='Termine anzeigen ('.self::this_addon.')';
   $ident[2]=$addon.'_module::kal_terminmenue_out';
   $in[2]   ='<?php
$men=REX_VALUE[1];
$von=REX_VALUE[2];
$anztage=REX_VALUE[3];
$kid=REX_VALUE[4];
echo '.$addon.'_module::kal_terminmenue_in($men,$von,$anztage,$kid);
?>';
   $out[2]  ='<?php
$men=REX_VALUE[1];
$von=REX_VALUE[2];
$anztage=REX_VALUE[3];
$kid=REX_VALUE[4];
echo '.$ident[2].'($men,$von,$anztage,$kid);
?>';
   #
   # --- returning the modules codes
   $modules=array();
   for($i=1;$i<=count($name);$i=$i+1)
      $modules[$i]=array(
         'name'  =>$name[$i],
         'input' =>str_replace('\\','\\\\',$in[$i]),
         'output'=>str_replace('\\','\\\\',$out[$i]),
         'ident' =>$ident[$i]);
   return $modules;
   }
public static function build_modules() {
   #   Creating / updating a number of modules in table rex_module.
   #   Called in install.php, only.
   #   functions used:
   #      self::define_modules()
   #
   $addon=self::this_addon;
   $table=rex::getTablePrefix().'module';
   $modules=self::define_modules();
   for($i=1;$i<=count($modules);$i=$i+1):
      #
      # --- module sources: name input, output 
      #     and string for identifying the input/output part 
      $name  =$modules[$i]['name'];
      $input =$modules[$i]['input'];
      $output=$modules[$i]['output'];
      $ident =$modules[$i]['ident'];
      $idname=$addon::MODULE_IN_OUT; // 'input' or 'output'
      #
      # --- module exists already?
      $sql=rex_sql::factory();
      $where='name LIKE \'%'.self::this_addon.'%\' AND '.$idname.' LIKE \'%'.$ident.'%\'';
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
}
?>
