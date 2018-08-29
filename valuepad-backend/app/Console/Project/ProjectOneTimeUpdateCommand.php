<?php
namespace ValuePad\Console\Project;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Console\Command;
use ValuePad\Core\Shared\Interfaces\TokenGeneratorInterface;

class ProjectOneTimeUpdateCommand extends Command
{
	protected $name = 'project:one-time-update';

	public function fire(EntityManagerInterface $entityManager, TokenGeneratorInterface $generator)
	{

	}
}
