<?php

namespace NamelessMC\DiscordIntegration\Api;

use NamelessMC\DiscordIntegration\DiscordUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * @param string $roles An array of Discord Roles with their name and ID
 *
 * @return string JSON Array
 */
class SubmitDiscordRoleListEndpoint extends \KeyAuthEndpoint {

    public function __construct() {
        $this->_route = 'discord/submit-role-list';
        $this->_module = 'Discord Integration';
        $this->_description = 'Update NamelessMC\'s list of your Discord guild\'s roles.';
        $this->_method = 'POST';
    }

    public function execute(\Nameless2API $api): void {
        $roles = [];

        if ($_POST['roles'] != null) {
            $roles = $_POST['roles'];
        }

        try {
            DiscordUtils::saveRoles($roles);
        } catch (Exception $e) {
            $api->throwError(DiscordApiErrors::ERROR_UNABLE_TO_UPDATE_DISCORD_ROLES, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $api->returnArray(['message' => DiscordUtils::getLanguageTerm('discord_settings_updated')]);
    }
}
