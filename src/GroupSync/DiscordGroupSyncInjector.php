<?php

namespace NamelessMC\DiscordIntegration\GroupSync;

use NamelessMC\DiscordIntegration\DiscordUtils;
use RuntimeException;
/**
 * Discord group sync injector implementation.
 *
 * @package Modules\DiscordIntegration
 * @author Aberdeener
 * @version 2.0.3
 * @license MIT
 */
class DiscordGroupSyncInjector implements \GroupSyncInjector, \BatchableGroupSyncInjector {

    public function getModule(): string {
        return 'Discord Integration';
    }

    public function getName(): string {
        return 'Discord role';
    }

    public function getColumnName(): string {
        return 'discord_role_id';
    }

    public function getColumnType(): string {
        return 'BIGINT';
    }

    public function shouldEnable(): bool {
        return DiscordUtils::isBotSetup();
    }

    public function getNotEnabledMessage(\Language $language): string {
        return DiscordUtils::getLanguageTerm('discord_integration_not_setup');
    }

    public function getSelectionOptions(): array {
        $roles = [];

        foreach (DiscordUtils::getRoles() as $role) {
            $roles[] = [
                'id' => $role['id'],
                'name' => \Output::getClean($role['name']),
            ];
        }

        return $roles;
    }

    public function getValidationRules(): array {
        return [
            \Validate::MIN => 18,
            \Validate::MAX => 20,
            \Validate::NUMERIC => true
        ];
    }

    public function getValidationMessages(\Language $language): array {
        return [
            \Validate::MIN => DiscordUtils::getLanguageTerm('discord_role_id_length', ['min' => 18, 'max' => 20]),
            \Validate::MAX => DiscordUtils::getLanguageTerm('discord_role_id_length', ['min' => 18, 'max' => 20]),
            \Validate::NUMERIC => DiscordUtils::getLanguageTerm('discord_role_id_numeric'),
        ];
    }

    public function addGroup(\User $user, $group_id): bool {
        throw new RuntimeException('Batchable injector should not have this called');
    }

    public function removeGroup(\User $user, $group_id): bool {
        throw new RuntimeException('Batchable injector should not have this called');
    }

    public function batchAddGroups(\User $user, array $group_ids) {
        return DiscordUtils::updateDiscordRoles($user, $group_ids, []);
    }

    public function batchRemoveGroups(\User $user, array $group_ids) {
        return DiscordUtils::updateDiscordRoles($user, [], $group_ids);
    }
}
