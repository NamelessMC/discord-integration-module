<?php

use NamelessMC\Framework\Extend;

return [
    (new Extend\Language(__DIR__ . '/language')),
    (new Extend\PanelPages)
        ->registerInDropdown('integrations', '/discord', 'discord', 'discord_integration/discord', \NamelessMC\DiscordIntegration\Pages\Panel\Discord::class, 'admincp.discord', 'fab fa-discord'),
    (new Extend\Permissions)
        ->register([
            'staffcp' => [
                'admincp.discord' => 'discord_integration/discord',
            ],
        ]),
    (new Extend\DebugInfo)
        ->provide(\NamelessMC\DiscordIntegration\DebugInfoProvider::class),
    (new Extend\Events)
        ->register(\NamelessMC\DiscordIntegration\Events\DiscordWebhookFormatterEvent::class),
    (new Extend\Widgets)
        ->register(\NamelessMC\DiscordIntegration\Widgets\DiscordWidget::class),
    (new Extend\GroupSync)
        ->register(\NamelessMC\DiscordIntegration\GroupSync\DiscordGroupSyncInjector::class),
    (new Extend\Integrations)
        ->register(\NamelessMC\DiscordIntegration\Integration\DiscordIntegration::class),
    (new Extend\Endpoints)
        ->register(\NamelessMC\DiscordIntegration\Api\SetDiscordRolesEndpoint::class)
        ->register(\NamelessMC\DiscordIntegration\Api\SubmitDiscordRoleListEndpoint::class)
        ->register(\NamelessMC\DiscordIntegration\Api\SyncDiscordRolesEndpoint::class)
        ->register(\NamelessMC\DiscordIntegration\Api\UpdateDiscordBotSettingsEndpoint::class)
        ->register(\NamelessMC\DiscordIntegration\Api\UpdateDiscordUsernames::class),
];
