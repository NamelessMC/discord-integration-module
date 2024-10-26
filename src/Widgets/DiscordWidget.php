<?php

namespace NamelessMC\DiscordIntegration\Widgets;

use NamelessMC\DiscordIntegration\DiscordUtils;

/*
 *  Made by Partydragen
 *  Updated by BrightSkyz
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr8
 *
 *  License: MIT
 *
 *  Discord Widget
 */

class DiscordWidget extends \WidgetBase implements \HasWidgetSettings {

    private \Cache $_cache;
    private ?string $_guild_id;

    public function __construct(\Cache $cache, \Smarty $smarty) {
        $this->_module = 'Discord Integration';
        $this->_name = 'Discord';
        $this->_description = 'Display your Discord channel on your site. Make sure you have entered your Discord widget details in the StaffCP -> Integrations -> Discord tab first!';
        $this->_smarty = $smarty;
        $this->_cache = $cache;
        $this->_guild_id = DiscordUtils::getGuildId();
    }

    public function initialise(): void {
        // Generate HTML code for widget
        // If there is no Guild ID set, display error message
        if ($this->_guild_id === null) {
            $this->_content = DiscordUtils::getLanguageTerm('discord_widget_disabled');
            return;
        }

        // First, check to see if the Discord server has the widget enabled.
        $this->_cache->setCache('social_media');
        if ($this->_cache->isCached('discord_widget_check')) {
            $result = $this->_cache->retrieve('discord_widget_check');

        } else {
            $request = \HttpClient::get('https://discord.com/api/guilds/' . urlencode($this->_guild_id) . '/widget.json');
            if ($request->hasError()) {
                $this->_content = DiscordUtils::getLanguageTerm('discord_widget_error', [
                    'error' => $request->getError()
                ]);
                return;
            }

            $result = $request->json();

            // Cache for 60 seconds
            $this->_cache->store('discord_widget_check', $result, 60);
        }

        // Check if the widget is disabled.
        if (!isset($result->channels) || isset($result->code)) {
            // Yes, it is: display message
            $this->_content = DiscordUtils::getLanguageTerm('discord_widget_disabled');

        } else {
            // No, it isn't: display the widget
            // Check cache for theme
            $theme = 'dark';
            if ($this->_cache->isCached('discord_widget_theme')) {
                $theme = $this->_cache->retrieve('discord_widget_theme');
            }

            $this->_content = '<iframe src="https://discord.com/widget?id=' . urlencode($this->_guild_id) . '&theme=' . urlencode($theme) . '" width="100%" height="500" allowtransparency="true" frameborder="0"></iframe><br />';
        }
    }

    public function handleSettingsRequest(
        \Cache $cache,
        \Smarty $smarty,
        \Language $language,
        &$success,
        &$errors
    ): void {
        $cache->setCache('social_media');

        if (\Input::exists()) {
            if (\Token::check()) {
                if (isset($_POST['theme'])) {
                    $cache->store('discord_widget_theme', $_POST['theme']);
                }

                $success = $language->get('admin', 'widget_updated');
            } else {
                $errors = [$language->get('general', 'invalid_token')];
            }
        }

        if ($cache->isCached('discord_widget_theme')) {
            $discord_theme = $cache->retrieve('discord_widget_theme');
        } else {
            $discord_theme = 'dark';
        }

        if (isset($errors) && count($errors)) {
            $smarty->assign([
                'ERRORS' => $errors,
            ]);
        }

        $smarty->assign([
            'DISCORD_THEME' => DiscordUtils::getLanguageTerm('discord_widget_theme'),
            'DISCORD_THEME_VALUE' => $discord_theme,
            'SETTINGS_TEMPLATE' => 'discord_integration/widgets/discord.tpl',
            'DARK' => $language->get('admin', 'dark'),
            'LIGHT' => $language->get('admin', 'light')
        ]);
    }
}
