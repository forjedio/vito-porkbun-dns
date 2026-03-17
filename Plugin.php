<?php

namespace App\Vito\Plugins\Forjedio\VitoPorkbunDns;

use App\DTOs\DynamicField;
use App\DTOs\DynamicForm;
use App\Plugins\AbstractPlugin;
use App\Plugins\RegisterDNSProvider;

class Plugin extends AbstractPlugin
{
    protected string $name = 'Porkbun DNS';

    protected string $description = 'Porkbun DNS provider integration';

    public function boot(): void
    {
        RegisterDNSProvider::make(Porkbun::id())
            ->label('Porkbun')
            ->handler(Porkbun::class)
            ->form(
                DynamicForm::make([
                    DynamicField::make('api_key')
                        ->passwordWithToggle()
                        ->label('API Key')
                        ->description('Generate an API key at porkbun.com/account/api'),
                    DynamicField::make('secret_api_key')
                        ->passwordWithToggle()
                        ->label('Secret API Key')
                        ->description('The secret key paired with your API key'),
                    DynamicField::make('domain_filter')
                        ->text()
                        ->label('Domain Filter')
                        ->placeholder('example.com, mysite.org')
                        ->description('Optional comma-separated list of domains to show. Porkbun\'s API does not exclude domains without API access enabled, so you can filter them here instead. Leave empty to show all.'),
                ])
            )
            ->editForm(
                DynamicForm::make([
                    DynamicField::make('api_key')
                        ->passwordWithToggle()
                        ->label('API Key')
                        ->description('Leave empty to keep the current key'),
                    DynamicField::make('secret_api_key')
                        ->passwordWithToggle()
                        ->label('Secret API Key')
                        ->description('Leave empty to keep the current key'),
                    DynamicField::make('domain_filter')
                        ->text()
                        ->label('Domain Filter')
                        ->placeholder('example.com, mysite.org')
                        ->description('Optional comma-separated list of domains to show. Leave empty to show all.'),
                ])
            )
            ->proxyTypes(['A'])
            ->supportsCreatedAt(false)
            ->register();
    }
}
