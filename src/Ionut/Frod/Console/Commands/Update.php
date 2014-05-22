<?php namespace Ionut\Frod\Console\Commands;

use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \Ionut\Frod\Console\Command;
use \Ionut\Frod\Console\Application;
use \Ionut\Frod\Facades\Client;

class Update extends Command {

	protected function configure()
	{
		 $this
            ->setName('update')
            ->setDescription('Update packages')
            ->setHelp(<<<EOT
Update packages data from Frod repositories.

Usage:

<info>php frod update</info>

* If you dont like new packages, just run php frod downdate
EOT
);
        ;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->comment('Download and parse packages...');

        $packages = Client::combineRequest('GET', 'packages');

        $baseFrodPath = pathUpLevels(__DIR__, 6);
        $dataFrodPath = $baseFrodPath.'/data';
        $packagesPath = $dataFrodPath.'/packages.json';

        if(version_compare(PHP_VERSION, '5.4', '<')){
            define('JSON_PRETTY_PRINT', 1);
        }

        copy($packagesPath, $dataFrodPath.'/archive/'.time());

        $this->comment('Clear storage data...');
        clearStorages();

        file_put_contents($packagesPath, json_encode($packages, JSON_PRETTY_PRINT));

        $this->info('Packages up to date.');
    }

}
