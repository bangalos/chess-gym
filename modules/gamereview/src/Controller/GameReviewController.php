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
  public function gamereview() {
    $response = new Response();
    $response->headers->set('Expires', 'Sun, 19 Nov 1978 05:00:00 GMT');
    $response->headers->set('Cache-Control', 'must-revalidate');
    $response->headers->set('Content-Type', 'text/html; charset=utf-8');

    $fen = 'r1k4r/p2nb1p1/2b4p/1p1n1p2/2PP4/3Q1NB1/1P3PPP/R5K1 b - c3 0 19';
    $general_bg = 'Rudolf Spielmann vs Baldur Hoenlinger 1929. Caro Kann Main Line. <a href="http://www.chessgames.com/perl/chessgame?gid=1130925">Chess Game</a>';
    $pgn = '1.e4 e5 2.Nf3 Nc6 3.Bc4 Bc5 4.b4 Bxb4 5.c3 Ba5 6.d4 exd4 7.O-O d3 8.Qb3 Qf6 9.e5 Qg6 10.Re1 Nge7 11.Ba3 b5 12.Qxb5 Rb8 13.Qa4 Bb6 14.Nbd2 Bb7 15.Ne4 Qf5 16.Bxd3 Qh5 17.Nf6+ gxf6 18.exf6 Rg8 19.Rad1 Qxf3 20.Rxe7+ Nxe7 21.Qxd7+ Kxd7 22.Bf5+ Ke8 23.Bd7+ Kf8 24.Bxe7# 1-0';

    // Read actual feed from file.
    $file_name = drupal_get_path('module', 'gamereview') . '/gamereview_core.html';
    $handle = fopen($file_name, 'r');
    $feed = fread($handle, filesize($file_name));
    fclose($handle);

    $feed = str_replace('SECRETFEN', $fen, $feed);
    $feed = str_replace('SECRETGENBG', $general_bg, $feed);
    $feed = str_replace('SECRETPGN', $pgn, $feed);

    $response->setContent($feed);
    return $response;
  }
}
