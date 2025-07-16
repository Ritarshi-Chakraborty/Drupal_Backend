<?php

    namespace Drupal\ritam\Access;
    use Drupal\Core\Access\AccessResult;
    use Drupal\Core\Session\AccountInterface;
    
    class MyAccessCheck {
        /**
         * Function which checks access based on permission
         *
         * @param AccountInterface $account
         * @return void
         */
        public function access(AccountInterface $account) {
            return AccessResult::allowedIf($account->hasPermission('access info'));
        }

        /**
         * Function which checks access based on permission & User id
         *
         * @param AccountInterface $account
         * @return void
         */
        public function access2(AccountInterface $account) {
            // Example: Only users with 'access content' and UID > 1
            if ($account->hasPermission('access info') && $account->id() > 1) {
                return AccessResult::allowed();
            }
            return AccessResult::forbidden();
        }
    
    }
?>
