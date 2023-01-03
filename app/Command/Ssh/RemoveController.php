<?php

namespace App\Command\Ssh;

use Minicli\Input;
use Minicli\Command\CommandController;

class RemoveController extends CommandController
{
    public function handle(): void
    {
        $home = getenv('HOME');
        $configs = json_decode( file_get_contents( $home . '/sshcm.json' ), true ) ?? [];
        
        if(count($configs) < 1) {
            $this->getPrinter()->display('No config found.');
            return;
        }

        foreach ($configs as $key => $config) {
            $this->getPrinter()->display('[' . $key . ']' . $config['connection_name'] . '[' . $config['user'] . ']');
        }
        $input = new Input('Delete Connection =>');
        $read = $input->read();
        if ($read === '') {
            $this->getPrinter()->display('No connection selected.');
            return;
        }
        elseif ( (string)(int)$read !== $read ) {
            $this->getPrinter()->display('Select a valid connection index.');
            return;
        }
        else {
            if(is_file($configs[(int)$read]['private_key'])) {
                unlink( $configs[(int)$read]['private_key'] );
            }
            if(is_file($configs[(int)$read]['public_key'])) {
                unlink( $configs[(int)$read]['public_key'] );
            }
            unset($configs[(int)$read]);
        }
        file_put_contents( $home . '/sshcm.json', json_encode( array_values($configs) ) );
        $this->getPrinter()->display('Connection removed.');
        return;
    }
}
