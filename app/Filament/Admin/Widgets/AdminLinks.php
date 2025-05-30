<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AdminLinks extends Widget
{
    protected static string $view = 'filament.admin.widgets.links';
    protected static bool $isLazy = false;
}