<?php
    /**
     * @file
     * Contains \Drupal\gamereview\Controller\GameReviewController
     */
    namespace Drupal\gamereview\Controller;
    use Drupal\Core\Url;
    use Drupal\user\Entity;
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
        
        function gamereview($gamedir="rudolf_spielmann", $gamefile="game_9", $class, $corehtml) {
            $response = new Response();
            $response->headers->set('Expires', 'Sun, 19 Nov 1978 05:00:00 GMT');
            $response->headers->set('Cache-Control', 'must-revalidate');
            $response->headers->set('Content-Type', 'text/html; charset=utf-8');
            //TODO: Assume that the pgn filename is magically available in the following variable:
            $current_uri = \Drupal::request()->getRequestUri();
            if (preg_match("/^(.+?)amereview\/$class\/(.+?)\/(.+?)_(.+?)$/", $current_uri, $match)) {
                $gamedir = $match[2];
                $gamefile = "$match[3]_$match[4]";
                $nextgamefile = "$match[3]_" . ($match[4] + 1);
                $defaultgamefile = "$match[3]_1";
                $sitebaseuri = "$match[1]amereview/$class/$gamedir";
                $gamescoreurl = "$match[1]amereview/score/$gamedir/$gamefile/";
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
                    if ($pgn == "") {$pgn = $line;} else {$pgn = "$pgn $line";}
                } else {
                    $general_bg = "$general_bg<br/>$line";
                }
                if (preg_match("/Black \"Rudolf Spielmann\"/", $line)) {
                    $threshold = 12;
                }
                
                if (preg_match("/Premove \"(.+?)\"/", $line, $match)) {
                    $threshold = intval($match[1]);
                }
                if (preg_match("/POV \"(.+?)\"/", $line, $match)) {
                    $pov = $match[1];
                }
            }
            
            $feed = "";
            // Read actual feed from file.
            $file_name = drupal_get_path('module', 'gamereview') . '/' . $corehtml;
            $handle = fopen($file_name, 'r');
            $feed = fread($handle, filesize($file_name));
            fclose($handle);
            $feed = str_replace('SECRETFEN', $fen, $feed);
            $feed = str_replace('SECRETGENBG', $general_bg, $feed);
            $feed = str_replace('SECRETPGN', $pgn, $feed);
            $feed = str_replace('SECRETTHRESHOLD', $threshold, $feed);
            $feed = str_replace('SECRETPOV', $pov, $feed);
            $feed = str_replace('SECRETNEXT', $nextgameurl, $feed);
            $feed = str_replace('SECRETSCOREURL', $gamescoreurl, $feed);
            $response->setContent($feed);
            return $response;
        }
        
        public function web($gamedir, $gamefile) {
            return $this->gamereview($gamedir, $gamefile, "web", "gamereview_core_web_new.html");
        }
        
        public function mobile($gamedir, $gamefile) {
            return $this->gamereview($gamedir, $gamefile, "mobile", "gamereview_core_mobile_new.html");
        }

        public function score($gamedir, $gamefile, $gamescore) {
            $response = new Response();
            $response->headers->set('Expires', 'Sun, 19 Nov 1978 05:00:00 GMT');
            $response->headers->set('Cache-Control', 'must-revalidate');
            $response->headers->set('Content-Type', 'text/html; charset=utf-8');
            $response_text = "Score recorded!";
            $user = \Drupal::service('current_user');
            $uid = $user->id();
            $formatted_name = $user->getDisplayName();
            if ($formatted_name != "Anonymous") {
                $fields = [
                    'username' => $formatted_name,
                    'coursename' => $gamedir,
                    'gameid' => $gamefile,
                    'score' => $gamescore,
                    'timestamp' => '2018:02:14 12:00:00'
                ];
                db_insert('gamereview_progress') ->fields($fields) ->execute();
            }
            $response->setContent($response_text);
            return $response;
        }
    }
