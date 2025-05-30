<?php

namespace App\Filament\Public\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class Links extends Widget
{
    protected static string $view = 'filament.public.widgets.links';
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        $assos = json_decode(Auth::user()->assos, true);

        $authorized = false;
        if (is_array($assos)) {
            foreach ($assos as $asso) {
                if (
                    (isset($asso['login'], $asso['role']) && $asso['login'] === 'simde' && $asso['role'] === 'team_payutc') ||
                    (isset($asso['login']) && $asso['login'] === 'bde')
                ) {
                    $authorized = true;
                    break;
                }
            }
        }

        return $authorized;
    }
}