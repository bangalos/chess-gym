<?php

/**
 * @file
 * Contains \Drupal\chessthink\Controller\ChessThinkController
 */

namespace Drupal\chessthink\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpFoundation\Request;
// Change following https://www.drupal.org/node/2457593
use Drupal\Component\Utility\SafeMarkup;

/**
 * Controller routines for Chess think pages.
 */
class ChessThinkController extends ControllerBase{
  public function purdymethod() {
    $response = new Response();
    $response->headers->set('Expires', 'Sun, 19 Nov 1978 05:00:00 GMT');
    $response->headers->set('Cache-Control', 'must-revalidate');
    $response->headers->set('Content-Type', 'text/html; charset=utf-8');

    $fen = '3r1rk1/1bq2ppp/p1p1pb2/1pPnN1Nn/3P3P/PP1B2P1/2QB1PK1/4R2R b - - 0 1';
    $opponentsmove = 'h7h6';
    $answer = 'd3h7';
    $general_bg = '1... h6 2. Bh7+ Kh8 3. Bg8 g6 4. Bxf7 hxg5 5. Qxg6';
    $posid = 1;

    // Read actual feed from file.
    $file_name = drupal_get_path('module', 'chessthink') . '/purdymethod.html';
    $handle = fopen($file_name, 'r');
    $feed = fread($handle, filesize($file_name));
    fclose($handle);

    $feed = str_replace('SECRETFEN', $fen, $feed);
    $feed = str_replace('SECRETOPPMOVE', $opponentsmove, $feed);
    $feed = str_replace('SECRETMYMOVE', $answer, $feed);
    $feed = str_replace('SECRETGENBG', $general_bg, $feed);
    $feed = str_replace('SECRETPOSID', $posid, $feed);

    $response->setContent($feed);
    return $response;
  }
  public function think() {
    $response = new Response();
    $response->headers->set('Expires', 'Sun, 19 Nov 1978 05:00:00 GMT');
    $response->headers->set('Cache-Control', 'must-revalidate');
    $response->headers->set('Content-Type', 'text/html; charset=utf-8');

    // Read actual feed from file.
    $file_name = drupal_get_path('module', 'chessthink') . '/srib.html';
    $handle = fopen($file_name, 'r');
    $feed = fread($handle, filesize($file_name));
    fclose($handle);

    $response->setContent($feed);
    return $response;
  }
  public function braindump(Request $request) {
    $params = array();
    $content = $request->getContent();
    if (!empty($content)) {
      // 2nd param to get as array
      //$params = json_decode($content, TRUE);
      $params['srib'] = 'success';
      $ply = 1;
      $params['whatigot'] = print_r($content, TRUE);
    }
    $braindump = $content;

    $fields = array('username' => 'defaultuser', 'gamename' => 'defaultgame', 'braindump' => $braindump, 'ply' => $ply);
    db_insert('chessthink')->fields($fields)->execute();

    $response = new JsonResponse($params);
    return $response;
  }
}
