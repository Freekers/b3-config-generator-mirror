<?php
/**
*BigBrotherBot Config File Generator
*Version: 1.0
*Date: 05.07.2010
*Author: Freelander
*Author URI: http://www.fps-gamer.net

*Copyright(c)2010  Freelander  <mailto:freelander@fps-gamer.net>

*This program is free software; you can redistribute it and/or modify
*it under the terms of the GNU General Public License, version 2, as 
*published by the Free Software Foundation.

*This program is distributed in the hope that it will be useful,
*but WITHOUT ANY WARRANTY; without even the implied warranty of
*MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*GNU General Public License for more details.

*You should have received a copy of the GNU General Public License
*along with this program; if not, write to the Free Software
*Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$version = '1.0';

function select_your_game()
{
  //list of available parsers
  $parsers = array ( 
                     "cod"     => "Call of Duty", 
                     "cod2"    => "Call of Duty 2",
                     "cod4"    => "Call of Duty 4",
                     "cod5"    => "Call of Duty: World at War", 
                     "iourt41" => "Urban Terror", 
                     "etpro"   => "Wolfenstein Enemy Territory", 
                     "wop"     => "World of Padman", 
                     "smg11"   => "Smokin' Guns", 
                     "bfbc2"   => "Battle Field Bad Company 2"
                   );
  
  foreach ($parsers as $parser => $gamename)
    echo "<option value=".$parser.">".$gamename."</option>\n";
}

function select_log_level()
{
  //possible log levels
  $loglevels = array (
                       "8"  => "8 - Extra verbose debugging",
                       "9"  => "9 - Verbose debugging",
                       "10" => "10 - Debugging",
                       "20" => "20 - Extra information",
                       "21" => "21 - B3 log messages",
                       "22" => "22 - Game log messages",
                       "30" => "30 - Warnings",
                       "40" => "40 - Errors",
                       "50" => "50 - Critical errors"
                      );

  foreach ($loglevels as $log => $logdesc)
    echo "<option value=".$log.">".$logdesc."</option>\n";
}

function select_pbsettings()
{
  $pbsettings = array ("on", "off");

  foreach ($pbsettings as $pb)
    echo "<option value=".$pb.">".$pb."</option>\n";
}

function select_autodoc_type()
{
  $autodoctypes = array ("html", "htmltable", "xml");

  foreach ($autodoctypes as $autodoctype)
    echo "<option value=".$autodoctype.">".$autodoctype."</option>\n";
}

function generate_b3config_xml()
{
  //collect posted information
  foreach($_POST as $key => $value)
  {
    //create new variables named after our form input names.
    $$key = $value;
  }

  //where is our gamelog?
  if($game_log == 'local')
  {
    $game_log = $game_log_local;
  }
  elseif ($game_log == 'ftp')
    {
      $game_log_ftpadr = explode("//", $game_log_ftpadr);
      $game_log = $game_log_ftpadr[0].'//'.$game_log_ftpusr.':'.$game_log_ftppas.'@'.$game_log_ftpadr[1];
    } 

  //what is our autodoc file location?
  if($autodoc == 'locala')
  {
    $autodoc = $autodoc_local;
  }
  elseif ($autodoc == 'ftpa')
    {
      $autodoc_ftpadr = explode("//", $autodoc_ftpadr);
      $destination = $autodoc_ftpadr[0].'//'.$autodoc_ftpusr.':'.$autodoc_ftppas.'@'.$autodoc_ftpadr[1];
    }

  $database = 'mysql://'.$db_user.':'.$db_pass.'@'.$db_host.'/'.$db_name;

  //This is the main array holding our b3 settings
  $b3_xml_input = array (
                          "b3"        => array (
                                          "parser" => $parser,
                                          "database" => $database,
                                          "bot_name" => $bot_name,
                                          "bot_prefix" => $bot_prefix,
                                          "time_format" => $time_format,
                                          "time_zone" => $time_zone,
                                          "log_level" => $log_level,
                                          "logfile" => $logfile
                                        ),
                          "bfbc2"     => array(
                                          "max_say_line_length" => $max_say_line_length    //bfbc2
                                        ),
                          "server"    => array (
                                          "rcon_password" => $rcon_password,
                                          "port" => $port,
                                          "game_log" => $game_log,                         //no bfbc2
                                          "public_ip" => $public_ip,
                                          "rcon_ip" => $rcon_ip,
                                          "rcon_port" => $rcon_port,                       //bfbc2
                                          "timeout" => $timeout,                           //bfbc2
                                          "punkbuster" => $punkbuster
                                        ),
                          "autodoc"   => array (
                                          "type" => $type, 
                                          "maxlevel" => $maxlevel, 
                                          "destination" => $destination
                                        ),
                          "messages"  => array (
                                          "kicked_by" => $kicked_by,
                                          "kicked" => $kicked,
                                          "banned_by" => $banned_by,
                                          "banned" => $banned,
                                          "temp_banned_by" => $temp_banned_by,
                                          "temp_banned" => $temp_banned,
                                          "unbanned_by" => $unbanned_by,
                                          "unbanned" => $unbanned
                                        ),
                          "plugins"   => array(
                                          "external_dir" => $external_dir
                                        ),
                          "plugins_s" => array(
                                          "censor" => $censor,
                                          "spamcontrol" => $spamcontrol,
                                          "admin" => $admin,
                                          "tk" => $tk,
                                          "stats" => $stats,
                                          "pingwatch" => $pingwatch,
                                          "adv" => $adv,
                                          "status" => $status,
                                          "welcome" => $welcome,
                                          "punkbuster" => $punkbuster,
                                          "xlrstats" => $xlrstats
                                        )
                        );

  //destroy bfbc2 specific variables
  if($parser != 'bfbc2')
  {
    unset(
          $b3_xml_input['bfbc2'],
          $b3_xml_input['server']['rcon_port'],
          $b3_xml_input['server']['timeout']
         );
  }

  //destroy non bfbc2 variables
  if($parser == 'bfbc2')
  {
    unset($b3_xml_input['server']['game_log']);
  }

  //Prepare the XML document
  $doc = new DOMDocument();
  $doc->formatOutput = true;

  $conf = $doc->createElement("configuration");
  $doc->appendChild($conf);

  foreach($b3_xml_input as $key => $settings)
  {
    if($key == 'plugins_s')
    {
      $plugins = $doc->createElement("plugins");
      $conf->appendChild($plugins);

      foreach($settings as $key => $text)
      {
        if($text == 'on')
        {
          $plugin = $doc->createElement("plugin");
          $plugins->appendChild($plugin);

          $plugin_atr = $doc->createAttribute('name');
          $plugin->appendChild($plugin_atr);

          $plugin_atr_text = $doc->createTextNode($key);
          $plugin_atr->appendChild($plugin_atr_text);

          $plugin_atr = $doc->createAttribute('config');
          $plugin->appendChild($plugin_atr);

          $text = ${$key.'_location'};
          $plugin_atr_text = $doc->createTextNode($text);
          $plugin_atr->appendChild($plugin_atr_text);
        }
      }
    }
    else
    {
      $sets = $doc->createElement("settings");
      $conf->appendChild($sets);

      $sets_atr = $doc->createAttribute('name');
      $sets->appendChild($sets_atr);

      $sets_atr_text = $doc->createTextNode($key);
      $sets_atr->appendChild($sets_atr_text);

      foreach($settings as $key => $text)
      {
        $set = $doc->createElement("set");
        $set->appendChild( $doc->createTextNode($text));
        $sets->appendChild($set);

        $set_atr = $doc->createAttribute('name');
        $set->appendChild($set_atr);

        $set_atr_text = $doc->createTextNode($key);
        $set_atr->appendChild($set_atr_text);
      }
    }
  }
  $doc->save('tmp/b3.xml');
  header('Content-disposition: attachment; filename=b3.xml');
  header('Content-type: application/xml');
  readfile('tmp/b3.xml');
  unlink('tmp/b3.xml');
  return;
}

?>