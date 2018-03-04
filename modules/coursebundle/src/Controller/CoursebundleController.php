<?php
    /**
     * @file
     * Contains \Drupal\coursebundle\Controller\CoursebundleController
     */
    namespace Drupal\coursebundle\Controller;
    use Drupal\Core\Url;
    use Drupal\Core\Database\Database;
    use Drupal\Core\Controller\ControllerBase;
    use \Symfony\Component\HttpFoundation\Response;
    use \Symfony\Component\HttpFoundation\JsonResponse;
    use \Symfony\Component\HttpFoundation\Request;
    // Change following https://www.drupal.org/node/2457593
    use Drupal\Component\Utility\SafeMarkup;
    /**
     * Controller routines for Coursebundle pages.
     */
    class CoursebundleController extends ControllerBase{
        
        function coursebundle($cb = 'srib_master') {

            $current_uri = \Drupal::request()->getRequestUri();
            $usercourse_uri = str_replace("coursebundle/$cb", 'usercourse', $current_uri);

            $coursebundle_file_name = DRUPAL_ROOT . "/sites/default/files/chessgym/coursebundle/$cb/overview.txt";
            // Read actual feed from file.
            $handle = fopen($coursebundle_file_name, 'r');
            $feed = fread($handle, filesize($coursebundle_file_name));
            fclose($handle);
            $coursebundle_lines = explode("\n", $feed);
            $cb_title = ""; $cb_desc = "";
            foreach ($coursebundle_lines as $line) {
                $key_value = explode ("\t", $line);
                if ($key_value[0] == 'Title') {$cb_title = $key_value[1];}
                if ($key_value[0] == 'Description') {$cb_desc = $key_value[1];}
                if ($key_value[0] == 'Content') {$course_list = explode (",", $key_value[1]);}
            }

            $course_div = '<h2>' . $cb_title . '</h2>';
            $course_div .= '<p>' . $cb_desc . '</p>';
            foreach ($course_list as $course) {
                $course_file_name = DRUPAL_ROOT . "/sites/default/files/chessgym/gamereview/$course/overview.txt";
                // Read actual feed from file.
                $handle = fopen($course_file_name, 'r');
                $feed = fread($handle, filesize($course_file_name));
                fclose($handle);
                $course_lines = explode("\n", $feed);
                $course_title = ""; $course_desc = "";
                foreach ($course_lines as $line) {
                    $key_value = explode ("\t", $line);
                    if ($key_value[0] == 'Title') {$course_title = $key_value[1];}
                    if ($key_value[0] == 'Description') {$course_desc = $key_value[1];}
                }
                $course_div .= '<h3>' . $course_title . '</h3>';
                $course_div .= '<p>' . $course_desc . '</p>';
                $course_div .= '<h3><a href="' . $usercourse_uri . '/web/' . $course . '">' . 'View Details' . '</a></h3>';
            }

            $element = array(
                '#markup' => $course_div,
            );
            return $element;
        }
    }
