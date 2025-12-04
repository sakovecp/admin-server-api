<?php
namespace App\Services\Vhost;
class BladeVhostTemplateRenderer implements VhostTemplateRendererInterface
{
    protected string $templateConfig;
    protected string $templateHtml;

    public function __construct(string $templateConfig, string $templateHtml) {
        $this->templateConfig = $templateConfig;
        $this->templateHtml = $templateHtml;
    }

    public function renderConfig(array $data): string
    {
        return view($this->templateConfig, $data)->render();
    }


    public function renderHtml(array $data): string
    {
        return view($this->templateHtml, $data)->render();
    }
}
