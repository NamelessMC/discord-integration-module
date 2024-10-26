<?php

namespace NamelessMC\DiscordIntegration;

class DebugInfoProvider implements \NamelessMC\Framework\Debugging\DebugInfoProvider
{
    public function provide(): array
    {
        return [
            'guild_id' => DiscordUtils::getGuildId(),
            'roles' => DiscordUtils::getRoles(),
            'bot_setup' => DiscordUtils::isBotSetup(),
            'bot_url' => DiscordUtils::botUrl(),
            'bot_username' => DiscordUtils::botUsername()
        ];
    }
}