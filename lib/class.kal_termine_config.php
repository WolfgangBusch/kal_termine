<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
*/
#
class kal_termine_config {
#
#----------------------------------------- Methoden
#   Erzeugen der Stylesheet-Datei
#      kal_define_css()
#      kal_write_css()
#   Erzeugen der zweiten Stylesheet-Datei
#      kal_termlist_colors($nz)
#      kal_read_termlist_colors()
#      kal_get_termlist_colors()
#      kal_html_termlist_colors($colors)
#      kal_define_termlist_css($param)
#      kal_add_termlist_css($param)
#      kal_config_termlist()
#   Erzeugen der Konfigurationsdaten
#      kal_config_form($readsett,$entf_kat,$anzdelterm)
#      kal_lost_termine()
#      kal_new_config($settconf)
#      kal_config()
#
#----------------------------------------- Konstanten
const this_addon=kal_termine::this_addon;   // Name des AddOns
#
#----------------------------------------- Erzeugen der Stylesheet-Datei
public static function kal_define_css() {
   #   Rueckgabe der Quelle einer Stylesheet-Datei, basierend auf den
   #   konfigurierten Farben fuer die Kalendermenues.
   #
   $addon=self::this_addon;
   #
   # --- Farben
   $farben=$addon::kal_farben();
   $kalcol=array();
   for($i=1;$i<=count($farben);$i=$i+1) $kalcol[$i]=$farben[$i]['rgb'];
   #
   # --- CSS-Formate
   $bradius='border-radius:0.25em';
   $form_box='padding:0.25em; border-collapse:separate; ';
   $form_col=array();
   $form_col[1]=$form_box.'
    color:'.$kalcol[1].'; background-color:transparent;
    border:solid 1px '.$kalcol[1].'; '.$bradius.';';
   $form_col[2]=$form_box.'
    color:'.$kalcol[2].'; background-color:transparent;
    border:solid 1px '.$kalcol[2].'; '.$bradius.';';
   $form_col[3]=$form_box.'
    color:'.$kalcol[1].'; background-color:'.$kalcol[3].';
    border:solid 1px '.$kalcol[1].'; '.$bradius.';';
   $form_col[4]=$form_box.'
    color:'.$kalcol[1].'; background-color:'.$kalcol[4].';
    border:solid 1px '.$kalcol[1].'; '.$bradius.';';
   $form_col[5]=$form_box.'
    color:'.$kalcol[1].'; background-color:'.$kalcol[5].';
    border:solid 1px '.$kalcol[1].'; '.$bradius.';';
   $form_col[6]=$form_box.'
    color:'.$kalcol[6].'; background-color:transparent;
    border:solid 1px '.$kalcol[6].'; '.$bradius.';';
   $form_col[7]=$form_box.'
    color:'.$kalcol[6].'; background-color:'.$kalcol[7].';
    border:solid 1px '.$kalcol[6].'; '.$bradius.';';
   $form_col[8]=$form_box.'
    color:'.$kalcol[8].'; background-color:transparent;
    border:dotted 1px '.$kalcol[8].'; '.$bradius.';';
   $form_btn=$form_box.'
    color:'.$kalcol[1].'; background-color:'.$kalcol[5].';
    border:solid 2px '.$kalcol[4].'; '.$bradius.';
    box-shadow:2px 2px 2px '.$kalcol[3].'; cursor:pointer;';
   $form_btn_save=$form_box.'
    color:white; background-color:'.$kalcol[2].';
    border:solid 2px '.$kalcol[1].'; '.$bradius.';
    box-shadow:2px 2px 2px '.$kalcol[3].'; cursor:pointer;';
   $form_transparent=$form_box.'
    color:'.$kalcol[1].'; background-color:transparent;
    border:solid 1px transparent; '.$bradius.';';
   $form_monat=$form_box.'
    color:white; background-color:'.$kalcol[3].';
    border:solid 1px '.$kalcol[3].'; '.$bradius.';';
   $form_tview=$form_box.'
    color:'.$kalcol[1].'; background-color:white;
    border:solid 1px '.$kalcol[1].'; '.$bradius.';';
   #
   # --- Stylesheet-Datei
   $string='/*   T e r m i n k a l e n d e r   */

/*   Allgemeines   */
button, textarea { font-family:inherit; }  /* sonst sans serif-Font */
.kal_linkbut  { cursor:pointer; text-decoration:none; }
.kal_overline { text-decoration:overline; }
.kal_bold     { font-weight:bold; }
.kal_small    { font-size:0.8em; }
.kal_normal   { font-size:1em; }
.kal_lightbig { font-size:1.2em; }
.kal_big      { font-size:1.4em; }
.kal_bigbig   { font-size:1.6em; }
.kal_icon     { padding:0.25em; }
.kal_transp   { margin:0; padding:0; text-align:left; border:none;
    color:inherit; background-color:transparent; }
.kal_table    { border-collapse:separate; border-spacing:0.25em; background-color:inherit; }
.kal_box      { '.$form_col[1].' }
.kal_updown   { '.$form_col[5].' }
.kal_msg      { '.$form_col[7].' }
.kal_btn_save { '.$form_btn_save.' }
.kal_btn      { '.$form_btn.' }
.kal_100pro   { width:100%; }
.kal_basecol  { color:'.$kalcol[1].'; }
.kal_fail     { color:red; }
.kal_black    { color:inherit; }
.kal_select   { '.$form_box.$bradius.';
    color:'.$kalcol[1].'; background-color:'.$kalcol[5].'; }
.kal_short    { min-width:4em; max-width:4em; }
.kal_indent   { padding-left:20px; }
.kal_olul     { margin:0; padding-left:30px; }
.kal_olul li  { padding-left:5px; }
.kal_code2    { color:'.$addon::COLOR_CODE2.'; background-color:'.$addon::BGCOLOR_CODE2.'; }
.kal_warning  { margin:1em 0 1em 0; padding:1em; background-color:'.$addon::BGCOLOR_WARNING.'; color:white; }
.kal_botpad   { padding-bottom:0.5em; }
.kal_hide     { display:none; }
.kal_newterm  { background-color:transparent; position:sticky;
    position: -webkit-sticky /* wegen Safari-Browser */; bottom:1em; }
.kal_plus     { '.$form_col[4].' font-size:1.8em; }
.kal_uhr_feld { min-width:2em; max-width:2em; text-align:center; font-size:2em; }
.kal_uhrline  { line-height:0; }
';
   #
   $string=$string.'
/*   Termin-Eingabeformular   */
.form_nowrap   { white-space:nowrap; }
.form_right    { text-align:right; }
.form_pad      { padding-left:1em; text-align:left; }
.form_left2    { width:10em; vertical-align:top; white-space:nowrap; }
.form_text     { width:30em; padding:0 0.25em 0 0.25em; }
.form_date     { width: 6em; padding:0 0.25em 0 0.25em; }
.form_time     { width: 4em; padding:0 0.25em 0 0.25em; }
.form_int      { width: 3em; padding:0 0.25em 0 0.25em; }
.form_text2    { line-height:1.2em; min-height:1.2em; border:solid 1px grey; }
.form_empline  { line-height:0.4em; }
.form_readonly { background-color:'.$addon::BGCOLOR_READONLY.'; }
.form_th       { max-width:5em; min-width:5em; text-align:left; font-weight:normal; white-space:nowrap; }
@media screen and (max-width:'.$addon::CSS_MOBILE.'em) {
    .form_text { width:100%; }
    }
';
   #
   $string=$string.'
/*   Formulare im Backend   */
.conf_indent { padding:0 0.1em 0 1.5em;  vertical-align:top; white-space:nowrap; }
.conf_undent { padding:0 0.1em 0 0.25em; vertical-align:top; white-space:nowrap; }
.conf_number { padding-right:1em; text-align:right; }
.conf_inpint { width:4em;  padding:0 0.25em 0 0.25em; text-align:right; }
.conf_inptxt { width:14em; padding:0 0.25em 0 0.25em; }
';
   #
   # --- umrandete Boxen
   $string=$string.'
/*   Konfiguration, Farbkombinationen fuer Kalenderfelder   */';
for($i=1;$i<=count($form_col);$i=$i+1) $string=$string.'
.'.$addon::CSS_COLS.$i.' { '.$form_col[$i].' }';
   #
   # --- Monatsmenue
   $tagwidth='1.2em';
   $monwidth='9em';
   $strwidth=10;   // Streifen fuer Monatstage mit Terminen, Streifenbreite 10%
   $string=$string.'
   
/*   Monatsmenue   */
.mm_tag    { text-align:right;  min-width:'.$tagwidth.'; max-width:'.$tagwidth.'; line-height:0; }
.mm_ueber  { text-align:center; min-width:'.$monwidth.'; max-width:'.$monwidth.'; }
.mm_wot    { text-align:center; min-width:'.$tagwidth.'; max-width:'.$tagwidth.'; color:'.$kalcol[2].'; }
.mm_kw     { text-align:right; color:'.$kalcol[2].'; }
.mm_newter { min-width:3em; max-width:3em; }
.mm_so_box { '.$form_col[4].' }
.mm_wo_box { '.$form_col[5].' }
.mm_he_box { '.$form_col[7].' }
.mm_xx_box { '.$form_col[8].' }
'.$addon::kal_hatch_gen($strwidth,$kalcol[3]);
   #
   # --- Monats-/Wochen-/Tagesblatt
   $string=$string.'

/*   Monats-/Wochen-/Tagesblatt   */
.mwt_table   { border-collapse:separate; border-spacing:0.1em; padding:0; margin:0;
    background-color:inherit; }
.mwt_head    { padding:0 0.25em 0 0.25em; text-align:center; }
.mwt_ueber   { padding:0 0.25em 0 0.25em; text-align:center; width:100%; }
.mwt_so_box  { '.$form_col[4].' padding:0 !important; }
.mwt_wo_box  { '.$form_col[5].' padding:0 !important; }
.mwt_he_box  { '.$form_col[7].' padding:0 !important; }
.mwt_th      { max-width:6em; padding:0.25em; color:'.$kalcol[2].'; white-space:nowrap; }
.mwt_hr      { margin:0; border:none; }
.mwt_uhrzlst { visibility:visible; }
@media screen and (max-width:'.$addon::CSS_MOBILE.'em) {
    .mwt_th      { padding:0; white-space:normal; }
    .mwt_hr      { margin:0.25em; border-top:solid 1px '.$kalcol[1].'; }
    .mwt_uhrzlst { visibility:collapse; }
    }
';
   #
   # --- Stundenleiste
   $settings=$addon::kal_get_config();
   $stauhrz=$settings[$addon::STAUHRZ_KEY];
   $enduhrz=$settings[$addon::ENDUHRZ_KEY];
   $st1=floatval(100/($enduhrz-$stauhrz));
   $st2=2*$st1;
   $minwid1=2;
   $minwid2=2*$minwid1;
   $string=$string.'/*   Stundenleiste   */
.mwt_uhrzei_wid { width:'.$st2.'%; min-width:'.$minwid2.'em; font-family:monospace; text-align:center;
    color:'.$kalcol[2].'; }
.mwt_lineal_wid { width:'.$st1.'%; min-width:'.$minwid1.'em; }
.mwt_lineal     { line-height:0.25em; border-left:solid 1px '.$kalcol[2].'; }
.mwt_lineal_re  { line-height:0.25em; border-right:solid 1px '.$kalcol[2].'; }
@media screen and (max-width:'.$addon::CSS_MOBILE.'em) {
    .mwt_uhrzei_wid { width:auto; min-width:0.2em; font-size:1px; }
    .mwt_lineal_wid { width:auto; min-width:0.1em; }
    .mwt_lineal     { line-height:0; border:none; }
    .mwt_lineal_re  { line-height:0; border:none; }
    }
';
   #
   # --- Terminfeld
   $string=$string.'/*   Terminfeld   */
.mwt_leertermin { margin-top:0.1em; margin-bottom:0.1em; padding:0 !important;
    '.$form_transparent.' }
.mwt_termin     { margin-top:0.1em; margin-bottom:0.1em; padding:0 !important;
    '.$form_col[3].'
    overflow-x:hidden; white-space:nowrap; }
@media screen and (max-width:'.$addon::CSS_MOBILE.'em) {
    .mwt_termin { white-space:normal; '.$form_transparent.' }
    }
';
   #
   # --- Terminblatt
   $string=$string.'
/*   Terminblatt   */
.termblatt_ter { padding-right:1em; width:100%; font-weight:bold; }
.termblatt_th  { padding:0.25em; text-align:left; white-space:nowrap; }
.termblatt_td  { padding:0.25em; width:100%; }
.termblatt_box { '.$form_col[5].' }
';
   #
   # --- Terminuebersicht
   $string=$string.'    
/*   Terminuebersicht   */
.tview_table   { '.$form_tview.' }
.tview_width  { width:9em; white-space:nowrap;}
.tview_bgcol  { width:97%; background-color:'.$kalcol[5].'; }
.tview_datum  { padding:0.5em; text-align:center; color:'.$kalcol[1].'; }
.tview_termin { padding:0.25em; background-color:'.$kalcol[5].'; '.$bradius.'; }
.tview_monat  { '.$form_monat.' }
.tview_heute  { '.$form_col[7].' }
.tview_pad    { padding-left:10px; }
.tview_men    { position:sticky; position: -webkit-sticky /* Safari-Browser */; top:1em; }
.tview_spmen  { top:4em !important; }
';
   #
   # --- Funktionsleiste
   $string=$string.'
/*   Funktionsleiste   */
.funktl_table { '.$form_col[3].' }
.funktl_td    { width:20%; text-align:center; }
';
   #
   # --- Terminliste
   $string=$string.'
/*   Terminliste   */
.termlist_pfeil { margin-bottom:-40px; font-size:40px; }
.termlist_th    { padding:0 0.5em 0 0; text-align:right; white-space:nowrap; }
.termlist_td    { padding:0 0.5em 0 0.5em; }
.termlist_ort::before        { content:" Ort: "; }
.termlist_ausrichter::before { content:" Ausrichter: "; }
@media screen and (max-width:'.$addon::CSS_MOBILE.'em) {
    .termlist_th { float:left; }
    .termlist_td { float:left; margin-left:0.5em; }
    }';
   #
   return $string;
   }
public static function kal_write_css() {
   #   Schreiben der Stylesheet-Datei kal_termine.css in den assets-Ordner.
   #
   $addon=self::this_addon;
   #
   # --- Erzeugen des Stylesheet-Textes
   $buffer=self::kal_define_css();
   #
   # --- ggf. Erzeugen des Ordners
   $dir=cms_interface::path_assets();
   if(!file_exists($dir)) mkdir($dir);
   #
   # --- Schreiben der Datei
   $file=$dir.$addon.'.css';
   $handle=fopen($file,'w');
   fwrite($handle,$buffer);
   fclose($handle);
   #
   # --- noetigenfalls Ergaenzen der Styles fuer die Terminliste
   $key=$addon::TERMLIST;
   $termlist=cms_interface::config_get($key);
   if(empty($termlist)) return;   // keine Terminliste-Konfigurationsparameter
   $arr=explode(',',$termlist);
   $param=array();
   for($i=0;$i<count($arr);$i=$i+1) $param[$addon::PALETTE_KEYS[$i]]=$arr[$i];
   self::kal_add_termlist_css($param);
   }
#
#----------------------------------------- Erzeugen der zweiten Stylesheet-Datei
public static function kal_termlist_colors($nz) {
   #   Rueckgabe von 30 + $nz*6 unterschiedlichen Farben zur moeglichen Markierung
   #   der Terminkategorien in einer Terminliste. Die Farben sind als nummeriertes
   #   Arrays von RGB-Werten 'rgb(red,green,blue)' gegeben (Nummerierung ab 1).
   #   $nz               = 1 oder 2 oder 3
   #
   if($nz>3) return array();
   #
   $part=array(1=>0.8, 2=>0.6, 3=>0.45);
   #
   # --- 6 Regenbogenfarben
   $rbcols=array(
      1=>'rgb(255,0,0)',   2=>'rgb(255,255,0)', 3=>'rgb(0,255,0)',
      4=>'rgb(0,255,255)', 5=>'rgb(0,0,255)',   6=>'rgb(255,0,255)');
   $nrb=count($rbcols);
   #
   # --- feste Zwischenfarben
   $dark=55;
   $midd=127;
   $brig=200;
   $brik=235;
   #
   # --- 6 Regenbogenfarben (gelb und tuerkis abgedunkelt) als 1. 6-er-Block
   $newcols=array();
   for($i=1;$i<=$nrb;$i=$i+1):
      $col=$rbcols[$i];
      $arr=explode(',',$col);
      $red=intval(trim(substr($arr[0],4)));
      $gre=intval(trim($arr[1]));
      $blu=intval(trim($arr[2]));
      #     gelb und tuerkis abdunkeln
      if($red>250 and $gre>250):
        $red=$brik;
        $gre=$brik;
        endif;
      if($gre>250 and $blu>250):
        $gre=$brik;
        $blu=$brik;
        endif;
      $newcols[$i]='rgb('.$red.','.$gre.','.$blu.')';     
      endfor;
   #
   # --- hellere Varianten hinzufuegen als 2. 6-er-Block
   $m=count($newcols);
   for($i=1;$i<=$nrb;$i=$i+1):
      $col=$rbcols[$i];
      $arr=explode(',',$col);
      $red=intval(trim(substr($arr[0],4)));
      $gre=intval(trim($arr[1]));
      $blu=intval(trim($arr[2]));
      if($red<=10 and $gre<=10):
        $red=$midd;
        $gre=$midd;
        endif;
      if($red<=10 and $blu<=10):
        $red=$midd;
        $blu=$midd;
        endif;
      if($gre<=10 and $blu<=10):
        $gre=$midd;
        $blu=$midd;
        endif;
      if($red<=10 and $gre>250 and $blu>250) $red=$midd;
      if($gre<=10 and $red>250 and $blu>250) $gre=$midd;
      if($blu<=10 and $red>250 and $gre>250) $blu=$midd;
      #     gelb und tuerkis abdunkeln
      if($red>250 and $gre>250):
        $red=$brik;
        $gre=$brik;
        endif;
      if($gre>250 and $blu>250):
        $gre=$brik;
        $blu=$brik;
        endif;
      $m=$m+1;
      $newcols[$m]='rgb('.$red.','.$gre.','.$blu.')';
      endfor;
   #
   # --- etwas dunklere Varianten (Teil 1) als 3. 6-er-Block
   $m=count($newcols);
   for($i=1;$i<=$nrb;$i=$i+1):
      $col=$rbcols[$i];
      $arr=explode(',',$col);
      $red=intval(trim(substr($arr[0],4)));
      $gre=intval(trim($arr[1]));
      $blu=intval(trim($arr[2]));
      if($red<=10 and $gre<=10):
        $red=$dark;
        $gre=$midd;
        $blu=$brig;
        endif;
      if($red<=10 and $blu<=10):
        $red=$midd;
        $gre=$brig;
        $blu=$dark;
        endif;
      if($gre<=10 and $blu<=10):
        $red=$brig;
        $gre=$dark;
        $blu=$midd;
        endif;
      if($red<=10 and $gre>250 and $blu>250) $red=$midd;
      if($gre<=10 and $red>250 and $blu>250) $gre=$midd;
      if($blu<=10 and $red>250 and $gre>250) $blu=$midd;
      if($red>250) $red=$brig;
      if($gre>250) $gre=$brig;
      if($blu>250) $blu=$brig;
      $m=$m+1;
      $newcols[$m]='rgb('.$red.','.$gre.','.$blu.')';
      endfor;
   #
   # --- etwas dunklere Varianten (Teil 2) als 4. 6-er-Block
   $m=count($newcols);
   for($i=1;$i<=$nrb;$i=$i+1):
      $col=$rbcols[$i];
      $arr=explode(',',$col);
      $red=intval(trim(substr($arr[0],4)));
      $gre=intval(trim($arr[1]));
      $blu=intval(trim($arr[2]));
      if($red<=10 and $gre<=10):
        $red=$dark;
        $gre=$dark;
        $blu=$brig;
        endif;
      if($red<=10 and $blu<=10):
        $red=$dark;
        $gre=$brig;
        $blu=$dark;
        endif;
      if($gre<=10 and $blu<=10):
        $red=$brig;
        $gre=$dark;
        $blu=$dark;
        endif;
      if($red<=10 and $gre>250 and $blu>250) $red=$dark;
      if($gre<=10 and $red>250 and $blu>250) $gre=$dark;
      if($blu<=10 and $red>250 and $gre>250) $blu=$dark;
      if($red>250) $red=$brig;
      if($gre>250) $gre=$brig;
      if($blu>250) $blu=$brig;
      $m=$m+1;
      $newcols[$m]='rgb('.$red.','.$gre.','.$blu.')';
      endfor;
   #
   # --- abgestufte dunkle Varianten (1, 2, ..., $nz) als 5./6./7./ ... 6-er-Block
   $m=count($newcols);
   for($k=1;$k<=$nz;$k=$k+1):
      for($i=1;$i<=$nrb;$i=$i+1):
         $col=$rbcols[$i];
         $arr=explode(',',$col);
         $red=intval(trim(substr($arr[0],4)));
         $gre=intval(trim($arr[1]));
         $blu=intval(trim($arr[2]));
         $red1=intval($red*$part[$k]);
         $gre1=intval($gre*$part[$k]);
         $blu1=intval($blu*$part[$k]);
         $m=$m+1;
         $newcols[$m]='rgb('.$red1.','.$gre1.','.$blu1.')';
         endfor;
      endfor;
   #
   # --- Grautoene als letzter 6-er-Block
   $grays=array(
      1=>'rgb(70,70,70)',    2=>'rgb(95,95,95)',    3=>'rgb(120,120,120)',
      4=>'rgb(145,145,145)', 5=>'rgb(170,170,170)', 6=>'rgb(195,195,195)');
   $m=count($newcols);
   for($i=1;$i<=$nrb;$i=$i+1):
      $m=$m+1;
      $newcols[$m]=$grays[$i];
      endfor;
   #
   return $newcols;
   }
public static function kal_read_termlist_colors() {
   #   Einlesen einer Benutzer-definierten Farbpalette zur farblichen Markierung
   #   der Terminkategorien in einer Terminliste. Die Paletten-Datei liegt im
   #   AddOn-Ordner /vendor (Dateiname $addon::PALETTE).
   #   Rueckgabe der Farben als nummeriertes Array (Nummerierung ab 1).
   #
   $addon=self::this_addon;
   $file=cms_interface::path_main().$addon::PALETTE_DIR.DIRECTORY_SEPARATOR.$addon::PALETTE_FILE;
   if(!file_exists($file)) return array();
   #
   $handle=fopen($file,'r');
   $m=0;
   $colors=array();
   while(($buffer=fgets($handle))!==false):
        $m=$m+1;
        $colors[$m]=trim($buffer,"\r\n");
        endwhile;
   fclose($handle);
   return $colors;
   }
public static function kal_get_termlist_colors() {
   #   Rueckgabe einer Farbpalette zur farblichen Markierung der Terminkategorien
   #   in einer Terminliste in Form eines nummerierten Arrays (Nummerierung
   #   ab 1). Stellt der Benutzer im AddOn-Ordner /vendor eine Datei namens
   #   $addon::PALETTE bereit, wird diese eingelesen und als Farbpalette
   #   verwendet. Andernfalls wird eine AddOn-interne Farbpalette definiert.
   #
   $addon=self::this_addon;
   $file=cms_interface::path_main().$addon::PALETTE_DIR.DIRECTORY_SEPARATOR.$addon::PALETTE_FILE;
   if(file_exists($file)):
     return self::kal_read_termlist_colors();
     else:
     return self::kal_termlist_colors($addon::PALETTE_SIZE);
     endif;
   }
public static function kal_html_termlist_colors($colors) {
   #   Rueckgabe des HTML-Codes zur Ausgabe einer Folge von Farben fuer eine
   #   moegliche farblichen Markierung der Terminkategorien in einer Terminliste.
   #   $colors           nummeriertes Array der Farben (Nummerierung ab 1), jede Farbe
   #                     ist in der Form 'rgb(red,green,blue)' gegeben
   #
   $addon=self::this_addon;
   $nn=count($colors);
   if($nn<=0) return '
<p>Es ist keine eigene Farbpalette vorgegeben.</p>';
   #
   # --- vorhandene Terminkategorien markieren
   $kats=$addon::kal_conf_terminkategorien();
   $nzkats=count($kats);
   $string='
<table>
    <tr><th colspan=2">
            Kateg.-Id &nbsp;</th>
        <th>Farbe</th>
        <th class="conf_indent">
            Farbcodierung</th>
        <th class="conf_indent">
            vorhandene Kategorie</th></tr>';
   for($i=1;$i<=$nn;$i=$i+1):
      $col=$colors[$i];
      $kat_id=$i;
      if($kat_id<=9) $kat_id='0'.$kat_id;
      $ii=$i-1;
      if($i>$nzkats):
        $nr=$i;
        $antw='';
        else:
        $nr='<b>'.$i.'</b>';
        $antw=$kats[$ii]['name'];
        endif;
      $string=$string.'
    <tr><td align="right">
            '.$nr.'</td>
        <td class="conf_indent">
            &nbsp;</td>
        <td style="background-color:'.$col.';" width="70"></td>
        <td class="conf_indent" style="color:'.$col.';">
            '.$col.'</td>
        <td class="conf_indent">
            '.$antw.'</td></tr>';
      if($i==$nzkats) $string=$string.'
    <tr><td colspan="5" height="3"></td></tr>';
      endfor;
   $string=$string.'
</table>';
   return $string;
   }
public static function kal_define_termlist_css($param) {
   #   Rueckgabe der Quelle fuer eine Ergaenzung der Stylesheet-Datei. Darin
   #   werden Styles fuer eine farbliche Markierung der Terminkategorien in
   #   einer Terminliste definiert, basierend auf einer gegebenen Farbpalette
   #   (Standardpalette oder Benutzer-definierte Palette). Benutzt werden ggf.
   #   nur die ersten Farben entsprechend der Anzahl der Terminkategorien.
   #   $param          assoziatives Array der Parameter fuer die Styles
   #       ['sides']   =0: kein Rand
   #                   =1: nur linker Rand
   #                   =2: alle Raender
   #       ['pix']     Randdicke in Anzahl Pixel (= 0, 1, 2, 3, ...)
   #
   $addon=self::this_addon;
   $kats=$addon::kal_conf_terminkategorien();
   $nzkats=count($kats);
   if($nzkats<=0) return;
   #
   $keys=$addon::PALETTE_KEYS;
   $intsid=$param[$keys[0]];
   $intpix=$param[$keys[1]];
   #
   # --- AddOn-Farben oder Benutzer-definierte Farben?
   $XX='XXXXX';
   $colors=self::kal_get_termlist_colors();
   $string='';
   #
   # --- Randdicke
   if($intpix<=0) $intpix=1;
   if($intpix>=$addon::MAX_PIX) $intpix=$addon::MAX_PIX;
   $px=$intpix.'px';
   #
   # --- Rand-Sides und -Farben
   if($intsid<=0) $stbord='border:none;';
   if($intsid==1) $stbord='border-left:solid '.$px.' '.$XX.';';
   if($intsid>=2) $stbord='border:solid '.$px.' '.$XX.';';
   for($i=1;$i<=$nzkats;$i=$i+1):
      $kat_id=$i;
      if($kat_id<=9) $kat_id='0'.$kat_id;
      $stb=$stbord;
      $stb=str_replace($XX,$colors[$i],$stb);
      $string=$string.'
.termbord_'.$kat_id.' { '.$stb.' }';
      endfor;
   return $string;
   }
public static function kal_add_termlist_css($param) {
   #   Ergaenzen der Stylesheet-Datei um die Styles zur zusaetzlichen Gestaltung
   #   der Terminliste. Dafuer wird aus systematischen Gruenden zunaechst die
   #   Stylesheet-Datei neu geschrieben.
   #   $param          assoziatives Array der Parameter fuer die Styles,
   #                   vergl. kal_define_termlist_css(...)
   #
   $addon=self::this_addon;
   #
   # --- keine Ergaenzung falls kein Rand
   $keys=$addon::PALETTE_KEYS;
   if($param[$keys[0]]<=0) return;
   #
   # --- Ergaenzen der Stylesheet-Datei
   $buffer=self::kal_define_termlist_css($param);
   $file=cms_interface::path_assets().$addon.'.css';
   $handle=fopen($file,'a');
   fwrite($handle,$buffer);
   fclose($handle);
   }
public static function kal_config_termlist() {
   #   Eingabeformular fuer die Daten zur Ergaenzung der Stylesheet-Datei.
   #   Darin werden Styles fuer eine farbliche Markierung der Terminkategorien
   #   in einer Terminliste definiert, basierend auf einer gegebenen Farbpalette
   #   (Standardpalette oder Benutzer-definierte Palette).
   #
   $addon=self::this_addon;
   #
   # --- Farbpaletten
   $defcols =self::kal_termlist_colors($addon::PALETTE_SIZE);
   $ndefcols=count($defcols);
   $usrcols =self::kal_read_termlist_colors();
   $keys    =$addon::PALETTE_KEYS;
   #
   # --- neu eingegebene Daten einlesen und speichern
   $sent=$addon::kal_post_in($addon::TERMLIST);
   if(!empty($sent)):
     $intsid=intval($addon::kal_post_in($keys[0],'int'));
     $intpix=intval($addon::kal_post_in($keys[1],'int'));
     #     logische Korrekturen
     if($intsid<=0) $intpix=0;                 // ohne Rand keine Rahmendicke
     if($intsid>0 and $intpix<=0) $intpix=1;   // Rahmen nicht ohne Rahmendicke
     #
     # --- Terminliste-Konfigurationsdaten speichern
     $string=$intsid.','.$intpix;
     cms_interface::config_set($addon::TERMLIST,$string);
     #
     # --- Stylesheet mit Ergaenzung zur Terminliste neu schreiben
     self::kal_write_css();
     endif;
   #
   # --- konfigurierte Daten einlesen
   $confdat=cms_interface::config_get($addon::TERMLIST);
   $keys=$addon::PALETTE_KEYS;
   if(empty($confdat)):
     $intsid=0;
     $intpix=0;
     else:
     $arr=explode(',',$confdat);
     $intsid=$arr[0];
     $intpix=$arr[1];
     endif;
   $param=array($keys[0]=>$intsid,$keys[1]=>$intpix);
   #
   # --- Einfuehrung
   $palette='<b>eigene Farbpalette</b>';
   $zusatz='</div>';
   $colfil=cms_interface::path_main().$addon::PALETTE_DIR.DIRECTORY_SEPARATOR.$addon::PALETTE_FILE;
   if(file_exists($colfil)):
     $palette='
<a onClick="document.getElementById(\'default\').style.display=\'none\';
            document.getElementById(\'user\').style.display=\'block\';"
   href="#user">'.$palette.'</a>';
     $zusatz=' Eine solche Datei wird hier benutzt.</div>';
     endif;
   $string='
<h4 align="center">Konfiguration der Terminliste-Darstellung</h4>
<div>Die HTML-Darstellung der Terminliste enthält einige Stilklassen, die in der
Stylesheet-Datei normalerweise nicht enthalten sind. Hier können diese Stilelmente
definiert und in der Stylesheet-Datei ergaenzt werden. Mit ihnen können <b>die
Termine unterschiedlicher Kategorien farblich voneinander abgehoben</b> werden.
Das AddOn stellt eine entsprechende
<a onClick="document.getElementById(\'default\').style.display=\'block\';
            document.getElementById(\'user\').style.display=\'none\';"
   href="#default"><b>Default-Farbpalette</b></a> bereit ('.$ndefcols.' Farben).<br>
Stattdessen kann auch eine '.$palette.' vorgegeben werden. Diese wird in Form einer
Datei namens <code>'.$addon::PALETTE_FILE.'</code> im AddOn-Ordner
<code>'.$addon::PALETTE_DIR.'</code> abgelegt und enthält die Farben zeilenweise.';
   $string=$string.$zusatz.'
<form method="post">';
   #
   # --- Rahmen
   $string=$string.'
<div><br>Die Beschreibung jedes Termins (rechte Spalte der Tabelle) kann mit
einem farbigen linken Rand oder einem farbigen Rahmen versehen werden. Die Farbe
ergibt sich aus der Terminkategorie. Die Dicke des Rahmens (Anzahl Pixel) kann
ausgewählt werden.
<table class="kal_table">
    <tr valign="top">
        <td class="kal_indent">
            <u>Rahmentyp:</u></td>';
   #     Rahmentyp
   $border=array(
      'ohne Rahmen',
      'nur linker Rand',
      'vollständiger Rahmen');
   $string=$string.'
        <td class="kal_indent">';
   for($i=0;$i<count($border);$i=$i+1):
      $chk='';
      if($i==$intsid) $chk=' checked';
      $string=$string.'
            <div><input type="radio" name="'.$keys[0].'" value="'.$i.'"'.$chk.'>&nbsp;'.$border[$i].'</div>';
      endfor;
   #     Rahmendicke
   $string=$string.'</td>
        <td class="kal_indent">
            <u>Rahmendicke:</u></td>
        <td class="kal_indent">
            <select name="'.$keys[1].'">';
   for($i=0;$i<=$addon::MAX_PIX;$i=$i+1):
      $sel='';
      if($i==$intpix) $sel=' selected';
      $string=$string.'
                 <option align="right" value="'.$i.'"'.$sel.'>'.$i.' px &nbsp; </option>';
      endfor;
   $string=$string.'
            </select></td></tr>
</table>
</div>';
   #
   # --- Absende-Button
   $string=$string.'
<br>
<button type="submit" name="termlist" value="sent" class="kal_btn_save"
        title="Daten übernehmen und Stylesheet ergänzen"
        onClick="window.location.reload();">
&nbsp;<b>Daten übernehmen</b>&nbsp;</button>
</form>';
   #
   # --- Link-Pfeil 'nach oben'
   $pfeil='
<div class="termlist_pfeil" title="Farbpaletten ausblenden, an den Anfang des Dokuments gehen">
<a onClick="document.getElementById(\'default\').style.display=\'none\';
            document.getElementById(\'user\').style.display=\'none\';"
   href="#">'.$addon::AWE_HIDE.'</a>
</div>';
   #
   # --- Ausgabe der Spieldaten-Terminliste mit den neuen Styles
   $styles=self::kal_define_termlist_css($param);
   $string=$string.'
<style>
'.$styles.'
</style>';
   $heute=kal_termine_kalender::kal_heute();
   $von  =kal_termine_kalender::kal_montag_vor($heute);
   $bis  =kal_termine_kalender::kal_datum_vor_nach($von,6);
   $kats =$addon::kal_get_spielkategorien();
   $katids=array();
   for($i=0;$i<count($kats);$i=$i+1) $katids[$i+1]=$kats[$i]['id'];
   $string=$string.'
<br>'.$pfeil.'
<h4 align="center">Exemplarische Terminliste mit Spieldaten (gemäß obigem Stylesheet)</h4>
'.kal_termine_tabelle::kal_terminliste($von,$bis,$katids);
   #
   # --- Ausgabe der eigenen Farbpalette
   if(file_exists($colfil)):
     $userhtml=self::kal_html_termlist_colors($usrcols);
     $string=$string.'
<div id="user" class="kal_hide">
<br><a name="user"></a>'.$pfeil.'
<h4 align="center">Eigene Farbpalette</h4>'.$userhtml.'
</div>';
     endif;
   #
   # --- Ausgabe der Default-Farbpalette
   $defhtml=self::kal_html_termlist_colors($defcols);
   $string=$string.'
<div id="default" class="kal_hide">
<br><a name="default"></a>'.$pfeil.'
<h4 align="center">Default-Farbpalette</h4>'.$defhtml.'
</div>';
   #
   $string=$string.$pfeil.'
<br><br><br>';
   echo $string;
   }
#
#----------------------------------------- Erzeugen der Konfigurationsdaten
public static function kal_config_form($readsett,$entf_kat,$anzdelterm) {
   #   Anzeige des Formulars zur Eingabe der Konfigurationsdaten.
   #   $readsett         Array der Formulardaten im Format der Default-Konfiguration
   #   $entf_kat         assoziatives Array mit den Schluesseln 'id' und 'name'
   #                     ['id']   kommaseparierte Liste von Terminkategorie-Ids,
   #                              die zuvor entfernnt wurden
   #                     ['name'] Name der Terminkategorie bzw.
   #                              'nn entfernten Kategorien' (nn = Anzahl Kat.)
   #   $anzdelterm       Anzahl geloeschter Termine (nach Reduzierung der Anzahl
   #                     der Terminkategorien)
   #
   $addon=self::this_addon;
   #
   # --- Ueberschrift Farbe
   $string='
<form id="config" method="post">
<table class="kal_table">
    <tr><td colspan="4">
            <b>Farben in den Kalendermenüs (RGB):</b></td></tr>
    <tr><td class="conf_indent">
            (bis auf die letzte) abgeleitet von der definierten Grundfarbe</td>
        <td class="conf_number">
            <b>R </b></td>
        <td class="conf_number">
            <b>G </b></td>
        <td class="conf_number">
            <b>B </b></td></tr>';
   #
   # --- Formular Grundfarbe
   $farben=$addon::kal_farben();
   $colrgb=array();
   $coltxt=array();
   for($i=1;$i<=count($farben);$i=$i+1):
      $colrgb[$i]=$farben[$i]['rgb'];
      $coltxt[$i]=$farben[$i]['name'];
      endfor;
   $col=$addon::kal_split_color($readsett[$addon::DEFAULT_COL_KEY]);
   $string=$string.'
    <tr><td class="conf_indent">
            <div class="conf_indent '.$addon::CSS_COLS.'1">'.$coltxt[1].'</div></td>
        <td class="conf_indent">
            <input class="conf_inpint" type="text" name="red"   value="'.$col['red'].'"></td>
        <td class="conf_undent">
            <input class="conf_inpint" type="text" name="green" value="'.$col['green'].'"></td>
        <td class="conf_undent">
            <input class="conf_inpint" type="text" name="blue"  value="'.$col['blue'].'"></td></tr>';
   #
   # --- restliche Farben
   for($i=2;$i<=count($farben);$i=$i+1):
      $col=$addon::kal_split_color($colrgb[$i]);
      $string=$string.'
    <tr><td class="conf_indent">
            <div class="conf_indent '.$addon::CSS_COLS.$i.'">'.$coltxt[$i].'</div></td>
        <td class="conf_number">
            '.$col['red'].'</td>
        <td class="conf_number">
            '.$col['green'].'</td>
        <td class="conf_number">
            '.$col['blue'].'</td></tr>';
      endfor;
   $string=$string.'
    <tr><td class="conf_indent" colspan="4">
            <b>(*)</b> &nbsp; <i>Damit die Farbunterschiede nicht verschwimmen,
            sollten die RGB-Parameter <b>&le; 150</b> sein.</i></td></tr>';
   #
   # --- Formular Stundenleiste
   $string=$string.'
    <tr><td colspan="4">
            <b>Darstellung des Uhrzeit-Bereichs bei Tagesterminen:</b></td></tr>
    <tr><td class="conf_indent">
            Start-Uhrzeit &nbsp; <i>(ganze Zahl)</i>:</td>
        <td class="conf_indent">
            <input class="conf_inpint" type="text" name="'.$addon::STAUHRZ_KEY.'" value="'.$readsett[$addon::STAUHRZ_KEY].'"></td>
        <td colspan="2">
            <tt>&nbsp;:00 Uhr</tt></td></tr>
    <tr><td class="conf_indent">
            End-Uhrzeit &nbsp; <i>(ganze Zahl)</i>:</td>
        <td class="conf_indent">
            <input class="conf_inpint" type="text" name="'.$addon::ENDUHRZ_KEY.'" value="'.$readsett[$addon::ENDUHRZ_KEY].'"></td>
        <td colspan="2">
            <tt>&nbsp;:00 Uhr</tt></td></tr>';
   #
   # --- Formular, Terminkategorien
   $string=$string.'
    <tr><td colspan="4">
            <b>Terminkategorien (Id):</b></td></tr>';
   if(cms_interface::backend()):
     $ro='';
     else:
     $ro=' readonly';
     endif;
   $anz=0;
   $anzkat=count($readsett)-3;   // abzueglich Grundfarbe, Startzeit, Endzeit (3)
   for($i=1;$i<=$anzkat;$i=$i+1):
      $key=$addon::KAT_KEYS.strval($i);
      $set=$readsett[$key];
      if(!empty($set)):
        $string=$string.'
    <tr><td class="conf_indent">
            '.$i.'</td>
        <td class="conf_indent" colspan="3">
            <input class="conf_inptxt" type="text" name="'.$key.'" value="'.$set.'"'.$ro.'></td></tr>';
        $anz=$anz+1;
        endif;
      endfor;
   #
   # --- im CMS sind mehrere Terminkategorien moeglich
   if(cms_interface::backend()):
     #     Formular, leeres Feld fuer eine neue Terminkategorie
     $i=$anz+1;
     $key=$addon::KAT_KEYS.strval($i);
     $string=$string.'
    <tr><td class="conf_indent">
            '.$i.' &nbsp; <i>(hier kann eine neue Kategorie angefügt werden)</i></td>
        <td class="conf_indent" colspan="3">
            <input class="conf_inptxt" type="text" name="'.$key.'" value=""></td></tr>
    <tr valign="top">
        <td class="conf_indent">
            Zum <b>Entfernen</b> der <b>jeweils letzten</b> Terminkategorie ...</td>
        <td class="conf_indent" colspan="3" align="right">
            ... das zugehörige <b>Feld leeren</b>!</td></tr>
        <td class="conf_indent" colspan="4" align="right">
            <i>Mit dem Entfernen einer Kategorie sollten auch die zugehörigen Termine entfernt werden!</i></td></tr>';
     #     Entfernen von Terminen zu entfernten Kategorien
     if($anzdelterm>0):
       $eid =$entf_kat['id'];
       $ekat=$entf_kat['name'];
       $arr=explode(',',$eid);
       if(count($arr)>1):
         $text='Die <code>'.$anzdelterm.'</code> zu den ';
         else:
         $text='Die <code>'.$anzdelterm.'</code> zur Kategorie ';
         endif;
       $string=$string.'
    <tr valign="top">
        <td colspan="4" align="right">
            <b>'.$text.'<code>'.$ekat.'</code>
            gehörigen &nbsp;
            <button class="kal_btn_save" type="submit" name="clean" value="'.$eid.'">
            &nbsp;Termine entfernen&nbsp;</button></b></td></tr>';
       endif;
     endif;
   #
   # --- Formular, Abschluss
   $rebut='auf Defaultwerte zurücksetzen';
   $retit='Parameter '.$rebut.' (inkl. Terminlisten-Konfiguration), Parameter und css-Stylesheet speichern';
   $spbut='speichern';
   $sptit='Parameter und css-Stylesheet '.$spbut;
   $string=$string.'
    <tr><td>
            <br>
            <button class="kal_btn_save" type="submit" name="save"  value="save"
                    title="'.$sptit.'">&nbsp;<b>'.$spbut.'</b>&nbsp;</button></td>
        <td colspan="3"><br>
            <button class="kal_btn"      type="submit" name="reset" value="reset"
                    title="'.$retit.'">&nbsp;'.$rebut.'&nbsp;</button></td></tr>
</table>
</form>';
   echo $string;
   }
public static function kal_lost_termine() {
   #   Rueckgabe der Termine, die zu einer geloeschten Terminkategorie gehoeren.
   #
   $addon=self::this_addon;
   $keypid=$addon::TAB_KEY[0];   // pid
   $keykat=$addon::TAB_KEY[1];   // kat_id
   #
   # --- konfigurierte Terminkategorien
   $kats=$addon::kal_conf_terminkategorien();
   $anz=count($kats);
   $idmax=$kats[$anz-1]['id'];
   #
   # --- Termine zu geloeschten Terminkategorien finden und loeschen
   $where=$keykat.'>'.$idmax;
   $order=$keykat.' ASC';
   $term=cms_interface::select_termin($where,$order);
   $lost_term=array();
   for($i=0;$i<count($term);$i=$i+1):
      $pid=$term[$i][$keypid];
      $lost_term[$i+1]=$term[$i];
      $lost_term[$i+1]=$term[$i];
      endfor;
   return $lost_term;
   }
public static function kal_new_config($settconf) {
   #   Setzen der gerade veraenderten Konfiguration (save / reset).
   #   $settconf         assoziatives Array der neuen Konfigurationsparameter
   #
   $addon=self::this_addon;
   #     Terminliste-Konfiguration uebernehmen
   $key=$addon::TERMLIST;
   $settconf[$key]=cms_interface::config_get($key);
   #     Konfiguration neu setzen
   $addon::kal_set_config($settconf);
   #     Bestand an Benutzerrollen zu den Terminkategorien anpassen
   cms_interface::set_roles();
   #     Stylesheet ueberschreiben
   self::kal_write_css();
   }
public static function kal_config() {
   #   Einlesen und Setzen der Konfigurationsdaten.
   #
   $addon=self::this_addon;
   $keypid=$addon::TAB_KEY[0];   // pid
   $keykat=$addon::TAB_KEY[1];   // kat_id
   $kats=$addon::kal_conf_terminkategorien();
   #
   # --- speichern oder zuruecksetzen?
   $save =$addon::kal_post_in('save');
   $reset=$addon::kal_post_in('reset');
   $clean=$addon::kal_post_in('clean');
   $confsett=$addon::kal_get_config();
   #
   # --- Anzahl Kategorien
   $keys=array_keys($confsett);
   $anzkat=count($keys)-3;   // abzueglich Grundfarbe, Startzeit, Endzeit (3)
   #
   # --- Auslesen der Daten
   $readsett=array();
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if($key==$addon::DEFAULT_COL_KEY):
        #
        # --- Grundfarbe
        $confcol=$addon::kal_split_color($confsett[$key]);
        if(empty($save)):
          #     aus der Konfiguration
          $red=$confcol['red'];
          $gre=$confcol['green'];
          $blu=$confcol['blue'];
          else:
          #     eingelesen
          $red=$addon::kal_post_in('red',  'int');
          $gre=$addon::kal_post_in('green','int');
          $blu=$addon::kal_post_in('blue', 'int');
          endif;
        $readsett[$key]='rgb('.$red.','.$gre.','.$blu.')';
        else:
        #
        # --- Sonstige Parameter
        $conf=$confsett[$key];
        if(empty($save)):
          #     aus der Konfiguration
          $sett=$conf;
          else:
          #     eingelesen          
          if(substr($key,0,3)==$addon::KAT_KEYS):
            $sett=$addon::kal_post_in($key);
            else:
            $sett=$addon::kal_post_in($key,'int');
            endif;
          #     ggf. Kategorie leer setzen (um sie spaeter herauszunehmen)
          if(substr($key,0,3)==$addon::KAT_KEYS and substr($key,3)>1 and empty($sett)
             and !empty($conf))
            $sett='';
          #     Ueberpruefen der Start-Uhrzeit
          if($key==$addon::STAUHRZ_KEY):
            if(intval($sett)<$sett):
             $warning='Die Uhrzeit muss <code>ganzzahlig</code> sein! Wert bleibt erhalten.';
              echo '<div class="kal_warning">'.$warning.'</div>';
              $sett=$conf;
              else:
              if($sett<0 or $sett>23 or $sett>=$confsett[$addon::ENDUHRZ_KEY]):
                $warning='Die Start-Uhrzeit <code>'.$sett.'</code> muss <code>zwischen '.
                         '0 und 23 Uhr</code> und '.
                         '<code>vor der End-Uhrzeit</code> liegen! Wert bleibt erhalten.';
                echo '<div class="kal_warning">'.$warning.'</div>';
                $sett=$conf;
                endif;
              endif;
            endif;
          #     Ueberpruefen der End-Uhrzeit
          if($key==$addon::ENDUHRZ_KEY):
            if(intval($sett)<$sett):
              $warning='Die Uhrzeit muss <code>ganzzahlig</code> sein! Wert bleibt erhalten.';
              echo '<div class="kal_warning">'.$warning.'</div>';
              $sett=$conf;
              else:
              if($sett<1 or $sett>24 or $sett<=$confsett[$addon::STAUHRZ_KEY]):
                $warning='Die End-Uhrzeit muss <code>zwischen 1'.
                         ' und 24 Uhr</code> und <code>nach der '.
                         'Start-Uhrzeit</code> liegen! Wert bleibt erhalten.';
                echo '<div class="kal_warning">'.$warning.'</div>';
                $sett=$conf;
                endif;
              endif;
            endif;
          endif;
        $readsett[$key]=$sett;
        endif;
      endfor;
   #
   # --- (bisher leere) neue Kategorie einlesen
   $neu=intval($anzkat+1);
   $key=$addon::KAT_KEYS.strval($neu);
   $post=$addon::kal_post_in($key);
   if(!empty($post)) $readsett[$key]=$post;
   #
   # --- verlorene Termine loeschen
   if(!empty($clean)):
     $arr=explode(',',$clean);
     for($i=0;$i<count($arr);$i=$i+1):
        $katid=$arr[$i];
        $where=$keykat.'='.$katid;
        $ter=cms_interface::select_termin($where,'');
        for($k=0;$k<count($ter);$k=$k+1)
           kal_termine_tabelle::kal_delete_termin($ter[$k][$keypid]);
        endfor;
     endif;
   $anzdelterm=0;
   #
   # --- Default-Konfiguration speichern
   if(!empty($reset)):
     $readsett=$addon::kal_default_config();
     $addon::kal_set_config($readsett);
     #     zu entfernende Kategorien (Id>1) herausnehmen
     $lost_term=self::kal_lost_termine();
     $kidmax=0;
     $entf_ids='';
     for($i=1;$i<=count($lost_term);$i=$i+1):
        $id=$lost_term[$i][$keykat];
        if($id>$kidmax) $entf_ids=$entf_ids.','.$id;
        $kidmax=max($kidmax,$id);
        endfor;
     if(!empty($entf_ids)) $entf_ids=substr($entf_ids,1);
     if($kidmax>0):
       $m=$kidmax-1;
       else:
       $m=0;
       endif;
     $entf_kat=array('id'=>$entf_ids,'name'=>$m.' entfernten Kategorien');
     $anzdelterm=count($lost_term);
     self::kal_new_config($readsett);
     endif;
   #
   # --- neue Konfiguration speichern
   if(!empty($save)):
     $keys=array_keys($readsett);
     $settconf=array();
     #     ggf. leere (entfernte) Kategorien herausnehmen
     for($i=0;$i<count($keys);$i=$i+1):
        $key=$keys[$i];
        if(substr($key,0,3)==$addon::KAT_KEYS and empty($readsett[$key])):
          $entf_id=intval(substr($key,3,1));
          $entf_kat=$kats[$entf_id-1];
          #     Anzahl zugehoeriger Termine (ggf. zu loeschen)
          $where=$keykat.'='.$entf_id;
          $ter=cms_interface::select_termin($where,'');
          $anzdelterm=count($ter);
          continue;
          endif;
        $settconf[$key]=$readsett[$key];
        endfor;
     self::kal_new_config($settconf);
     endif;
   #
   # --- Eingabeformular anzeigen
   self::kal_config_form($readsett,$entf_kat,$anzdelterm);
   }
}
?>