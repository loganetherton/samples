<?php
namespace ValuePad\DAL\Asc\Support\Import;

use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use Symfony\Component\DomCrawler\Field\InputFormField;
use ValuePad\Core\Asc\Interfaces\ImporterInterface;
use Illuminate\Config\Repository as Config;
use ValuePad\Core\Asc\Persistables\AppraiserPersistable;

class Importer implements ImporterInterface
{
	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $storage;

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	private $fields = [
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem4$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem5$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem8$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem9$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem7$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem10$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem11$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem12$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem13$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem14$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem21$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem22$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem23$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem24$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem25$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem26$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem27$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem33$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem36$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem37$cbDisplayField',
		'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$QueryItem38$cbDisplayField',
		'__EVENTTARGET' => 'ctl00$ContentPlaceHolder1$ctl00$Control_Zone1_2_7$lnkBtnDownload',
		'__EVENTARGUMENT' => ''
	];

	/**
	 * @param Config $config
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(Config $config, EntityManagerInterface $entityManager)
	{
		$this->url = $config->get('import.asc.url');
		$this->storage = $config->get('import.asc.storage');
		$this->entityManager = $entityManager;
	}

	/**
	 * @return AppraiserPersistable[]
	 */
	public function import()
	{
		$client = new Client();
		$crawler = $client->request('GET', $this->url);
		$form = $crawler->filter('form')->form();

		foreach ($this->fields as $name => $value){
			if (is_int($name)){
				$name = $value;
				$value = 'on';
			}

			if ($form->has($name)){
				$form->get($name)->setValue($value);
			}

			$domField = (new DOMDocument())->createElement('input');
			$domField->setAttribute('name', $name);
			$domField->setAttribute('value', $value);

			$form->set(new InputFormField($domField));
		}

		$crawler = $client->submit($form);

		$dest = $this->storage.'/__asc_database__.txt';

		$client->download($crawler->selectLink('Tab-Delimited [.txt]')->link(), $dest);

		return Producer::onlyActive($dest);
	}
}
