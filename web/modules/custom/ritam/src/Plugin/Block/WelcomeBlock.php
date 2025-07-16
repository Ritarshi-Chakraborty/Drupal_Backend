<?php

    namespace Drupal\ritam\Plugin\Block;
    use Drupal\Core\Block\BlockBase;
    use Drupal\Core\Block\BlockPluginInterface;
    use Drupal\Core\Session\AccountInterface;

    /**
     * Provides a 'Welcome role' Block
     * 
     * @Block(
     *  id = "welcome_role_block",
     *  admin_label = @Translation("Welcome Role Block")
     * )
     */
    class WelcomeBlock extends BlockBase {
        /**
         * {@inheritDoc}
         */
        public function build() {
            $current_user = \Drupal::currentUser();
            $roles = $current_user->getRoles();
            $user_role = ucfirst($roles[1]);
            return [
                '#markup' => $this->t('Hello @role', ['@role' => $user_role])
            ];
        }
    }
?>
