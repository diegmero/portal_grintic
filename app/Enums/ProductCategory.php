<?php

namespace App\Enums;

enum ProductCategory: string
{
    case HOSTING = 'hosting';
    case SERVERS = 'servers';
    case CDN = 'cdn';
    case ANTIVIRUS = 'antivirus';
    case PLUGINS = 'plugins';
    case EMAIL = 'email';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::HOSTING => 'Hosting',
            self::SERVERS => 'Servidores',
            self::CDN => 'CDN Imágenes',
            self::ANTIVIRUS => 'Antivirus',
            self::PLUGINS => 'Plugins WordPress',
            self::EMAIL => 'Email Profesional',
            self::OTHER => 'Otro',
        };
    }
}
