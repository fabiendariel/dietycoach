<?php

namespace AppBundle\Command;

use AppBundle\Entity\Transmission;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// */5 * * * * php /var/www/promed_halomuco/bin/console appbundle:transmission:document

/**
 * Class Generate documentation.
 */
class GenerateCodeCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
          ->setName('appbundle:generate:code')
          ->setDescription('Génération de codes en vrac');
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Génération de 2006 codes');
        apc_clear_cache ();

        $codes = $this->generateCodesAcces(2006);
        foreach($codes as $code){
            $output->writeln('<info>'.$code.'</info>');
        }
    }

    private function generateCodesAcces($combien)
    {
        $codes = array();
        $isOk = false;
        do{
            do{
                $code_test1 = $this->getCode();
                if(!in_array($code_test1,$codes)) {
                    $isOk = true;
                    $combien--;
                    $codes[]=$code_test1;
                }
            }while(!$isOk);
        }while($combien>0);

        return $codes;
    }

    private function getCode(){
        $permitted_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $key1 = $this->generate_string($permitted_chars, 4);
        $key2 = $this->generate_string($permitted_chars, 4);
        $key3 = $this->generate_string($permitted_chars, 4);

        return $key1.'-'.$key2.'-'.$key3;
    }

    private function generate_string($input, $strength = 16) {
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }

        return $random_string;
    }

}