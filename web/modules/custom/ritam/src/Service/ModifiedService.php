<?php

    namespace Drupal\ritam\Service;
    use Drupal\Core\Database\Connection;

    class ModifiedService {
        /**
         * The db connection
         *
         * @var Connection
         */
        protected $database;

        /**
         * Function which utilizes the @database service
         *
         * @param Connection $database
         */
        public function __construct(Connection $database) {
            $this->database = $database;
        }

        /**
         * Function which prints a welcome message
         *
         * @return void
         */
        public function hello() {
            return 'This is a modified service.';
        }
    }
?>
