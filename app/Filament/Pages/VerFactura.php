<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Factura;

class VerFactura extends Page
{
    protected static string $panel = 'admin';
    protected static string $view = 'filament.pages.ver-factura';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static bool $shouldRegisterNavigation = false;
    public ?Factura $factura = null;

    public function mount(): void
    {
        $id = request()->route('record');
        $this->factura = Factura::with(['productos', 'empresa', 'user'])->findOrFail($id);
    }
    //aca funciona
    public static function getRoutePath(): string
    {
        return 'ver-factura/{record}';
    }
}
