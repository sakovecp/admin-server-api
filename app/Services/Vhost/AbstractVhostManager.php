<?php
namespace App\Services\Vhost;

use App\Services\Host\HostManagerInterface;
use App\Services\Servers\ServerManagerInterface;
use Illuminate\Filesystem\Filesystem;

abstract class AbstractVhostManager implements VhostManagerInterface
{
    protected Filesystem $fs;
    protected HostManagerInterface $host;
    protected ServerManagerInterface $server;
    protected VhostTemplateRendererInterface $renderer;
    protected $configDir;
    protected $htmlDir;

    public function __construct(
        string $configDir,
        string $htmlDir,
        ServerManagerInterface $server,
        HostManagerInterface $host,
        Filesystem $fs,
        VhostTemplateRendererInterface $renderer
    ){
        $this->fs = $fs;
        $this->server = $server;
        $this->host = $host;
        $this->configDir = $configDir;
        $this->htmlDir = $htmlDir;
        $this->renderer = $renderer;
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->host->all();
    }

    protected function sanitizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        if (!preg_match('/^[a-z0-9\-.]+$/', $domain)) {
            throw new \InvalidArgumentException('Invalid domain name');
        }
        return $domain;
    }

    protected function createConfig(string $domain, int $port): string
    {
        $this->fs->ensureDirectoryExists($this->configDir);
        $filePath = "{$this->configDir}/{$domain}.conf";
        $content = $this->renderer->renderConfig(['domain' => $domain, 'port' => $port]);

        if ($this->fs->put($filePath, $content)) {
            return $filePath;
        }

        throw new \RuntimeException('Error writing configuration file');
    }

    protected function deleteConfig(string $domain): bool
    {
        $filePath = "{$this->configDir}/{$domain}.conf";
        return $this->fs->exists($filePath) ? $this->fs->delete($filePath) : false;
    }

    protected function createIndexPage(string $domain): bool
    {
        $path = "$this->htmlDir/$domain";
        $this->fs->ensureDirectoryExists($path);
        $html = $this->renderer->renderHtml(['domain' => $domain]);

        if ($this->fs->put("$path/index.html", $html)) {
            return true;
        }

        throw new \RuntimeException('Error writing index.html');
    }

    protected function deleteIndexPage(string $domain): bool
    {
        return $this->fs->deleteDirectory("$this->htmlDir/$domain");
    }
}
