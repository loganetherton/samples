<?php
namespace ValuePad\Console\Project;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Console\Command;
use Illuminate\Foundation\Application;
use ValuePad\Console\Support\DoctrineKernel as Doctrine;
use ValuePad\Core\Back\Entities\Admin;
use ValuePad\Core\User\Entities\System;
use ValuePad\Core\User\Interfaces\PasswordEncryptorInterface;
use ValuePad\DAL\Support\ConnectionFactory;
use ValuePad\Core\Location\Entities\County;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Location\Entities\Zip;
use ValuePad\Core\Language\Entities\Language;
use ValuePad\DAL\Location\Fixtures\States;
use ValuePad\Core\JobType\Entities\JobType;

class ProjectResetCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'project:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets the project';

    /**
     * @param Doctrine $doctrine
     * @param Application $application
	 * @param EntityManagerInterface $entityManager
     */
    public function fire(Doctrine $doctrine, Application $application, EntityManagerInterface $entityManager)
    {
        $this->comment('Resetting the project ...');

        /**
         * @var Configuration
         */
        $configuration = $application->make('doctrine:configuration');

        $dbConfig = $this->getDbConfig($application->make('config')
            ->get('doctrine', []));
        $dbName = $this->getDbName($dbConfig);

        unset($dbConfig['path'], $dbConfig['dbname']);

        /**
         *
         * @var Connection $connection
         */
        $connection = call_user_func(new ConnectionFactory(), $dbConfig, $configuration);

        $schemaManager = $connection->getDriver()->getSchemaManager($connection);

        $schemaManager->dropAndCreateDatabase($dbName);

        $doctrine->call('orm:schema-tool:create');

		$this->createSystem($entityManager);

		$this->createAdmin($entityManager);

		$this->seedStates($entityManager);
		$this->seedLanguages($entityManager);
		$this->seedCounties($entityManager);
		$this->seedJobTypes($entityManager);

        $class = 'ValuePad\Seeding\\' . ucfirst(camel_case($application->environment())) . 'Seeder';

        if (class_exists($class)) {
            $application->make($class)->seed();
        }

        $this->info('Perfect!');
    }

    /**
     * @param array $connection
     * @return string
     */
    private function getDbName(array $connection)
    {
        if (isset($connection['path'])) {
            return $connection['path'];
        }

        return $connection['dbname'];
    }

    /**
     * @param array $settings
	 * @return string
     */
    private function getDbConfig(array $settings)
    {
        return $settings['connections'][$settings['db']];
    }

	/**
	 * @param EntityManagerInterface $entityManager
	 */
	private function createSystem(EntityManagerInterface $entityManager)
	{
		$system = new System();

		$system->setEmail('support@appraisalscope.com');

		/**
		 * @var PasswordEncryptorInterface $encryptor
		 */
		$encryptor = $this->laravel->make(PasswordEncryptorInterface::class);

		$system->setPassword($encryptor->encrypt('password'));
		$system->setUsername('valuepad');
		$system->setName('ValuePad');

		$entityManager->persist($system);
		$entityManager->flush();
	}

	/**
	 * @param EntityManagerInterface $entityManager
	 */
	private function createAdmin(EntityManagerInterface $entityManager)
	{
		$config = $this->laravel->make('config')->get('qa.integrations.sessions.credentials.admin');

		$admin = new Admin();

		$admin->setEmail('admin@test.org');
		$admin->setFirstName('Super');
		$admin->setLastName('Admin');
		$admin->setUsername($config['username']);

		/**
		 * @var PasswordEncryptorInterface $encryptor
		 */
		$encryptor = $this->laravel->make(PasswordEncryptorInterface::class);

		$admin->setPassword($encryptor->encrypt($config['password']));

		$entityManager->persist($admin);
		$entityManager->flush();
	}

	/**
	 * @param EntityManagerInterface $entityManager
	 */
	private function seedStates(EntityManagerInterface $entityManager)
	{
		foreach (States::getAll() as $code => $name){
			$state = new State();
			$state->setName($name);
			$state->setCode($code);

			$entityManager->persist($state);
		}

		$entityManager->flush();
	}

	/**
	 * @param EntityManagerInterface $entityManager
	 */
	private function seedLanguages(EntityManagerInterface $entityManager)
	{
		$languages = [
			'cmn' => 'Chinese-Mandarin',
			'eng' => 'English',
			'spa' => 'Spanish',
			'ara' => 'Arabic',
			'ben' => 'Bengali',
			'hin' => 'Hindi',
			'rus' => 'Russian',
			'por' => 'Portuguese',
			'jpn' => 'Japanese',
			'deu' => 'German',
			'zho' => 'Chinese',
			'jav' => 'Javanese',
			'kor' => 'Korean',
			'fra' => 'French',
			'tur' => 'Turkish',
			'vie' => 'Vietnamese',
			'tel' => 'Telugu',
			'yue' => 'Chinese,Yue(Cantonese)',
			'mar' => 'Marathi',
			'tam' => 'Tamil',
			'yid' => 'Yiddish',
			'wuu' => 'Chinese-Wu'
		];

		foreach ($languages as $code => $title){
			$language = new Language();
			$language->setName($title);
			$language->setCode($code);

			$entityManager->persist($language);
		}

		$entityManager->flush();
	}

	/**
	 * @param EntityManagerInterface $entityManager
	 */
	private function seedCounties(EntityManagerInterface $entityManager)
	{
		$pointer = fopen(__DIR__.'/scope_zip_codes.csv', 'r');

		$content = false;
		$zip = null;
		$county = null;
		$state = null;

		$data = [];

		while (($row = fgetcsv($pointer, null, ',')) !== false) {
			if (!$content){
				$zip = array_search('zip_code', $row);
				$county = array_search('county', $row);
				$state = array_search('state', $row);

				$content = true;

				continue ;
			}

			if (trim($row[$county]) === ''){
				$row[$county] = 'UNKNOWN';
			}

			$data[$row[$state]][$row[$county]][] = $row[$zip];
		}

		fclose($pointer);

		/**
		 * @var State[] $states
		 */
		$states  = $entityManager->getRepository(State::class)->findAll();

		foreach ($states as $state){

			$counties = $data[$state->getCode()];


			foreach(array_keys($counties) as $title){
				if (!trim($title)){
					continue;
				}

				$county = new County();
				$county->setTitle($title);
				$county->setState($state);
				$entityManager->persist($county);
			}
		}

		$entityManager->flush();

		/**
		 * @var County[] $counties
		 */
		$counties = $entityManager->getRepository(County::class)->findAll();

		foreach ($counties as $county){

			$zips = $data[$county->getState()->getCode()][$county->getTitle()];

			foreach ($zips as $code){
				$zip = new Zip();
				$zip->setCounty($county);
				$zip->setCode($code);

				$entityManager->persist($zip);

				$county->addZip($zip);
			}
		}

		$entityManager->flush();
	}

	private function seedJobTypes(EntityManagerInterface $entityManager)
	{
		$titles = [
			'Uniform Residential Appraisal (FNMA 1004)',
			'Uniform Residential Appraisal w/ REO (FNMA 1004)',
			'FHA Appraisal (FNMA 1004)',
			'Single Family Investment (1004, 1007, and 216)',
			'Single Family FHA Investment (1004, 1007, and 216)',
			'Single Family Investment w/Comparable Rent Sch (1004 and 1007)',
			'Single Family FHA Investment w/Comparable Rent Schedule (1004 and 1007)',
			'Exterior Only Residential Report (FNMA 2055)',
			'Exterior Only Residential Report w/ Comparable Photos (FNMA 2055)',
			'Exterior Only Investment (2055, 1007, and 216)',
			'Exterior Only Investment w/Comparable Rent Schedule (2055 and 1007)',
			'Multi-Family Appraisal (FNMA 1025)',
			'Multi-Family FHA (FNMA 1025)',
			'Multi-Family Investment (FNMA 1025 and 216)',
			'Condo Appraisal (FNMA 1073)',
			'Condo FHA (1073)',
			'Condo Investment (1073, 1007, and 216)',
			'Condo FHA Investment (1073, 1007, and 216)',
			'Condo Investment w/Comparable Rent Sch (1073 and 1007)',
			'Condo FHA Investment w/Comparable Rent Schedule (1073 and 1007)',
			'Property Inspection (FNMA 2075)',
			'Condition and Marketability Report (FHLMC 2070)',
			'Appraisal Update/Inspection of Repairs (FNMA 1004D)',
			'Land Appraisal',
			'Manufactured Home (FNMA 1004C)',
			'FHA Manufactured Home (FNMA 1004C)',
			'Manufactured Home Investment (FNMA 1004C, 1007, and 216)',
			'Manufactured Home Investment with Comparable Rent Schedule (FNMA 1004C and 1007)',
			'Co-op Appraisal (FNMA 2090)',
			'Exterior Only Co-op Appraisal (FNMA 2095)',
			'Exterior Only Condo Appraisal (FNMA 1075)',
			'Exterior Only Condo Investment (1075, 1007, and 216)',
			'Exterior Only Condo Investment w/Comparable Rent Schedule (1075 and 1007)',
			'Field Review (FNMA 2000)',
			'Multi-Family Field Review (FNMA 2000A)',
			'Supplemental REO Addendum',
			'Desktop Review',
			'Comparable Rent Schedule (1007)',
			'Operating Income Statement (216)',
			'Comparable Rent Schedule w/Operating Income Statement (1007 and 216)',
			'FHA Inspection (CIR)',
			'USDA Appraisal (FNMA 1004)',
			'USDA Condo (FNMA 1073)',
			'FHA Field Review (HUD 1038)',
			'Employee Relocation Council Report (ERC)',
			'Reverse Mortgage Appraisal (1004/FHA)',
			'Drive by Appraisal (Legacy 2055)',
			'Real Estate Value Estimate (RVE)',
			'URAR Single Family 203k',
			'Single Family REO (1004 w/REO Addendum)',
			'Single Family Exterior REO (2055w/REO Addendum)',
			'1025 Multi Family REO Appraisal',
			'Appraisal Update/Recertification (FNMA 1004D)',
			'HUD 92051 Compliance Inspection Report',
			'Single Family FHA REO (1004 w/ REO Addendum)',
			'General Purpose Appraisal Report (GPAR)',
			'Disaster Inspection',
			'Retrospective Field Review',
			'Single Family Investment w/Operating Income State (1004 and 216)',
			'REO Land Appraisal',
			'Condo REO (FNMA 1073',
		];

		foreach ($titles as $title){
			$jt = new JobType();
			$jt->setTitle($title);
			$entityManager->persist($jt);
		}

		$entityManager->flush();
	}
}
