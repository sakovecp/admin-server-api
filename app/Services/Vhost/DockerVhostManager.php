<?php

namespace App\Services\Vhost;


use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;


class DockerVhostManager extends AbstractVhostManager
{
    public function create(string $domain, ?int $port = null): array
    {
        $domain = $this->sanitizeDomain($domain);


        if ($this->mode === 'local') {
            $sitesAvailable = config('nginxapi.local.sites_available');
            $sitesEnabled = config('nginxapi.local.sites_enabled');
            $htmlRoot = config('nginxapi.local.html_root');


            $confPath = rtrim($sitesAvailable, '/') . '/' . $domain . '.conf';
            $siteDir = rtrim($htmlRoot, '/') . '/' . $domain;
            $indexPath = $siteDir . '/index.html';


            if ($this->files->exists($confPath)) {
                return ['success' => false, 'reason' => 'conf_exists'];
            }


// створюємо папку сайту
            if (!$this->files->isDirectory($siteDir)) {
                $this->files->makeDirectory($siteDir, 0755, true);
            }


// запис index.html
            $this->files->put($indexPath, "<html><body>HELLO {$domain}</body></html>");


// створюємо nginx конфіг
            $template = $this->vhostTemplate($domain, $siteDir);
            $this->files->put($confPath, $template);


// створюємо симлінк у sites-enabled
            $enabledPath = rtrim($sitesEnabled, '/') . '/' . $domain . '.conf';
            if (!$this->files->exists($enabledPath)) {
// створюємо симлінк через shell (щоб мати можливість створити лінк під root)
                $cmd = ['ln', '-s', $confPath, $enabledPath];
                $p = new Process($cmd);
                $p->run();
// ігноруємо помилку якщо вже існує
            }
            // mode docker
            if ($this->mode === 'docker') {
                $container = config('nginxapi.docker.container');
                $sitesAvailable = config('nginxapi.docker.sites_available');
                $htmlRoot = config('nginxapi.docker.html_root');


                $confName = $domain . '.conf';
                $confContent = $this->vhostTemplate($domain, $htmlRoot . '/' . $domain);
                $indexContent = "<html><body>HELLO {$domain}</body></html>";


// Запис файлів тимчасово локально та копіювання в контейнер
                $tmpConf = sys_get_temp_dir() . '/' . $confName;
                file_put_contents($tmpConf, $confContent);


                $tmpIndex = sys_get_temp_dir() . '/' . $domain . '_index.html';
                file_put_contents($tmpIndex, $indexContent);


// copy conf into container
                $p1 = new Process(['docker', 'cp', $tmpConf, "{$container}:{$sitesAvailable}/{$confName}"]);
                $p1->run();


// create html dir and copy index
                $p2 = new Process(['docker', 'exec', $container, 'mkdir', '-p', $htmlRoot . '/' . $domain]);
                $p2->run();


                $p3 = new Process(['docker', 'cp', $tmpIndex, "{$container}:{$htmlRoot}/{$domain}/index.html"]);
                $p3->run();


                return ['success' => true, 'conf' => "{$sitesAvailable}/{$confName}", 'index' => "{$htmlRoot}/{$domain}/index.html"];
            }


            return ['success' => false, 'reason' => 'unknown_mode'];

        }

        // mode docker
        if ($this->mode === 'docker') {
            $container = config('nginxapi.docker.container');
            $sitesAvailable = config('nginxapi.docker.sites_available');
            $htmlRoot = config('nginxapi.docker.html_root');


            $confName = $domain . '.conf';
            $confContent = $this->vhostTemplate($domain, $htmlRoot . '/' . $domain);
            $indexContent = "<html><body>HELLO {$domain}</body></html>";


// Запис файлів тимчасово локально та копіювання в контейнер
            $tmpConf = sys_get_temp_dir() . '/' . $confName;
            file_put_contents($tmpConf, $confContent);


            $tmpIndex = sys_get_temp_dir() . '/' . $domain . '_index.html';
            file_put_contents($tmpIndex, $indexContent);


// copy conf into container
            $p1 = new Process(['docker', 'cp', $tmpConf, "{$container}:{$sitesAvailable}/{$confName}"]);
            $p1->run();


// create html dir and copy index
            $p2 = new Process(['docker', 'exec', $container, 'mkdir', '-p', $htmlRoot . '/' . $domain]);
            $p2->run();


            $p3 = new Process(['docker', 'cp', $tmpIndex, "{$container}:{$htmlRoot}/{$domain}/index.html"]);
            $p3->run();


            return ['success' => true, 'conf' => "{$sitesAvailable}/{$confName}", 'index' => "{$htmlRoot}/{$domain}/index.html"];
        }


        return ['success' => false, 'reason' => 'unknown_mode'];
    }

    public function delete(string $domain): bool
    {
        $domain = $this->sanitizeDomain($domain);


        if ($this->mode === 'local') {
            $sitesAvailable = config('nginxapi.local.sites_available');
            $sitesEnabled = config('nginxapi.local.sites_enabled');
            $htmlRoot = config('nginxapi.local.html_root');


            $confPath = rtrim($sitesAvailable, '/') . '/' . $domain . '.conf';
            $enabledPath = rtrim($sitesEnabled, '/') . '/' . $domain . '.conf';
            $siteDir = rtrim($htmlRoot, '/') . '/' . $domain;


            if ($this->files->exists($enabledPath)) {
                @unlink($enabledPath);
            }
            if ($this->files->exists($confPath)) {
                $this->files->delete($confPath);
            }
            if ($this->files->isDirectory($siteDir)) {
                $this->files->deleteDirectory($siteDir);
            }


            return ['success' => true];
        }

        if ($this->mode === 'docker') {
            $container = config('nginxapi.docker.container');
            $sitesAvailable = config('nginxapi.docker.sites_available');
            $htmlRoot = config('nginxapi.docker.html_root');


            $confName = $domain . '.conf';


            $p1 = new Process(['docker', 'exec', $container, 'rm', '-f', $sitesAvailable . '/' . $confName]);
            $p1->run();


            $p2 = new Process(['docker', 'exec', $container, 'rm', '-rf', $htmlRoot . '/' . $domain]);
            $p2->run();


            return ['success' => true];
        }


        return ['success' => false, 'reason' => 'unknown_mode'];
    }

    protected function vhostTemplate(string $domain, string $rootDir): string
    {
        return <<<HTML
server {
listen 80;
server_name {$domain} www.{$domain};


root {$rootDir};
index index.html;


location / {
try_files \$uri \$uri/ =404;
}
}
HTML;
    }
}
