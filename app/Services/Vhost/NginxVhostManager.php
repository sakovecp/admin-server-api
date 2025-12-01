<?php
namespace App\Services\Vhost;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class NginxVhostManager implements VhostManagerInterface
{
    protected Filesystem $fs;
    protected array $cfg;
    protected string $vhostsPath;
    protected string $confPath;
    protected string $nginxContainer; // to trigger reload

    public function __construct(array $cfg = [])
    {
        $this->fs = new Filesystem();
        $this->cfg = $cfg;
        $this->vhostsPath = $cfg['vhosts_path'] ?? base_path('storage/vhosts');
        $this->confPath = $cfg['nginx_conf_path'] ?? base_path('storage/nginx/conf.d');
        $this->nginxContainer = $cfg['nginx_container'] ?? 'nginx';

        if (! $this->fs->exists($this->vhostsPath)) {
            $this->fs->makeDirectory($this->vhostsPath, 0755, true);
        }
        if (! $this->fs->exists($this->confPath)) {
            $this->fs->makeDirectory($this->confPath, 0755, true);
        }
    }

    /**
     * @param string $templatePath
     * @param array $data
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function render(string $templatePath, array $data = []): string
    {
        $contents = $this->fs->get($templatePath);
        foreach ($data as $k => $v) {
            $contents = str_replace(['{{'.$k.'}}','{!!'.$k.'!!}'], $v, $contents);
        }
        return $contents;
    }

    /**
     * @return void
     */
    protected function reloadNginx(): void
    {
        $proc = new Process(['docker', 'exec', $this->nginxContainer, '/bin/sh', '-c', 'nginx -s reload']);
        $proc->run();
        if (! $proc->isSuccessful()) {
            Log::warning('Nginx reload failed; attempting restart', ['output' => $proc->getErrorOutput()]);
            (new Process(['docker', 'restart', $this->nginxContainer]))->run();
        }
    }

    /**
     * @param int|null $port
     * @return int
     */
    protected function allocatePort(?int $port = null): int
    {
        if ($port && $port > 0) return $port;

        $base = (int)($this->cfg['port_base'] ?? 8001);

        $i = 0;
        while (true) {
            $candidate = $base + $i;
            $files = $this->fs->files($this->confPath);
            $used = array_filter($files, function($f) use ($candidate) {
                $c = (string) $f;
                return str_contains(file_get_contents($c), "listen $candidate");
            });
            if (empty($used)) return $candidate;
            $i++;
            if ($i > 10000) throw new \RuntimeException('No free port found');
        }
    }

    /**
     * @param string $domain
     * @param int|null $port
     * @return array
     */
    public function create(string $domain, ?int $port = null): array
    {
        $domain = trim($domain);
        if (!preg_match('/^[a-zA-Z0-9\\.-]+$/', $domain)) {
            throw new \InvalidArgumentException('Invalid domain name');
        }

        $port = $this->allocatePort($port);
        $siteDir = $this->vhostsPath . DIRECTORY_SEPARATOR . $domain;
        if (! $this->fs->exists($siteDir)) {
            $this->fs->makeDirectory($siteDir, 0755, true);
        }

        $indexTpl = $this->cfg['templates']['vhost_index'] ?? resource_path('templates/vhost_index.blade.php');
        $indexHtml = $this->render($indexTpl, ['domain' => $domain]);
        $this->fs->put($siteDir . DIRECTORY_SEPARATOR . 'index.html', $indexHtml);

        $nginxTpl = $this->cfg['templates']['nginx_conf'] ?? resource_path('templates/nginx_vhost.conf.blade.php');
        $conf = $this->render($nginxTpl, ['domain' => $domain, 'port' => $port, 'root' => "/usr/share/nginx/vhosts/{$domain}"]);
        $confPath = $this->confPath . DIRECTORY_SEPARATOR . $domain . '.conf';
        $this->fs->put($confPath, $conf);

        try {
            $this->reloadNginx();
        } catch (\Throwable $e) {
            Log::error('Failed to reload nginx after creating vhost', ['exception' => $e->getMessage()]);
        }

        return ['domain' => $domain, 'port' => $port, 'path' => $siteDir];
    }

    /**
     * @param string $domain
     * @return bool
     */
    public function delete(string $domain): bool
    {
        $siteDir = $this->vhostsPath . DIRECTORY_SEPARATOR . $domain;
        if ($this->fs->exists($siteDir)) {
            $this->fs->deleteDirectory($siteDir);
        }

        $confFile = $this->confPath . DIRECTORY_SEPARATOR . $domain . '.conf';
        if ($this->fs->exists($confFile)) {
            $this->fs->delete($confFile);
        }

        try {
            $this->reloadNginx();
        } catch (\Throwable $e) {
            Log::warning('Failed to reload nginx after deleting vhost', ['exception' => $e->getMessage()]);
        }

        return true;
    }
}

