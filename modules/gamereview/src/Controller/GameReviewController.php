<?php

/**
 * @file
 * Contains \Drupal\gamereview\Controller\GameReviewController
 */

namespace Drupal\gamereview\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpFoundation\Request;
// Change following https://www.drupal.org/node/2457593
use Drupal\Component\Utility\SafeMarkup;

/**
 * Controller routines for Game Review pages.
 */
class GameReviewController extends ControllerBase{
  public function gamereview($gamedir="rudolf_spielmann", $gamefile="game_9") {
    $response = new Response();
    $response->headers->set('Expires', 'Sun, 19 Nov 1978 05:00:00 GMT');
    $response->headers->set('Cache-Control', 'must-revalidate');
    $response->headers->set('Content-Type', 'text/html; charset=utf-8');

    //TODO: Assume that the pgn filename is magically available in the following variable:
    $current_uri = \Drupal::request()->getRequestUri();
    if (preg_match("/^(.+?)amereview\/(.+?)\/(.+?)_(.+?)$/", $current_uri, $match)) {
      $gamedir = $match[2];
      $gamefile = "$match[3]_$match[4]";
      $nextgamefile = "$match[3]_" . ($match[4] + 1);
      $defaultgamefile = "$match[3]_1";
      $sitebaseuri = "$match[1]amereview/$gamedir";
    }
    $pgn_file_name = "rudolf_spielmann/game_9";
    if ($gamedir != "" && $gamefile != "") {
      $pgn_file_name = "$gamedir/$gamefile";
    }
    $pgn_file_name = DRUPAL_ROOT . "/sites/default/files/chessgym/gamereview/" . $pgn_file_name;
    $nextpgn_file_name = DRUPAL_ROOT . "/sites/default/files/chessgym/gamereview/$gamedir/$nextgamefile";
    $tmphandle = fopen($nextpgn_file_name, 'r');
    $nextgameurl = "$sitebaseuri/$defaultgamefile";
    if ($tmphandle) {
      $nextgameurl = "$sitebaseuri/$nextgamefile";
    }
    // Read actual feed from file.
    $handle = fopen($pgn_file_name, 'r');
    $feed = fread($handle, filesize($pgn_file_name));
    fclose($handle);
    $pgnlines = explode("\n", $feed);
    $pgn = "";
    $general_bg = "";
    $threshold = 11;
    foreach ($pgnlines as $line) {
      $line = rtrim($line);
      if (!preg_match("/\[/", $line) && $line != "") {
        if ($pgn == "") {$pgn = $line;} else { $pgn = "$pgn $line";}
      } else {
        $general_bg = "$general_bg<br/>$line";
      }
      if (preg_match("/Black \"Rudolf Spielmann\"/", $line)) {
        $threshold = 12;
      }
    }
    $feed = ""; //Reset it.


    $fen = 'r1k4r/p2nb1p1/2b4p/1p1n1p2/2PP4/3Q1NB1/1P3PPP/R5K1 b - c3 0 19';

    // Read actual feed from file.
    $file_name = drupal_get_path('module', 'gamereview') . '/gamereview_core.html';
    $handle = fopen($file_name, 'r');
    $feed = fread($handle, filesize($file_name));
    fclose($handle);

    $feed = str_replace('SECRETFEN', $fen, $feed);
    $feed = str_replace('SECRETGENBG', $general_bg, $feed);
    $feed = str_replace('SECRETPGN', $pgn, $feed);
    $feed = str_replace('SECRETTHRESHOLD', $threshold, $feed);
    $feed = str_replace('SECRETNEXT', $nextgameurl, $feed);

    $response->setContent($feed);
    return $response;
  }
}
