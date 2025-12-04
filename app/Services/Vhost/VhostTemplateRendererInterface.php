<?php
namespace App\Services\Vhost;
interface VhostTemplateRendererInterface
{
    public function renderConfig(array $data): string;
    public function renderHtml(array $data): string;
}
