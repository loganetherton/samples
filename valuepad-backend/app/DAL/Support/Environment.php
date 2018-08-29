<?php
namespace ValuePad\DAL\Support;

use DateTime;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Support\Shortcut;

class Environment implements EnvironmentInterface
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @return bool
	 */
	public function isRelaxed()
	{
		/**
		 * @var Request $request
		 */
		$request = $this->container->make('request');

		$relax = $request->header('Relax');

		return $relax === 'jwB87k9t1oa0DsA9V85TnzM2A6nNVi1f';
	}

	/**
	 * @return DateTime
	 */
	public function getLogCreatedAt()
	{
		/**
		 * @var Request $request
		 */
		$request = $this->container->make('request');

		$createdAt = $request->input('logCreatedAt');

		if (!$createdAt){
			return null;
		}

		return Shortcut::utc($createdAt);
	}

    /**
     * Takes id of the assignee as who the customer acts
     * @return bool
     */
    public function getAssigneeAsWhoActorActs()
    {
        /**
         * @var Request $request
         */
        $request = $this->container->make('request');

        $id = $request->header('Act-As-Assignee');

        if ($id !== null){
            return (int) $id;
        }

        return null;
    }
}
