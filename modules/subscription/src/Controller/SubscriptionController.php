<?php
    /**
     * @file
     * Contains \Drupal\subscription\Controller\SubscriptionController
     */
    namespace Drupal\subscription\Controller;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Drupal\Core\Url;
    use Drupal\Core\Database\Database;
    use Drupal\Core\Controller\ControllerBase;
    use \Symfony\Component\HttpFoundation\Response;
    use \Symfony\Component\HttpFoundation\JsonResponse;
    use \Symfony\Component\HttpFoundation\Request;
    // Change following https://www.drupal.org/node/2457593
    use Drupal\Component\Utility\SafeMarkup;
    /**
     * Controller routines for Subscription pages.
     */
    class SubscriptionController extends ControllerBase{
        
        function sub() {

            $user = \Drupal::service('current_user');
            $uid = $user->id();
            $subscription_list = "";
            $formatted_name = $user->getDisplayName();

            $current_uri = \Drupal::request()->getRequestUri();
            if (preg_match("/^(.+?)\/sub$/", $current_uri, $match)) {
                if ($formatted_name == "Anonymous") {
                    $defaulturi = "$match[1]/default";
                    return new RedirectResponse($defaulturi);
                }
            }

            $subscription_file_name = DRUPAL_ROOT . "/sites/default/files/chessgym/subscription/subscription.txt";
            // Read actual feed from file.
            $handle = fopen($subscription_file_name, 'r');
            $feed = fread($handle, filesize($subscription_file_name));
            fclose($handle);
            $subscription_lines = explode("\n", $feed);

            if ($formatted_name != "Anonymous") {
                foreach ($subscription_lines as $user_modules_tuple) {
                    $user_modules_array = explode ("\t", $user_modules_tuple);
                    if ($user_modules_array[0] == $formatted_name) {
                        $subscription_list = explode (",", $user_modules_array[1]);
                    }
                }
            }

            $subscription_div = '<div id="subscription_list">';
            $subscription_div .= '<h2>Your Library</h2>';

            foreach ($subscription_list as $coursebundle) {
                $coursebundle_file_name = DRUPAL_ROOT . "/sites/default/files/chessgym/coursebundle/$coursebundle/overview.txt";
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
                }
                $subscription_div .= '<h3>' . $cb_title . '</h3>';
                $subscription_div .= '<p>' . $cb_desc . '</p>';
                $subscription_div .= '<h3><a href="coursebundle/' . $coursebundle . '">' . 'View Course' . '</a></h3>';
            }

            $element = array(
                '#markup' => $subscription_div,
            );
            return $element;
        }
    }
