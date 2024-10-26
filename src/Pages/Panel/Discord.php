<?php

namespace NamelessMC\DiscordIntegration\Pages\Panel;

use NamelessMC\DiscordIntegration\DiscordUtils;
use NamelessMC\Framework\Pages\PanelPage;

class Discord extends PanelPage
{
    private \Smarty $smarty;
    private \Language $coreLanguage;
    private \Language $cookiesLanguage;

    public function __construct(
        \Smarty $smarty,
        \Language $coreLanguage
    ) {
        $this->smarty = $smarty;
        $this->coreLanguage = $coreLanguage;
        $this->discordLanguage = \Illuminate\Container\Container::getInstance()->get('discord_integrationLanguage');
    }

    public function pageName(): string {
        return 'discord';
    }

    public function viewFile(): string {
        return 'discord_integration/integrations/discord.tpl';
    }

    public function permission(): string {
        return 'admincp.discord';
    }

    public function parentPage(): string {
        return 'integrations';
    }

    public function render() {
        if (\Input::exists()) {
            $errors = [];
        
            if (\Token::check()) {
                if (isset($_POST['discord_guild_id'])) {
                    $validation = \Validate::check($_POST, [
                        'discord_guild_id' => [
                            \Validate::MIN => 18,
                            \Validate::MAX => 20,
                            \Validate::NUMERIC => true,
                            \Validate::REQUIRED => true,
                        ]
                    ])->messages([
                        'discord_guild_id' => [
                            \Validate::MIN => Discord::getLanguageTerm('discord_id_length', ['min' => 18, 'max' => 20]),
                            \Validate::MAX => Discord::getLanguageTerm('discord_id_length', ['min' => 18, 'max' => 20]),
                            \Validate::NUMERIC => Discord::getLanguageTerm('discord_id_numeric'),
                            \Validate::REQUIRED => Discord::getLanguageTerm('discord_id_required'),
                        ]
                    ]);
        
                    if ($validation->passed()) {
                        \Settings::set('discord', \Input::get('discord_guild_id'));
        
                        $success = Discord::getLanguageTerm('discord_settings_updated');
        
                    } else {
                        $errors = $validation->errors();
                    }
                } else {
                    // Valid token
                    // Either enable or disable Discord integration
                    if ($_POST['enable_discord'] === '1') {
                        if (DiscordUtils::botUrl() == '' || DiscordUtils::botUsername() == '' || Discord::getGuildId() == '') {
                            $errors[] = Discord::getLanguageTerm('discord_bot_must_be_setup', [
                                'linkStart' => '<a href="https://github.com/NamelessMC/Nameless-Link/wiki/Setup" target="_blank">',
                                'linkEnd' => '</a>',
                            ]);
                            \Settings::set('discord_integration', '0');
                        } else {
                            \Settings::set('discord_integration', '1');
                        }
                    } else {
                        \Settings::set('discord_integration', '0');
                    }
                }
        
                if (!count($errors)) {
                    \Session::flash('discord_success', Discord::getLanguageTerm('discord_settings_updated'));
                    \Redirect::to(\URL::build('/panel/discord'));
                }
            } else {
                // Invalid token
                $errors[] = [$this->coreLanguage->get('general', 'invalid_token')];
            }
        }
        
        // Load modules + template
        // Module::loadPage($user, $pages, $cache, $this->smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);
        
        if (\Session::exists('discord_success')) {
            $success = \Session::flash('discord');
        }
        
        if (isset($success)) {
            $this->smarty->assign([
                'SUCCESS' => $success,
                'SUCCESS_TITLE' => $this->coreLanguage->get('general', 'success')
            ]);
        }
        
        if (isset($errors) && count($errors)) {
            $this->smarty->assign([
                'ERRORS' => $errors,
                'ERRORS_TITLE' => $this->coreLanguage->get('general', 'error')
            ]);
        }

        if (\Session::exists('discord_error')) {
            $this->smarty->assign([
                'ERRORS' => [\Session::flash('discord_error')],
                'ERRORS_TITLE' => $this->coreLanguage->get('general', 'error')
            ]);
        }
        
        // TODO: Add a check to see if the bot is online using `/status` endpoint Discord::botRequest('/status');

        $this->smarty->assign([
            // 'PARENT_PAGE' => PARENT_PAGE,
            'DASHBOARD' => $this->coreLanguage->get('admin', 'dashboard'),
            'INTEGRATIONS' => $this->coreLanguage->get('admin', 'integrations'),
            'DISCORD' => Discord::getLanguageTerm('discord'),
            //'PAGE' => PANEL_PAGE,
            'INFO' => $this->coreLanguage->get('general', 'info'),
            'TOKEN' => \Token::get(),
            'SUBMIT' => $this->coreLanguage->get('general', 'submit'),
            'ENABLE_DISCORD_INTEGRATION' => Discord::getLanguageTerm('enable_discord_integration'),
            'DISCORD_ENABLED' => DiscordUtils::isBotSetup(),
            'INVITE_LINK' => Discord::getLanguageTerm('discord_invite_info', [
                'inviteLinkStart' => '<a target="_blank" href="https://namelessmc.com/discord-bot-invite">',
                'inviteLinkEnd' => '</a>',
                'command' => '<code>/configure link</code>',
                'selfHostLinkStart' => '<a target="_blank" href="https://github.com/NamelessMC/Nameless-Link/wiki/Installation-guide">',
                'selfHostLinkEnd' => '</a>',
            ]),
            'GUILD_ID_SET' => (DiscordUtils::getGuildId() != ''),
            'BOT_URL_SET' => (DiscordUtils::botUrl() != ''),
            'BOT_USERNAME_SET' => (DiscordUtils::botUsername() != ''),
            'REQUIREMENTS' => rtrim($this->coreLanguage->get('installer', 'requirements'), ':'),
            'BOT_SETUP' => Discord::getLanguageTerm('discord_bot_setup'),
            'DISCORD_GUILD_ID' => Discord::getLanguageTerm('discord_guild_id'),
            'DISCORD_GUILD_ID_VALUE' => DiscordUtils::getGuildId(),
            'ID_INFO' => Discord::getLanguageTerm('discord_id_help', [
                'linkStart' => '<a href="https://support.discord.com/hc/en-us/articles/206346498" target="_blank">',
                'linkEnd' => '</a>',
            ]),
        ]);
        
        // $template->onPageLoad();
        
        // require(ROOT_PATH . '/core/templates/panel_navbar.php');
        
        // Display template
        // $template->displayTemplate('integrations/discord/discord.tpl', $this->smarty);        
    }
}