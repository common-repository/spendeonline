<?php
/*
Plugin Name: SpendeOnline.org
Plugin URI: http://www.spendeonline.org/
Description: Spenden-Modul fuer WordPress
Version: 1.6
Date: 12 Jul 2022
Author: GRITH AG
Author URI: http://www.grith-ag.de/
*/

if (!function_exists('spendenonline'))
{
   function spendenonline($content)
   {
      $html = $content;
      if (SpendenOnlineContains($html,"[spendeonline"))
      {
         $h=SpendenOnlineParse($html,"[spendeonline");
         $url=trim(SpendenOnlineParse($html,"]"));
         if (substr($url,0,1)==":") $url=trim(substr($url,1)); else $url="demospende";
         $html=$h.SpendenOnlineRequest($url).$html;
      }
      return $html;
   }
   add_filter('the_content', 'spendenonline', 9);
   add_action('wp_enqueue_scripts', 'SpendenOnlineStyle');
   add_action('wp_enqueue_scripts', 'SpendenOnlineScript');
}

function SpendenOnlineStyle()
{
   wp_enqueue_style('spendeonline', 'https://spendeonline.org/admin/webservices/spendeonline/spendeonline.css', false);
}

function SpendenOnlineScript()
{
   wp_enqueue_script('spendeonline-js', 'https://spendeonline.org/admin/webservices/spendeonline/spendeonline.js', false);
}

function SpendenOnlineParse(&$a, $ch)
{
   $pos=strpos($a, $ch);
   if ($pos===false) { $ret=$a; $a=""; } else { $ret=substr($a, 0, $pos); $a=substr($a, $pos+strlen($ch)); }
   return $ret;
}

function SpendenOnlineContains($txt, $s)
{
   if ($s=="") return false;
   return !(strpos($txt, $s)===false);
}

function SpendenOnlineRequest($url)
{
   $curl = curl_init();
   if (!$curl) return "Das PHP-Modul 'curl' ist nicht installiert.";
   curl_setopt($curl, CURLOPT_URL, "https://spendeonline.org/$url/?spenden&dialog=4");
   curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept-Language: ".$_SERVER["HTTP_ACCEPT_LANGUAGE"]));
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
   curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
   $result="<DIV id=\"spendeonline\" url=\"https://spendeonline.org/$url/\">";
   $result.=utf8_encode(str_replace("\r","",str_replace("\n","",curl_exec($curl))));
   if (curl_error($curl)!="") $result.="error:".utf8_encode(curl_error($curl));
   curl_close($curl);
   $result.="</DIV>";
   return $result;
}

?>
