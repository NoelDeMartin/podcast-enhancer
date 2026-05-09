<?php

use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

it('renders the homepage using inertia ssr', function () {
    if (! file_exists(base_path('bootstrap/ssr/ssr.js'))) {
        $this->fail('SSR bundle not built.');
    }

    $pingSsr = function () {
        try {
            return Http::post('http://127.0.0.1:13714/render', [
                'component' => 'Welcome/Index',
                'props' => ['auth' => ['user' => null]],
                'url' => '/',
                'version' => 'test',
            ])->successful();
        } catch (Exception) {
            return false;
        }
    };

    $process = null;

    if (! $pingSsr()) {
        $process = new Process(['node', base_path('bootstrap/ssr/ssr.js')]);
        $process->start();

        $ready = false;
        for ($i = 0; $i < 10; $i++) {
            usleep(500000);

            if ($pingSsr()) {
                $ready = true;
                break;
            }
        }

        if (! $ready) {
            $process->stop();
            $this->fail('Could not start SSR server: '.$process->getErrorOutput());
        }
    }

    try {
        config(['inertia.ssr.enabled' => true]);

        $response = $this->get('/');

        $response->assertSuccessful();
        $response->assertSee('How does it work?');
        $response->assertSee('<h2', false);
    } finally {
        $process?->stop();
    }
});
