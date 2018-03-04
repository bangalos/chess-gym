<?php
    /**
     * @file
     * Contains \Drupal\usercourse\Controller\UserCourseController
     */
    namespace Drupal\usercourse\Controller;
    use Drupal\Core\Url;
    use Drupal\Core\Database\Database;
    use Drupal\Core\Controller\ControllerBase;
    use \Symfony\Component\HttpFoundation\Response;
    use \Symfony\Component\HttpFoundation\JsonResponse;
    use \Symfony\Component\HttpFoundation\Request;
    // Change following https://www.drupal.org/node/2457593
    use Drupal\Component\Utility\SafeMarkup;
    /**
     * Controller routines for User Course pages.
     */
    class UserCourseController extends ControllerBase{
        
        public function web($gamedir) {
            return $this->usercourse($gamedir, "web", "usercourse_core_web.html");
        }
        
        public function mobile($gamedir) {
            return $this->usercourse($gamedir, "mobile", "usercourse_core_mobile.html");
        }
        
        function usercourse($gamedir="rudolf_spielmann", $class, $corehtml) {
            $response = new Response();
            $response->headers->set('Expires', 'Sun, 19 Nov 1978 05:00:00 GMT');
            $response->headers->set('Cache-Control', 'must-revalidate');
            $response->headers->set('Content-Type', 'text/html; charset=utf-8');
            //TODO: Assume that the pgn filename is magically available in the following variable:
            $current_uri = \Drupal::request()->getRequestUri();
            if (preg_match("/^(.+?)sercourse\/$class\/(.+?)$/", $current_uri, $match)) {
                $gamedir = $match[2];
                $sitebaseuri = "$match[1]sercourse/$class/$gamedir";
                $gamereviewuri = str_replace('usercourse', 'gamereview', $sitebaseuri);
            }
            $overview_file_name = "rudolf_spielmann/overview.txt";
            if ($gamedir != "") {
                $overview_file_name = "$gamedir/overview.txt";
            }
            $overview_file_name = DRUPAL_ROOT . "/sites/default/files/chessgym/gamereview/" . $overview_file_name;

            // Read actual feed from file.
            $handle = fopen($overview_file_name, 'r');
            $feed = fread($handle, filesize($overview_file_name));
            fclose($handle);
            $overview_lines = explode("\n", $feed);
            $course_title = "";
            $course_description = "";
            $game_table = array();

            foreach ($overview_lines as $line) {
                $line = rtrim($line);
                if (preg_match("/^Title\t(.+?)$/", $line, $match)) {
                    $course_title = $match[1];
                } else if (preg_match("/^Description\t(.+?)$/", $line, $match)) {
                    $course_description = $match[1];
                } else {
                    if (preg_match("/^game_(.+?)\t(.+?)$/", $line, $match)) {
                        $game_table[$match[1]] = $match[2];
                    }
                } 
            }

            $user = \Drupal::service('current_user');
            $uid = $user->id();
            $formatted_name = $user->getDisplayName();
            if ($formatted_name != "Anonymous") {
                // Retrieves a \Drupal\Core\Database\Connection which is a PDO instance
                $connection = Database::getConnection();

                // Retrieves a PDOStatement object
                // http://php.net/manual/en/pdo.prepare.php
                $sth = $connection->select('gamereview_progress', 'x')
                                  ->fields('x', array('gameid'))
                                  ->condition('x.username', $formatted_name, '=')
                                  ->condition('x.coursename', $gamedir, '=')
                                  ;

                // Execute the statement
                $executed = $sth->execute();

                // Get all the results
                $results = $executed->fetchAll(\PDO::FETCH_OBJ);

                // Iterate results
                foreach ($results as $row) {
                    $progress[$row->gameid] = 1;
                }
            }

            $course_div = '<div id="course_progress">';
            $course_div .= '<h2>' . $course_title . '</h2>';
            $course_div .= '<p>' . $course_description . '</p>';
            $course_table = '<table id="course_table"><tr><td>Game ID</td><td>Game Description</td><td>Progress</td></tr>';
            foreach($game_table as $gameid => $gamedesc) {
                $game_progress_status = " - ";
                if ($progress["game_$gameid"]) {$game_progress_status = "Done";}
                $course_table .= "<tr><td><a href='$gamereviewuri/game_$gameid'>$gameid</a></td><td>$gamedesc &nbsp;</td><td>$game_progress_status</td></tr>";
            }
            $course_table .= "</table>";
            $course_div .= $course_table;
            $feed = "";
            // Read actual feed from file.
            $file_name = drupal_get_path('module', 'usercourse') . '/' . $corehtml;
            $handle = fopen($file_name, 'r');
            $feed = fread($handle, filesize($file_name));
            fclose($handle);

            $feed = str_replace('SECRETTITLE', $course_title, $feed);
            $feed = str_replace('SECRETDESC', $course_description, $feed);
            $feed = str_replace('SECRETTABLE', $course_table, $feed);

            $response->setContent($feed);

            $element = array(
                '#markup' => $course_div,
            );
            return $element;

            return $response;
        }
    }
