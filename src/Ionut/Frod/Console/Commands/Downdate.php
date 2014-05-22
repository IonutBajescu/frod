<?php namespace Ionut\Frod\Console\Commands;

use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \Ionut\Frod\Console\Command;
use \Ionut\Frod\Console\Application;
use \Ionut\Frod\Facades\Client;

class Downdate extends Command {

	protected function configure()
	{
		 $this
            ->setName('downdate')
            ->setDescription('Downdate packages')
            ->setHelp(<<<EOT
Downdate packages data from local arhive with older packages data.

Usage:

<info>php frod downdate</info>
EOT
);
        ;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseFrodPath = pathUpLevels(__DIR__, 6);
        $dataFrodPath = $baseFrodPath.'/data';
        $packagesPath = $dataFrodPath.'/packages.json';

        $oldPackagesDate = date('d.m.Y H:i',filemtime($packagesPath));

        $lastFile = null;
        $files = glob($dataFrodPath.'/archive/*');
        foreach($files as $file){
            $fileName = basename($file);
            if($fileName > $lastFile){
                $lastFile = $fileName;
            }
        }
        $prevPackagesArhive = $dataFrodPath.'/archive/'.$lastFile;

        copy($prevPackagesArhive, $packagesPath);

        $this->comment('Clear storage data...');
        clearStorages();

        $newPackagesDate = date('d.m.Y H:i',filemtime($prevPackagesArhive));
        unlink($prevPackagesArhive);

        $this->info('Packages from '.$oldPackagesDate.' downdated to '.$newPackagesDate.' version');
    }

}
