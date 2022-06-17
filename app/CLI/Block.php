<?php

namespace App\CLI;

use WP_CLI;
use Illuminate\Support\Str;

class Block
{
    public function create(array $args, array $assoc): void
    {
        try {
            if (empty($assoc['name'])) {
                throw new \Exception('--name attribute must be specified.');
            }

            $data = [
                'name' => $assoc['name'],
                'id' => Str::kebab($assoc['name']),
                'slug' => Str::snake($assoc['name']),
                'class' => Str::studly($assoc['name']),
            ];

            $files = [
                'source' => [
                    FIRESTARTER_PATH . "/app/Blocks/Base.php",
                    FIRESTARTER_RESOURCES_PATH . "/blocks/base/scripts.js",
                    FIRESTARTER_RESOURCES_PATH . "/blocks/base/styles.scss",
                    FIRESTARTER_RESOURCES_PATH . "/blocks/base/template.blade.php",
                ],
                'destination' => [
                    FIRESTARTER_PATH . '/app/Blocks' . "/{$data['class']}.php",
                    FIRESTARTER_RESOURCES_PATH . "/blocks/{$data['id']}/scripts.js",
                    FIRESTARTER_RESOURCES_PATH . "/blocks/{$data['id']}/styles.scss",
                    FIRESTARTER_RESOURCES_PATH . "/blocks/{$data['id']}/template.blade.php",
                ],
            ];

            $replace = [
                'from' => ["'Base'", 'Base', 'base_data', "base"],
                'to' => ["'{$data['name']}'", "{$data['class']}", "{$data['slug']}_data", $data['id']],
            ];

            if (count($files['source']) !== count(array_filter($files['source'], fn(string $path) => file_exists($path)))) {
                throw new \Exception('Base files are missing.');
            }

            if (! empty(array_filter($files['destination'], fn(string $path) => file_exists($path)))) {
                throw new \Exception('Destination files already exist.');
            }

            mkdir(FIRESTARTER_RESOURCES_PATH . "/blocks/{$data['id']}");

            for ($i = 0; $i < count($files['source']); $i++) {
                copy($files['source'][$i], $files['destination'][$i]);
                file_put_contents($files['destination'][$i], str_replace($replace['from'], $replace['to'], file_get_contents($files['destination'][$i])));
            }

            WP_CLI::success('Block created!');
        } catch (\Throwable $th) {
            WP_CLI::error($th->getMessage());
        }
    }
}
