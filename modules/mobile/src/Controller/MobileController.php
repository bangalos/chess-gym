<?php

namespace Drupal\mobile\Controller;

class MobileController {
    public function mobile() {
        return array (
            '#title' => 'Mobile App',
            '#markup' => 'This is the mobile app page'
                    );
    }
}
