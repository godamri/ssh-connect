<?php

namespace App\Command\Ssh;

use Minicli\Input;
use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function handle(): void
    {
        $home = getenv('HOME');
        $configs = json_decode(file_get_contents($home . '/sshcm.json'), true) ?? [];
        if (count($configs) < 1) {
            $this->getPrinter()->display('No config found.');
            return;
        }
        foreach ($configs as $key => $config) {
            $this->getPrinter()->display('[' . $key . ']' . $config['user'] . '@' . $config['connection_name']);
        }
        $selected = 0;
        $input = new Input('Select Connection: [' . $selected . '] =>');
        $read = $input->read();
        if ($read === '') {
            $read = $selected;
        }
        $selectedConfig = $configs[$read];
        if ($selectedConfig['type'] === 'public_key') {
            passthru(sprintf(
                'ssh -o StrictHostKeyChecking=no -i %s %s@%s -p %s',
                $selectedConfig['private_key'],
                $selectedConfig['user'],
                $selectedConfig['host'],
                $selectedConfig['port']
            ));
        } else {
            passthru(sprintf(
                'sshpass -p %s ssh -o StrictHostKeyChecking=no %s@%s -p %s',
                $selectedConfig['password'],
                $selectedConfig['user'],
                $selectedConfig['host'],
                $selectedConfig['port'],
            ));
        }

        return;
    }
}
