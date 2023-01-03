<?php

namespace App\Command\Ssh;

use Minicli\Command\CommandController;

class ListController extends CommandController
{
    public function handle(): void
    {       
        $home = getenv('HOME');
        if(!is_file($home . '/sshcm.json')) {
            $this->getPrinter()->display('Config file does not exists.');
            return;
        }
        $configs = json_decode( file_get_contents( $home . '/sshcm.json' ), true ) ?? [];
        if(count($configs) < 1) {
            $this->getPrinter()->display('No config found.');
            return;
        }
        foreach($configs as $config)
        {
            $this->getPrinter()->display($config['connection_name'].'['.$config['user'].']');
        }
        
    }
}