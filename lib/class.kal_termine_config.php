<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2024
*/
#
class kal_termine_config {
#
#----------------------------------------- Inhaltsuebersicht
#   Erzeugen der Stylesheet-Datei
#      kal_split_color($color)
#      kal_farben()
#      kal_hatch_gen($dif,$bgcolor)
#      kal_define_css()
#      kal_write_css()
#      kal_post_in($key,$type)
#   Erzeugen der zweiten Stylesheet-Datei
#      kal_termlist_colors($nz)
#      kal_read_termlist_colors()
#      kal_get_termlist_colors()
#      kal_html_termlist_colors($colors)
#      kal_define_termlist_css($param)
#      kal_add_termlist_css($param)
#      kal_config_termlist()
#   Einlesen der Konfigurationsdaten
#      kal_config_form($readsett)
#      kal_clean_termine()
#      kal_config()
#
#----------------------------------------- Konstanten
const this_addon=kal_termine::this_addon;   // Name des AddOns
#
#----------------------------------------- Erzeugen der Stylesheet-Datei
public static function kal_split_color($color) {
   #   Rueckgabe der RGB-Komponenten eines RGB-Farbstrings in Form eines
   #   assoziativen Arrays mit den Keys 'red', 'green', 'blue'.
   #   $color            RGB-String der Farbe in der Form 'rgb(r,g,b)'
   #
   $arr=explode(',',$color);
   if(count($arr)>1):
     $red  =trim(substr($arr[0],4));
     $green=trim($arr[1]);
     $blue =trim(substr($arr[2],0,strlen($arr[2])-1));
     else:
     $red  ='';
     $green='';
     $blue ='';
     endif;
   return array('red'=>$red, 'green'=>$green, 'blue'=>$blue);
   }
public static function kal_farben() {
   #   Rueckgabe der 9 RGB-Farben fuer die Kalendermenues als nummeriertes Array,
   #   wobei jedes Array-Element ein assoziatives Array aus RGB-Wert und
   #   Farbcharakterisierung ist:
   #      [1]             die konfigurierte Grundfarbe
   #      [2], ..., [6]   systematisch aufgehellte Variationen der Grundfarbe
   #      [7],[8]         komplementaere Farbtoene fuer den aktuellen Tag
   #      [9]             =$addon::RGB_GREY: Schrift-/Rahmenfarbe fuer Tage ausserhalb
   #                      des aktuellen Monats
   #   Die Grundfarbe ist als dunkle Schriftfarbe zu konfigurieren (RGB-Werte
   #   nicht groesser als $addon::RGB_MAX).
   #   Beispiele fuer die Farbgebung durch die Wahl der Grundfarbe:
   #      roetlich:   rgb(R,g,b)   (R ist deutlich groesser als g und b)
   #      gruenlich:  rgb(r,G,b)   (G ist deutlich groesser als r und b)
   #      blaeulich:  rgb(r,g,B)   (B ist deutlich groesser als r und g)
   #      gelblich:   rgb(R,G,b)   (R und G sind deutlich groesser als b)
   #      violett:    rgb(R,g,B)   (R und B sind deutlich groesser als g)
   #      tuerkis:    rgb(r,G,B)   (G und B sind deutlich groesser als r)
   #      grau:       rgb(R,G,B)   (R = G = B)
   #   benutzte functions:
   #      self::kal_split_color($color)
   #
   $addon=self::this_addon;
   #
   # --- Grundfarbe
   $sett=rex_config::get($addon::this_addon,NULL);
   $keys=array_keys($sett);
   $base_col=$addon::DEFAULT_COL;
   for($i=0;$i<count($keys);$i=$i+1)
      if(substr($keys[$i],0,3)=='col'):
        $base_col=$sett[$keys[$i]];
        break;
        endif;
   #
   # --- Charakterisierung/Einsatz der Farben
   $cnam=array(
      1=>'dunkle Schrift-/Rahmenfarbe, Grundfarbe (<b>R,G,B &le; '.$addon::RGB_MAX.'</b>)',
      2=>'helle Schriftfarbe, Stundenleiste',
      3=>'Hintergrundfarbe (Termine im Tages-/Wochen-/Monatsblatt)',
      4=>'Hintergrundfarbe (Sonn- und Feiertage, Such-Button)',
      5=>'Hintergrundfarbe (Suchformular), Schraffurfarbe',
      6=>'Hintergrundfarbe (Wochentage, Terminblatt, Suchformular)',
      7=>'Schrift-/Rahmenfarbe (heutiger Tag)',
      8=>'Hintergrundfarbe (heutiger Tag)',
      9=>'neutrale Schrift-/Rahmenfarbe (nicht abgeleitet)');
   $anz=6;   // Anzahl der Farbtoene in der Farbe der Grundfarbe
   #
   # --- RGB-Werte der Farben
   $dol=self::kal_split_color($base_col);
   $col=array();
   $col[1]=array('rgb'=>$base_col, 'name'=>$cnam[1]);
   for($i=2;$i<=$anz;$i=$i+1):
      $dif=$i*$addon::RGB_DIFF;
      $red=intval($dol['red']  +$dif);
      $gre=intval($dol['green']+$dif);
      $blu=intval($dol['blue'] +$dif);
      $rgb='rgb('.$red.','.$gre.','.$blu.')';
      $col[$i]=array('rgb'=>$rgb, 'name'=>$cnam[$i]);
      endfor;
   #
   # --- RGB-Werte der Farben fuer den heutigen Tag (Komplementaerfarbe zur Grundfarbe)
   $dif=$anz*$addon::RGB_DIFF;
   $red=intval(255-$dol['red']  -$dif);
   $gre=intval(255-$dol['green']-$dif);
   $blu=intval(255-$dol['blue'] -$dif);
   $rgb='rgb('.strval($red).','.strval($gre).','.strval($blu).')';
   $col[$anz+1]=array('rgb'=>$rgb, 'name'=>$cnam[7]);   // Rahmen
   $red=intval(255-$dol['red']);
   $gre=intval(255-$dol['green']);
   $blu=intval(255-$dol['blue']);
   $rgb='rgb('.strval($red).','.strval($gre).','.strval($blu).')';
   $col[$anz+2]=array('rgb'=>$rgb, 'name'=>$cnam[8]);   // Hintergrund
   #
   # --- RGB-Wert der Schrift-/Rahmenfarbe fuer Tage ausserhalb des aktuellen Monats
   $col[$anz+3]=array('rgb'=>$addon::RGB_GREY, 'name'=>$cnam[9]);
   return $col;
   }   
public static function kal_hatch_gen($dif,$bgcolor) {
   #   Rueckgabe eines Style-Elementes zur 45 Grad-Schraffur.
   #   $dif              Streifenbreite in %
   #   $bgcolor          Hintergrundfarbe
   #
   $ii=0;
   $hatch='#hatch { background-image:linear-gradient(-45deg,';
   $n=0;
   for($i=0;$i<100;$i=$i+$dif):
      $kk=$ii+$dif;
      if($n==0):
        $col='transparent';
        $n=1;
        else:
        $col=$bgcolor;
        $n=0;
        endif;
      $hatch=$hatch.'
    '.$col.' '.$i.'%, '.$col.' '.$kk.'%,';
      $ii=$kk;
      endfor;
   $hatch=substr($hatch,0,strlen($hatch)-1).'); }';
   return $hatch;
   }
public static function kal_define_css() {
   #   Rueckgabe der Quelle einer Stylesheet-Datei
   #   basierend auf den konfigurierten Farben fuer die Kalendermenues.
   #   benutzte functions:
   #      self::kal_farben()
   #      self::kal_hatch_gen($dif,$bgcolor)
   #
   $addon=self::this_addon;
   #
   # --- Farben
   $farben=self::kal_farben();
   $kalcol=array();
   for($i=1;$i<=count($farben);$i=$i+1) $kalcol[$i]=$farben[$i]['rgb'];
   #
   # --- Streifenmuster fuer Monatstage, an denen Termine liegen
   $dif=10;     // Streifenbreite: 10%
   $hatch=self::kal_hatch_gen($dif,$kalcol[4]);
   #
   # --- Breitenwerte
   $monwidth  ='8.5em';                               //         (Monatsmenue)
   $tbl_width ='6em';                                 //         (Terminblatt)
   $tl_width  =intval(0.6*$addon::CSS_MOBILE).'em';   // ='21em' (Terminliste, mobil)
   $suth_width=intval(0.2*$addon::CSS_MOBILE).'em';   // = '7em' (Suchmenue, links)        
   $sutd_width=intval(0.8*$addon::CSS_MOBILE).'em';   // ='28em' (Suchmenue, rechts)
   #
   # --- CSS-Formate
   $form_box='padding:0.25em; border-collapse:separate; border-spacing:0.25em;';
   $form_col=array();
   $form_col[1]=$form_box.'
    color:'.$kalcol[1].'; background-color:transparent;
    border:solid 1px '.$kalcol[1].'; border-radius:0.25em;';
   $form_col[2]=$form_box.'
    color:'.$kalcol[2].'; background-color:transparent;
    border:solid 1px '.$kalcol[2].'; border-radius:0.25em;';
   $form_col[3]=$form_box.'
    color:'.$kalcol[1].'; background-color:'.$kalcol[3].';
    border:solid 1px '.$kalcol[1].'; border-radius:0.25em;';
   $form_col[4]=$form_box.'
    color:'.$kalcol[1].'; background-color:'.$kalcol[4].';
    border:solid 1px '.$kalcol[1].'; border-radius:0.25em;';
   $form_col[5]=$form_box.'
    color:'.$kalcol[1].'; background-color:'.$kalcol[5].';
    border:solid 1px '.$kalcol[1].'; border-radius:0.25em;';
   $form_col[6]=$form_box.'
    color:'.$kalcol[1].'; background-color:'.$kalcol[6].';
    border:solid 1px '.$kalcol[1].'; border-radius:0.25em;';
   $form_col[7]=$form_box.'
    color:'.$kalcol[7].'; background-color:transparent;
    border:solid 1px '.$kalcol[7].'; border-radius:0.25em;';
   $form_col[8]=$form_box.'
    color:'.$kalcol[7].'; background-color:'.$kalcol[8].';
    border:solid 1px '.$kalcol[7].'; border-radius:0.25em;';
   $form_col[9]=$form_box.'
    color:'.$kalcol[9].'; background-color:transparent;
    border:solid 1px '.$kalcol[9].'; border-radius:0.25em;';
   #
   # --- Stylesheet-Datei
   $string='/*   T e r m i n k a l e n d e r   */

/*   Awesome-Font vom AddOn be_style, die benutzten Icons   */
@font-face { font-family:"FontAwesome";
    src:url("../be_style/fonts/fontawesome-webfont.eot?v=4.7.0");
    src:url("../be_style/fonts/fontawesome-webfont.eot?#iefix&v=4.7.0") format("embedded-opentype"),
    url("../be_style/fonts/fontawesome-webfont.woff2?v=4.7.0") format("woff2"),
    url("../be_style/fonts/fontawesome-webfont.woff?v=4.7.0") format("woff"),
    url("../be_style/fonts/fontawesome-webfont.ttf?v=4.7.0") format("truetype"),
    url("../be_style/fonts/fontawesome-webfont.svg?v=4.7.0#fontawesomeregular") format("svg");
    font-weight:normal; font-style:normal; }
.fa { display:inline-block; font:normal normal normal 14px/1 FontAwesome;
    font-size:inherit; text-rendering:auto; -webkit-font-smoothing:antialiased;
    -moz-osx-font-smoothing:grayscale; }
.fa-calendar:before { content:"\f073"; }
.fa-search:before   { content:"\f002"; }
.fa-angle-double-left:before  { content:"\f100"; }
.fa-angle-double-right:before { content:"\f101"; }
.fa-angle-left:before  { content:"\f104"; }
.fa-angle-right:before { content:"\f105"; }

/*   Allgemeines   */
.kal_linkbutton  { cursor:pointer; text-align:left; }
.kal_bold        { font-weight:bold; color:'.$kalcol[2].' !important; }
.kal_boldbig     { font-size:1.2em; font-weight:bold; }
.kal_transparent { margin:0; padding:0; border:none; color:inherit; background-color:transparent; }
.kal_table       { background-color:inherit; }
.kal_box         { '.$form_col[1].' }
.kal_100pro      { width:100%; }
.kal_basecol     { color:'.$kalcol[1].'; }
.kal_fail        { color:red; }
.kal_msg         { '.$form_col[6].' }
.kal_indent      { padding-left:20px; }
.kal_olul        { margin:0; padding-left:30px; }
.kal_code        { color:rgb(0,120,0); background-color:rgb(242,249,244); }
.kal_right       { float:right; }
.kal_block_uebernehmen { color:'.$kalcol[2].'; font-weight:bold; font-style:italic; }

/*   Termin-Eingabeformular   */
.'.$addon::CSS_EINFORM.' { text-align:left; }
.'.$addon::CSS_EINFORM.' .th_einf { vertical-align:top; line-height:2em; text-align:left; font-weight:bold; }
.'.$addon::CSS_EINFORM.' .td_einf { line-height:1.5em; }
.'.$addon::CSS_EINFORM.' select   { padding:0.25em; }
.'.$addon::CSS_EINFORM.' .action  { font-weight:bold; color:'.$kalcol[2].'; }
.'.$addon::CSS_EINFORM.' .martop  { margin-top:1em; line-height:3em; }
.'.$addon::CSS_EINFORM.' .left    { max-width:8em; min-width:8em; margin-right:1em; vertical-align:top; white-space:nowrap; }
.'.$addon::CSS_EINFORM.' .right   { text-align:right; }
.'.$addon::CSS_EINFORM.' .text    { width:35em; padding:0 0.25em 0 0.25em; }
.'.$addon::CSS_EINFORM.' .date    { width: 8em; padding:0 0.25em 0 0.25em; }
.'.$addon::CSS_EINFORM.' .time    { width: 6em; padding:0 0.25em 0 0.25em; }
.'.$addon::CSS_EINFORM.' .int     { width: 4em; padding:0 0.25em 0 0.25em; }
.'.$addon::CSS_EINFORM.' .left2   { width:10em; vertical-align:top; white-space:nowrap; }
.'.$addon::CSS_EINFORM.' .pad     { padding-left:1em; text-align:left; }

/*   Formulare im Backend   */
.'.$addon::CSS_CONFIG.' { }
.'.$addon::CSS_CONFIG.' h4 { text-align:center; }
.'.$addon::CSS_CONFIG.' ol { margin-left:1em; }
.'.$addon::CSS_CONFIG.' ol li { padding-left:0.5em; }
.'.$addon::CSS_CONFIG.' th { text-align:center; }
.'.$addon::CSS_CONFIG.' .head { text-align:left; font-weight:bold; }
.'.$addon::CSS_CONFIG.' .indent { padding:0.1em 0.1em 0.1em 1.5em; vertical-align:top; white-space:nowrap; }
.'.$addon::CSS_CONFIG.' .undent { padding:0.1em 0.1em 0.1em 0.25em; white-space:nowrap; }
.'.$addon::CSS_CONFIG.' .number { padding:0.25em 1em 0.25em 0.25em; text-align:right; }
.'.$addon::CSS_CONFIG.' .inpint { width:4em; padding:0 0.25em 0 0.25em; text-align:right; }
.'.$addon::CSS_CONFIG.' .inptxt { width:14em; padding:0 0.25em 0 0.25em; }

/*   Farbkombinationen fuer Kalenderfelder   */
.'.$addon::CSS_COLS.'1 { '.$form_col[1].' }
.'.$addon::CSS_COLS.'2 { '.$form_col[2].' }
.'.$addon::CSS_COLS.'3 { '.$form_col[3].' }
.'.$addon::CSS_COLS.'4 { '.$form_col[4].' }
.'.$addon::CSS_COLS.'5 { '.$form_col[5].' }
.'.$addon::CSS_COLS.'6 { '.$form_col[6].' }
.'.$addon::CSS_COLS.'7 { '.$form_col[7].' }
.'.$addon::CSS_COLS.'8 { '.$form_col[8].' }
.'.$addon::CSS_COLS.'9 { '.$form_col[9].' }

/*   Terminblatt   */
.'.$addon::CSS_TERMBLATT.' { }
.'.$addon::CSS_TERMBLATT.' th { max-width:'.$tbl_width.'; padding:0.25em; vertical-align:top; text-align:left; }
.'.$addon::CSS_TERMBLATT.' td { padding:0.25em; vertical-align:top; text-align:left; }
.'.$addon::CSS_TERMBLATT.' .kopf { font-size:1.2em; font-weight:bold; color:'.$kalcol[1].'; }

/*   Monatsmenue   */
.'.$addon::CSS_MONMENUE.' { }
.'.$addon::CSS_MONMENUE.' .padl { padding:0 0.5em 0 0; }
.'.$addon::CSS_MONMENUE.' .padr { padding:0 0 0 0.5em; }
.'.$addon::CSS_MONMENUE.' .left  { float:left; }
.'.$addon::CSS_MONMENUE.' .right { float:right; }
.'.$addon::CSS_MONMENUE.' .center { text-align:center; }
.'.$addon::CSS_MONMENUE.' .rechts { text-align:right; }
.'.$addon::CSS_MONMENUE.' .width { min-width:'.$monwidth.'; max-width:'.$monwidth.'; }
.'.$addon::CSS_MONMENUE.' .wot { padding:0.25em; text-align:center; color:'.$kalcol[2].'; }
.'.$addon::CSS_MONMENUE.' .kalenderwoche  { padding:0.25em; text-align:right;  color:'.$kalcol[2].'; }
'.$hatch.'

/*   Monats-/Wochen-/Tagesblatt, Stundenleiste, Terminfeld   */
.'.$addon::CSS_MWTBLATT.' { }
.'.$addon::CSS_MWTBLATT.' hr { margin:0; padding:0; border:none; background-color:inherit; }
.'.$addon::CSS_MWTBLATT.' .vis_leiste { visibility:visible; }
.'.$addon::CSS_MWTBLATT.' .zeitenzeile { height:0; visibility:hidden; }
.'.$addon::CSS_MWTBLATT.' .left  { float:left; }
.'.$addon::CSS_MWTBLATT.' .right { float:right; }
.'.$addon::CSS_MWTBLATT.' .center { text-align:center; }
.'.$addon::CSS_MWTBLATT.' .pad0 { padding:0 0.5em 0 0.5em; color:'.$kalcol[1].'; }
.'.$addon::CSS_MWTBLATT.' .pad1 { padding:0 0.25em 0 0.25em; vertical-align:top; text-align:right;
    white-space:nowrap; font-weight:bold; color:'.$kalcol[2].'; }
.'.$addon::CSS_MWTBLATT.' .tag { width:100%; padding:0 !important; }
@media screen and (max-width:'.$addon::CSS_MOBILE.'em) {
    .'.$addon::CSS_MWTBLATT.' hr { margin:0.25em; padding:1px; border:solid 1px inherit; 
        background-color:'.$kalcol[3].'; }
    .'.$addon::CSS_MWTBLATT.' .vis_leiste { visibility:collapse; }
    .'.$addon::CSS_MWTBLATT.' .zeitenzeile { height:inherit; visibility:visible; }
    .'.$addon::CSS_MWTBLATT.' .pad1 { padding:0 0.25em 0 0.25em; vertical-align:top; text-align:right; white-space:normal;
        white-space:normal; font-weight:normal; color:'.$kalcol[2].'; }
    }
/*   Stundenleiste   */
.'.$addon::CSS_MWTBLATT.' .width1 { min-width:1em; max-width:1em; }
.'.$addon::CSS_MWTBLATT.' .width2 { min-width:2em; max-width:2em; }
.'.$addon::CSS_MWTBLATT.' .lineal { line-height:0.25em; border-left:solid 1px '.$kalcol[2].'; }
.'.$addon::CSS_MWTBLATT.' .center { text-align:center; color:'.$kalcol[2].'; }
.'.$addon::CSS_MWTBLATT.' .right  { text-align:right;  color:'.$kalcol[2].'; }
/*   Terminfeld   */
.'.$addon::CSS_MWTBLATT.' .termin { white-space:nowrap; overflow-x:hidden; margin-top:0.1em; margin-bottom:0.1em;
    '.$form_col[4].' }
.'.$addon::CSS_MWTBLATT.' .leertermin { '.$form_box.' margin-top:0.1em; margin-bottom:0.1em; }
@media screen and (max-width:'.$addon::CSS_MOBILE.'em) {
    .'.$addon::CSS_MWTBLATT.' .termin { white-space:normal;
        padding:0.25em; color:'.$kalcol[1].'; background-color:transparent; border:none; }
    }

/*   Suchmenue   */
.'.$addon::CSS_SUCH.' { }
.'.$addon::CSS_SUCH.' select, .'.$addon::CSS_SUCH.' input, .'.$addon::CSS_SUCH.' button { padding:0.1em; }
.'.$addon::CSS_SUCH.' select { width:100%; }
.'.$addon::CSS_SUCH.' .stichwort { max-width:'.$sutd_width.'; }
.'.$addon::CSS_SUCH.' .th { min-width:'.$suth_width.'; max-width:'.$suth_width.'; padding:0.25em; text-align:left;
    font-weight:bold; color:'.$kalcol[2].'; }
.'.$addon::CSS_SUCH.' .td { padding:0.25em; max-width:'.$sutd_width.'; }
.'.$addon::CSS_SUCH.' .kopf { font-size:1.2em; font-weight:bold; color:'.$kalcol[1].'; }
.'.$addon::CSS_SUCH.' .small { font-size:0.9em; vertical-align:text-top; }
.'.$addon::CSS_SUCH.' .filter_button { float:right; margin-top:1em; padding:0.25em; }
.'.$addon::CSS_SUCH.' .left  { text-align:left; }
.'.$addon::CSS_SUCH.' .liste { padding-left:1em; }
@media screen and (max-width:'.$addon::CSS_MOBILE.'em) {
    .'.$addon::CSS_SUCH.' .liste { padding-left:0; }
    .'.$addon::CSS_SUCH.' .th { min-width:0; }
    }

/*   Terminliste   */
.termlist_shadow { text-shadow:'.$addon::SHADOW.'; }
.termlist_hell   { color:rgb(230,230,100); }
.termlist_pfeil  { margin-bottom:-50px; font-size:50px; }
.termlist_hide   { display:none; }
.termlist_table { border-collapse:separate; border-spacing:2px; }
.termlist_th { padding:0.3em 0.5em 0.3em 0; text-align:right; white-space:nowrap;
    font-weight:bold; vertical-align:top; }
.termlist_td { padding:0.3em; }
.termlist_ort::before { content:" ("; }
.termlist_ort::after  { content:")"; }
.termlist_ausrichter::before { content:", Ausrichter: "; }
.termlist_komm::before { content:"\A"; white-space:pre; }  /* zu Beginn Zeilenumbruch */
@media screen and (max-width:'.$addon::CSS_MOBILE.'em) {
    .termlist_th { float:left; padding:0.6em 0 0 0; text-align:left; white-space:normal; }
    .termlist_td { float:left; padding:0 0 0 1em; min-width:'.$tl_width.'; }
    }';
   return $string;
   }
public static function kal_write_css() {
   #   Schreiben der Stylesheet-Datei /assets/addons/kal_termine/kal_termine.css.
   #   benutzte functions:
   #      self::kal_define_css()
   #
   $addon=self::this_addon;
   #
   # --- Erzeugen des Stylesheet-Textes
   $buffer=self::kal_define_css();
   #
   # --- ggf. Erzeugen des Ordners
   $dir=rex_path::addonAssets($addon::this_addon);
   if(!file_exists($dir)) mkdir($dir);
   #
   # --- Schreiben der Datei
   $file=rex_path::addonAssets($addon::this_addon,$addon::this_addon.'.css');
   $handle=fopen($file,'w');
   fwrite($handle,$buffer);
   fclose($handle);
   }
public static function kal_post_in($key,$type) {
   #   Rueckgabe des Wertes von $_POST[$key]. Nicht-leere Werte werden
   #   unveraendert zurueck gegeben. Leere Werte werden als '0' zurueck
   #   gegeben, falls $type=='int'.
   #   $key            Schluessel von $_POST
   #   $type           ='int':    leere Werte werden als '0' zurueck gegeben
   #                   ='string': leere Werte werden unveraendert zurueck gegeben
   #                   =sonst:    eine Fehlermeldung wird zurueck gegeben
   #
   if($type!='int' and $type!='string')
     return '+++++ type!="int" and type!="string"';
   $post=$_POST[$key];
   if(!empty($post)):
     return $post;
     else:
     if($type=='int') return '0';
     return '';
     endif;
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
   $file=rex_path::addon($addon,'vendor/'.$addon::PALETTE_FILE);
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
   #   benutzte functions:
   #      self::kal_read_termlist_colors()
   #      self::kal_termlist_colors($addon::PALETTE_SIZE)
   #
   $addon=self::this_addon;
   if(file_exists(rex_path::addon($addon,'vendor/'.$addon::PALETTE_FILE))):
     return self::kal_read_termlist_colors();
     else:
     return self::kal_termlist_colors($addon::PALETTE_SIZE);
     endif;
   }
public static function kal_html_termlist_colors($colors) {
   #   Rueckgabe des HTML-Codes zur Ausgabe einer Folge von Farben fuer eine
   #   moegliche farblichen Markierung der Terminkategorien in einer Terminliste.
   #   $colors           Array der Farben (Nummerierung ab 1), jede Farbe ist
   #                     in der Form 'rgb(red,green,blue)' gegeben
   #   benutzte functions:
   #      $addon::kal_get_terminkategorien()
   #
   $addon=self::this_addon;
   $nn=count($colors);
   if($nn<=0) return '
<p>Es ist keine eigene Farbpalette vorgegeben.</p>';
   #
   # --- vorhandene Terminkategorien markieren
   $kats=$addon::kal_get_terminkategorien();
   $nzkats=count($kats);
   $string='
<div>(*) helle Schrift- und Randfarben sind mit <span class="termlist_shadow termlist_hell">Schatten</span> besser lesbar bzw. sichtbar.</div>
<table class="'.$addon::CSS_CONFIG.'">
    <tr><th colspan=2">Kateg.-Id &nbsp;</th>
        <th>Farbe</th>
        <th class="indent">(*) Farbcodierung</th>
        <th class="indent">vorhandene Kategorie</th></tr>';
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
    <tr><td align="right">'.$nr.'</td>
        <td class="indent">&nbsp;</td>
        <td style="background-color:'.$col.';" width="70"></td>
        <td class="indent termlist_shadow" style="color:'.$col.';">'.$col.'</td>
        <td class="indent">'.$antw.'</td></tr>';
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
   #                   =2: nur linker und rechter Rand
   #                   =3: nur oberer und unterer Rand
   #                   =4: alle Raender
   #       ['pix']     Randdicke in Anzahl Pixel (= 1, 2, 3, 4, 5)
   #       ['title']   =0: keine Textfarbe fuer den Veranstaltungstitel
   #                   =1: Linkfarbe = Randfarbe fuer den Veranstaltungstitel
   #       ['date']    =0: keine Textfarbe fuer Datum/Uhrzeit
   #                   =1: Textfarbe = Randfarbe fuer Datum/Uhrzeit
   #       ['shadow']  =0: keine Text- und Box-Schatten
   #                   =1: Text und Box-Schatten (hilfreich bei hellen Farben)
   #                       z.B.: 1) '-1px 1px 0 black' (links 1px, unten 1px,
   #                                kein Blur-Effekt, schwarz)
   #                             2) '0 0' (keinerlei Schatten)
   #   benutzte functions:
   #      self::kal_get_termlist_colors()
   #      $addon::kal_get_terminkategorien()
   #
   $addon=self::this_addon;
   $kats=kal_termine::kal_get_terminkategorien();
   $nzkats=count($kats);
   if($nzkats<=0) return;
   #
   $keys=$addon::PALETTE_KEYS;
   $intsid=$param[$keys[0]];
   $intpix=$param[$keys[1]];
   $inttit=$param[$keys[2]];
   $intdat=$param[$keys[3]];
   $intsha=$param[$keys[4]];
   #
   # --- AddOn-Farben oder Benutzer-definierte Farben?
   $XX='XXXXX';
   $colors=self::kal_get_termlist_colors();
   $string='';
   #
   # --- Text- und Box-Schatten
   $txtnoshad=' text-shadow:unset';
   $boxnoshad=' box-shadow:unset';
   if($intsha>0):
     $sha=$addon::SHADOW;
     $col=explode(' ',$sha)[3];
     $boxshad=$boxnoshad;   // falls kein Rand definiert ist: kein Schatten
     if($intsid==1) $boxshad=' box-shadow:-1px 0 0 '.$col;
     if($intsid==2) $boxshad=' box-shadow:-1px 0 0 '.$col;
     if($intsid==3) $boxshad=' box-shadow:0 -1px 0 '.$col;
     if($intsid==4) $boxshad=' box-shadow:-1px -1px 0 '.$col;
     $txtshad=' text-shadow:'.$sha.';';
     else:
     $boxshad=$boxnoshad;
     $txtshad=$txtnoshad;
     endif;
   #
   # --- Randdicke
   if($intpix<=0) $intpix=1;
   if($intpix>=$addon::MAX_PIX) $intpix=$addon::MAX_PIX;
   $px=$intpix.'px';
   #
   # --- Rand-Sides und -Farben
   if($intsid<=0)
     $stbord='border-left:none; border-right:none;
               border-top:none; border-bottom:none;
              '.$boxshad;
   if($intsid==1)
     $stbord='border-left:solid '.$px.' '.$XX.'; border-right:solid 1px transparent;
               border-top:none; border-bottom:none;
              '.$boxshad;
   if($intsid==2)
     $stbord='border-left:solid '.$px.' '.$XX.'; border-right:solid '.$px.' '.$XX.';
               border-top:none; border-bottom:none;
              '.$boxshad;
   if($intsid==3)
     $stbord='border-left:none; border-right:none;
               border-top:solid '.$px.' '.$XX.'; border-bottom:solid '.$px.' '.$XX.';
              '.$boxshad;
   if($intsid==4)
     $stbord='border-left:solid '.$px.' '.$XX.'; border-right:solid '.$px.' '.$XX.';
               border-top:solid '.$px.' '.$XX.'; border-bottom:solid '.$px.' '.$XX.';
              '.$boxshad;
   for($i=1;$i<=$nzkats;$i=$i+1):
      $kat_id=$i;
      if($kat_id<=9) $kat_id='0'.$kat_id;
      $stb=$stbord;
      $stb=str_replace($XX,$colors[$i],$stb);
      $string=$string.'
.termbord_'.$kat_id.' { '.$stb.' }';
      endfor;
   #
   # --- Farbe fuer den Veranstaltungstitel
   $sttext='color:'.$XX.';';
   for($i=1;$i<=$nzkats;$i=$i+1):
      $kat_id=$i;
      if($kat_id<=9) $kat_id='0'.$kat_id;
      $stl=$sttext;
      if($inttit>0):
        $stl=str_replace($XX,$colors[$i],$stl).$txtshad;
        else:
        $stl=str_replace($XX,'inherit',$stl).$txtnoshad;
        endif;
      $string=$string.'
.termtitle_'.$kat_id.' { '.$stl.' }';
      endfor;
   #
   # --- Farbe fuer Datum/Uhrzeit
   $stdate='color:'.$XX.';';
   for($i=1;$i<=$nzkats;$i=$i+1):
      $kat_id=$i;
      if($kat_id<=9) $kat_id='0'.$kat_id;
      $stt=$stdate;
      if($intdat>0):
        $stt=str_replace($XX,$colors[$i],$stt).$txtshad;
        else:
        $stt=str_replace($XX,'inherit',$stt).$txtnoshad;
        endif;
      $string=$string.'
.termdate_'.$kat_id.' { '.$stt.' }';
      endfor;
   #
   if(!empty($string))
     $string='

/*   Terminliste, farbliche Markierung der Termine nach Kategorien   */'.$string;
   return $string;
   }
public static function kal_add_termlist_css($param) {
   #   Ergaenzen der Stylesheet-Datei um die Styles zur zusaetzlichen Gestaltung
   #   der Terminliste. Dafuer wird aus systematischen Gruenden zunaechst die
   #   Stylesheet-Datei neu geschrieben.
   #   $param          assoziatives Array der Parameter fuer die Styles,
   #                   vergl. kal_define_termlist_css(...)
   #   benutzte functions:
   #      self::kal_write_css()
   #      self::kal_define_termlist_css($param)
   #
   $addon=self::this_addon;
   #
   # --- keine Ergaenzung falls: kein Rand, keine Titelfarbe, keine Datumsfarbe
   $keys=$addon::PALETTE_KEYS;
   if($param[$keys[0]]<=0 and $param[$keys[2]]<=0 and $param[$keys[3]]<=0) return;
   #
   # --- ggf. Erzeugen des Ordners
   $dir=rex_path::addonAssets($addon::this_addon);
   if(!file_exists($dir)) mkdir($dir);
   #
   # --- Stylesheet-Datei neu schreiben
   self::kal_write_css();
   #
   # --- Ergaenzen der Stylesheet-Datei
   $buffer=self::kal_define_termlist_css($param);
   $file=rex_path::addonAssets($addon::this_addon,$addon::this_addon.'.css');
   $handle=fopen($file,'a');
   fwrite($handle,$buffer);
   fclose($handle);
   }
public static function kal_config_termlist() {
   #   Eingabeformular fuer die Daten zur Ergaenzung der Stylesheet-Datei.
   #   Darin werden Styles fuer eine farbliche Markierung der Terminkategorien
   #   in einer Terminliste definiert, basierend auf einer gegebenen Farbpalette
   #   (Standardpalette oder Benutzer-definierte Palette).
   #   Aufgerufen nur in pages/settings_termlist.php
   #   benutzte functions:
   #      self::kal_post_in($key,$type)
   #      self::kal_termlist_colors($nz)
   #      self::kal_read_termlist_colors()
   #      self::kal_html_termlist_colors($colors)
   #      self::kal_define_termlist_css($param)
   #      self::kal_add_termlist_css($param)
   #      kal_termine_tabelle::kal_aktuelle_wochen_spieltermine()
   #      kal_termine_menues::kal_terminliste($termin)
   #
   $addon=self::this_addon;
   #
   # --- Farbpaletten
   $defcols =self::kal_termlist_colors($addon::PALETTE_SIZE);
   $ndefcols=count($defcols);
   $usrcols =self::kal_read_termlist_colors();
   #
   # --- konfigurierte Daten einlesen
   $confdat=rex_config::get($addon,$addon::TERMLIST);
   $keys=$addon::PALETTE_KEYS;
   if(empty($confdat)):
     $intsid=0;
     $intpix=0;
     $inttit=0;
     $intdat=0;
     $intsha=0;
     else:
     $arr=explode(',',$confdat);
     $intsid=$arr[0];
     $intpix=$arr[1];
     $inttit=$arr[2];
     $intdat=$arr[3];
     $intsha=$arr[4];
     endif;
   $param=array($keys[0]=>$intsid,$keys[1]=>$intpix,$keys[2]=>$inttit,
                $keys[3]=>$intdat,$keys[4]=>$intsha);
   #
   # --- neu eingegebene Daten einlesen und speichern
   $sent=self::kal_post_in($addon::TERMLIST,'string');
   if(!empty($sent)):
     $sid=intval(self::kal_post_in($keys[0],'int'));
     if($sid!=$intsid) $intsid=$sid;
     $pix=intval(self::kal_post_in($keys[1],'int'));
     if($pix!=$intpix) $intpix=$pix;
     $tit=0;
     if(!empty(self::kal_post_in($keys[2],'string'))) $tit=1;
     if(!$tit==$inttit) $inttit=$tit;
     $dat=0;
     if(!empty(self::kal_post_in($keys[3],'string'))) $dat=1;
     if(!$dat==$intdat) $intdat=$dat;
     $sha=0;
     if(!empty(self::kal_post_in($keys[4],'string'))) $sha=1;
     if(!$sha==$intsha) $intsha=$sha;
     #
     # --- Terminliste-Konfigurationsdaten speichern
     $string=$intsid.','.$intpix.','.$inttit.','.$intdat.','.$intsha;
     rex_config::set($addon,$addon::TERMLIST,$string);
     rex_config::save();
     #
     # --- Stylesheet neu schreiben
     $param=array($keys[0]=>$intsid,$keys[1]=>$intpix,$keys[2]=>$inttit,
                  $keys[3]=>$intdat,$keys[4]=>$intsha);               
     self::kal_add_termlist_css($param);
     endif;
   #
   # --- Einfuehrung
   $palette='<b>eigene Farbpalette</b>';
   $zusatz='</div>';
   $colfil=rex_path::addon($addon,'vendor/'.$addon::PALETTE_FILE);
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
Stattdessen kann auch eine '.$palette.' vorgegeben werden. Diese wird als Datei
namens <code>'.$addon::PALETTE_FILE.'</code> im AddOn-Ordner <code>vendor</code>
abgelegt und enthält die Farben zeilenweise.';
   $string=$string.$zusatz.'
<form method="post">';
   #
   # --- Rahmen
   $string=$string.'
<div><br><b>Einrahmung der Terminbeschreibung:</b></div>
<div class="kal_indent">Die Beschreibung jedes Termins (rechte Spalte der Tabelle)
kann mit einem teilweisen oder vollständigen farbigen Rahmen versehen werden.
Die Farbe des Rahmens ergibt sich aus der Terminkategorie. Die Dicke des Rahmens
(Anzahl Pixel) kann ausgewählt werden.
<table class="kal_table">
    <tr valign="top">
        <td class="kal_indent">
            <u>Rahmentyp:</u>';
   #     Rahmentyp
   $border=array(
      'ohne Rahmen',
      'nur linker Rand',
      'nur linker und rechter Rand',
      'nur oberer und unterer Rand',
      'vollständiger Rahmen');
   for($i=0;$i<count($border);$i=$i+1):
      $chk='';
      if($i==$intsid) $chk=' checked';
      $string=$string.'
            <br><input type="radio" name="'.$keys[0].'" value="'.$i.'"'.$chk.'>&nbsp;&nbsp;'.$border[$i];
      endfor;
   #     Rahmendicke
   $string=$string.'</td>
        <td class="kal_indent">
            <u>Rahmendicke:</u>
            <select name="'.$keys[1].'">';
   for($i=1;$i<=$addon::MAX_PIX;$i=$i+1):
      $sel='';
      if($i==$intpix) $sel=' selected';
      $string=$string.'
                 <option value="'.$i.'"'.$sel.'>'.$i.'</option>';
      endfor;
   $string=$string.'
            </select></td></tr>
</table>
</div>';
   #
   # --- Veranstaltungstitel
   $chk='';
   if($inttit>0) $chk=' checked';
   $string=$string.'
<div><b>Farbiger Veranstaltungstitel:</b></div>
<div class="kal_indent">
    <div class="kal_indent">
    <input type="checkbox" name="'.$keys[2].'"'.$chk.'>&nbsp;&nbsp;
    Veranstaltungstitel in der Textfarbe der Terminkategorie darstellen
    </div>
</div>';
   #
   # --- Datums-/Zeitangabe
   $chk='';
   if($intdat>0) $chk=' checked';
   $string=$string.'
<div><b>Farbige Datums-/Zeitangabe:</b></div>
<div class="kal_indent">
    <div class="kal_indent">
    <input type="checkbox" name="'.$keys[3].'"'.$chk.'>&nbsp;&nbsp;
    Veranstaltungdatum in der Textfarbe der Terminkategorie darstellen
    </div>
</div>';
   #
   # --- Schatten / kein Schatten fuer den Rand und die farbigen Texte
   $chk='';
   if($intsha>0) $chk=' checked';
   $string=$string.'
<div><b>Farbige Elemente mit Schatten:</b></div>
<div class="kal_indent">
    <div class="kal_indent">
    <input type="checkbox" name="'.$keys[4].'"'.$chk.'>&nbsp;&nbsp;
    obige farbige Elemente mit Schatten darstellen (bei hellen Farben besser lesbar)
    </div>
</div>';
   #
   # --- Absende-Button
   $string=$string.'
<br>
<button type="submit" name="termlist" value="sent" class="btn btn-apply"
        title="Daten übernehmen und Stylesheet ergänzen">Daten übernehmen</button>
</form>';
   #
   # --- Link-Pfeil 'nach oben'
   $pfeil='
<div class="termlist_pfeil">
<a onClick="document.getElementById(\'default\').style.display=\'none\';
            document.getElementById(\'user\').style.display=\'none\';"
   href="#"
   title="nach oben">&uarr;</a>
</div>';
   #
   # --- Ausgabe der Spieldaten-Terminliste mit den neuen Styles
   $styles=self::kal_define_termlist_css($param);
   $string=$string.'
<style>
'.$styles.'
</style>';
   $terms=kal_termine_tabelle::kal_aktuelle_wochen_spieltermine();
   $string=$string.'
<br>'.$pfeil.'
<h4 align="center">Exemplarische Terminliste mit Spieldaten (gemäß obigem Stylesheet)</h4>
'.kal_termine_menues::kal_terminliste($terms);
   #
   # --- Ausgabe der eigenen Farbpalette
   if(file_exists($colfil)):
     $userhtml=self::kal_html_termlist_colors($usrcols);
     $string=$string.'
<div id="user" class="termlist_hide">
<br><a name="user"></a>'.$pfeil.'
<h4 align="center">Eigene Farbpalette</h4>'.$userhtml.'
</div>';
     endif;
   #
   # --- Ausgabe der Default-Farbpalette
   $defhtml=self::kal_html_termlist_colors($defcols);
   $string=$string.'
<div id="default" class="termlist_hide">
<br><a name="default"></a>'.$pfeil.'
<h4 align="center">Default-Farbpalette</h4>'.$defhtml.'
</div>';
   #
   $string=$string.$pfeil.'
<br><br><br>';
   echo $string;
   }
#
#----------------------------------------- Einlesen der Konfigurationsdaten
public static function kal_config_form($readsett) {
   #   Anzeige des Formulars zur Eingabe der Konfigurationsdaten.
   #   $readsett         Array der Formulardaten im Format der Default-Konfiguration
   #   benutzte functions:
   #      self::kal_farben()
   #      self::kal_split_color($color)
   #
   $addon=self::this_addon;
   #
   # --- Ueberschrift Farbe
   $string='
<div class="'.$addon::CSS_CONFIG.'">
<form id="config" method="post">
<table class="kal_table">
    <tr><td class="head" colspan="4">
            Farben in den Kalendermenüs (RGB):</td></tr>
    <tr><td class="indent">
            zumeist abgeleitet von der definierten Grundfarbe</td>
        <th class="number">R&nbsp;</th>
        <th class="number">G&nbsp;</th>
        <th class="number">B&nbsp;</th></tr>';
   #
   # --- Formular Grundfarbe
   $farben=self::kal_farben();
   $colrgb=array();
   $coltxt=array();
   for($i=1;$i<=count($farben);$i=$i+1):
      $colrgb[$i]=$farben[$i]['rgb'];
      $coltxt[$i]=$farben[$i]['name'];
      endfor;
   $col=self::kal_split_color($readsett[$addon::DEFAULT_COL_KEY]);
   $string=$string.'
    <tr><td class="indent">
            <div class="indent '.$addon::CSS_COLS.'1">'.$coltxt[1].'</div></td>
        <td class="indent">
            <input class="inpint" type="text" name="red"   value="'.$col['red'].'"></td>
        <td class="undent">
            <input class="inpint" type="text" name="green" value="'.$col['green'].'"></td>
        <td class="undent">
            <input class="inpint" type="text" name="blue"  value="'.$col['blue'].'"></td></tr>';
   #
   # --- restliche Farben
   for($i=2;$i<=count($farben);$i=$i+1):
      $col=self::kal_split_color($colrgb[$i]);
      $string=$string.'
    <tr><td class="indent">
            <div class="indent '.$addon::CSS_COLS.''.$i.'">'.$coltxt[$i].'</div></td>
        <td class="number">'.$col['red'].'</td>
        <td class="number">'.$col['green'].'</td>
        <td class="number">'.$col['blue'].'</td></tr>';
      endfor;
   #
   # --- Formular Stundenleiste
   $string=$string.'
    <tr><td class="head" colspan="4">
            Darstellung des Uhrzeit-Bereichs bei Tagesterminen:</td></tr>
    <tr><td class="indent">Start-Uhrzeit &nbsp; <i>(ganze Zahl)</i>:</td>
        <td class="indent">
            <input class="inpint" type="text" name="'.$addon::DEFAULT_BEG_KEY.'" value="'.$readsett[$addon::DEFAULT_BEG_KEY].'"></td>
        <td colspan="2"><tt>&nbsp;:00 Uhr</tt></td></tr>
    <tr><td class="indent">End-Uhrzeit &nbsp; <i>(ganze Zahl)</i>:</td>
        <td class="indent">
            <input class="inpint" type="text" name="'.$addon::DEFAULT_END_KEY.'" value="'.$readsett[$addon::DEFAULT_END_KEY].'"></td>
        <td colspan="2"><tt>&nbsp;:00 Uhr</tt></td></tr>
    <tr><td class="indent">Gesamtbreite &nbsp; <i>(ganze Zahl, zwischen '.$addon::MIN_ANZ_PIXEL.' und '.$addon::MAX_ANZ_PIXEL.')</i>:</td>
        <td class="indent">
            <input class="inpint" type="text" name="'.$addon::DEFAULT_PIX_KEY.'" value="'.$readsett[$addon::DEFAULT_PIX_KEY].'"></td>
        <td colspan="2"><tt>&nbsp;Pixel</tt></td></tr>';
   #
   # --- Formular, Terminkategorien
   $string=$string.'
    <tr><td class="head" colspan="4">Terminkategorien:</td></tr>';
   $anz=0;
   for($i=1;$i<=count($readsett)-4;$i=$i+1):
      $key=$addon::KAT_KEYS.strval($i);
      $set=$readsett[$key];
      if(!empty($set)):
        $string=$string.'
    <tr><td class="indent">'.$i.'</td>
        <td class="indent" colspan="3">
            <input class="inptxt" type="text" name="'.$key.'" value="'.$set.'"></td></tr>';
        $anz=$anz+1;
        endif;
      endfor;
   #
   # --- Formular, leeres Feld fuer eine neue Terminkategorie
   $i=$anz+1;
   $key=$addon::KAT_KEYS.strval($i);
   $string=$string.'
    <tr><td class="indent">
            '.$i.' &nbsp; <i>(hier kann eine neue Kategorie angefügt werden)</i></td>
        <td class="indent" colspan="3">
            <input class="inptxt" type="text" name="'.$key.'" value=""></td></tr>
    <tr valign="top">
        <td class="indent">
            Zum <b>Entfernen</b> der <b>jeweils letzten</b> Terminkategorie ...</td>
        <td class="indent" colspan="3" align="right">
            ... das zugehörige <b>Feld leeren</b>!</td></tr>
        <td class="indent" colspan="4" align="right">
            <i>Mit dem <b>Speichern</b> werden auch die <b>Termine</b> der entfernten Kategorie <b>entfernt</b>!</i></td></tr>';
   #
   # --- Formular, Abschluss
   $rebut='auf Defaultwerte zurücksetzen';
   $retit='Parameter '.$rebut.', Parameter und css-Stylesheet speichern';
   $spbut='speichern';
   $sptit='Parameter und css-Stylesheet '.$spbut;
   $bl=' &nbsp; &nbsp; &nbsp; ';
   $string=$string.'
    <tr><td colspan="4"><br>
            <button class="btn btn-save"
                    type="submit" name="save"  value="save"
                    title="'.$sptit.'">'.$bl.$spbut.$bl.'</button>
            <button class="btn btn-default kal_right"
                    type="submit" name="reset" value="reset"
                    title="'.$retit.'">'.$rebut.' und speichern</button></td></tr>
</table>
</form>
</div>';
   echo $string;
   }
public static function kal_clean_termine() {
   #   Loeschen (und Rueckgabe) der Termine, die zu einer geloeschten
   #   Terminkategorie gehoeren.
   #   benutzte functions:
   #      $addon::kal_get_terminkategorien()
   #
   $addon=self::this_addon;
   $keypid=$addon::TAB_KEY[0];   // pid
   $keykat=$addon::TAB_KEY[1];   // kat_id
   #
   # --- konfigurierte Terminkategorien
   $kats=$addon::kal_get_terminkategorien();
   $anz=count($kats);
   $idmax=$kats[$anz-1]['id'];
   #
   # --- Termine zu geloeschten Terminkategorien finden und loeschen
   $sql=rex_sql::factory();
   $table=rex::getTablePrefix().$addon;
   $term=$sql->getArray('SELECT * FROM '.$table);
   $lost_term=array();
   $m=0;
   for($i=0;$i<count($term);$i=$i+1)
      if($term[$i][$keykat]>$idmax):
        $m=$m+1;
        $pid=$term[$i][$keypid];
        $delete='DELETE FROM '.$table.' WHERE '.$keypid.'='.$pid;
        $sql->setQuery($delete);
        $lost_term[$m]=$term[$i];
        endif;
   return $lost_term;
   }
public static function kal_config() {
   #   Einlesen und Setzen der Konfigurationsdaten.
   #   Aufgerufen nur in pages/settings.php
   #   benutzte functions:
   #      self::kal_split_color($color)
   #      self::kal_write_css()
   #      self::kal_post_in($key,$type)
   #      self::kal_config_form($readsett)
   #      self::kal_clean_termine()
   #      $addon::kal_default_config()
   #      $addon::kal_get_config()
   #      $addon::kal_set_config($settings)
   #
   $addon=self::this_addon;
   #
   # --- speichern oder zuruecksetzen?
   $save =self::kal_post_in('save','string');
   $reset=self::kal_post_in('reset','string');
   #
   # --- Konfigurationsdaten zuruecksetzen
   if(!empty($reset)):
     $defsett=$addon::kal_default_config();
     $addon::kal_set_config($defsett);
     self::kal_write_css();   // Stylesheet ueberschreiben
     endif;
   #
   $confsett=$addon::kal_get_config();
   #
   # --- Anzahl Kategorien
   $keys=array_keys($confsett);
   $anzkat=count($keys)-4;   // abzueglich Grundfarbe, Startzeit, Endzeit, Pixel
   #
   # --- Auslesen der Daten
   $readsett=array();
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if($key==$addon::DEFAULT_COL_KEY):
        #
        # --- Grundfarbe
        if(empty($save)):
          #     aus der Konfiguration
          $confcol=self::kal_split_color($confsett[$key]);
          $red=$confcol['red'];
          $gre=$confcol['green'];
          $blu=$confcol['blue'];
          else:
          #     eingelesen
          $red=self::kal_post_in('red',  'int');
          $gre=self::kal_post_in('green','int');
          $blu=self::kal_post_in('blue', 'int');
          if($red>$addon::RGB_MAX or $gre>$addon::RGB_MAX or $blu>$addon::RGB_MAX):
            #     zuruecksetzen, falls RGB-Wert zu gross
            if($red>$addon::RGB_MAX) $red=$confcol['red'];
            if($gre>$addon::RGB_MAX) $gre=$confcol['green'];
            if($blu>$addon::RGB_MAX) $blu=$confcol['blue'];
            echo rex_view::warning('Keiner dieser RGB-Werte darf größer als <code>'.
                                   $addon::RGB_MAX.'</code> sein! Wert bleibt erhalten.');
            endif;
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
            $sett=self::kal_post_in($key,'string');
            else:
            $sett=self::kal_post_in($key,'int');
            endif;
          #     Ueberpruefen der integer-Werte
          if($key==$addon::DEFAULT_BEG_KEY and 
             ($sett<$addon::MIN_BEG or $sett>$addon::MAX_BEG
              or $sett>=$confsett[$addon::DEFAULT_END_KEY])):
            echo rex_view::warning('Die Start-Uhrzeit <code>'.$sett.'</code> muss <code>zwischen '.
                                   $addon::MIN_BEG.' und '.$addon::MAX_BEG.' Uhr</code> und '.
                                   '<code>vor der End-Uhrzeit</code> liegen! Wert bleibt erhalten.');
            $sett=$conf;
            endif;
          if($key==$addon::DEFAULT_END_KEY and
             ($sett<$addon::MIN_END or $sett>$addon::MAX_END
              or $sett<=$confsett[$addon::DEFAULT_BEG_KEY])):
            echo rex_view::warning('Die End-Uhrzeit muss <code>zwischen '.$addon::MIN_END.
                                   ' und '.$addon::MAX_END.' Uhr</code> und '.
                                   '<code>nach der Start-Uhrzeit</code> liegen! Wert bleibt erhalten.');
            $sett=$conf;
            endif;
          if($key==$addon::DEFAULT_PIX_KEY and
             ($sett<$addon::MIN_ANZ_PIXEL or $sett>$addon::MAX_ANZ_PIXEL)):
            echo rex_view::warning('Die Gesamtbreite muss <code>zwischen '.$addon::MIN_ANZ_PIXEL.
                                   ' und '.$addon::MAX_ANZ_PIXEL.' Pixel</code> liegen! Wert bleibt erhalten.');
            $sett=$conf;
            endif;          
          #     ggf. Entfernen von Kategorien
          if(substr($key,0,3)==$addon::KAT_KEYS and substr($key,3)>1 and empty($sett)
             and !empty($conf))
            $sett='';
          endif;
        $readsett[$key]=$sett;
        endif;
      endfor;
   #
   # --- (bisher leere) neue Kategorie einlesen
   $neu=intval($anzkat+1);
   $key=$addon::KAT_KEYS.strval($neu);
   $post=self::kal_post_in($key,'string');
   if(!empty($post)) $readsett[$key]=$post;
   #
   # --- Konfigurationsparameter speichern
   if(!empty($save)):
     $keys=array_keys($readsett);
     $settconf=array();
     $m=0;
     #     leere Kategorien entfernen
     for($i=0;$i<count($keys);$i=$i+1):
        $key=$keys[$i];
        if(substr($key,0,3)==$addon::KAT_KEYS and empty($readsett[$key])) continue;
        $settconf[$key]=$readsett[$key];
        endfor;
     #     Terminliste-Konfiguration uebernehmen
     $settconf[$addon::TERMLIST]=rex_config::get($addon,$addon::TERMLIST);
     #     Konfiguration neu setzen
     $addon::kal_set_config($settconf);
     #     ggf. Termine zu entfernten Kategorien loeschen
     self::kal_clean_termine();
     #     Stylesheet ueberschreiben
     self::kal_write_css();
     endif;
   #
   # --- Eingabeformular anzeigen
   self::kal_config_form($readsett);
   }
}
?>