<?php
namespace ValuePad\Push\Support;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Foundation\Application;
use ValuePad\Core\User\Entities\User;
use ValuePad\Core\User\Interfaces\ActorProviderInterface;

class Listener
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Application $application
     */
    private $application;

    /**
     * @var ActorProviderInterface
     */
    private $actorProvider;

    /**
     * @param EntityManagerInterface $entityManager
     * @param Application $application
     * @param ActorProviderInterface $actorProvider
     */
    public function __construct(EntityManagerInterface $entityManager, Application $application, ActorProviderInterface $actorProvider)
    {
        $this->entityManager = $entityManager;
        $this->application = $application;
        $this->actorProvider = $actorProvider;
    }

    /**
     * @param Request $request
     * @param Response|Exception $responseOrException
     * @param array $data
     * @param User $target
     */
    public function __invoke(Request $request, $responseOrException, array $data, User $target)
    {
        // we don't want to log things when testing

        if ($this->application->environment() === 'tests'){
            return ;
        }

        $story = new Story();

        $request = [
            'url' => (string) $request->getUri(),
            'method' => strtoupper($request->getMethod()),
            'headers' => array_map(function($header){ return $header[0]; }, $request->getHeaders()),
            'body' => (string) $request->getBody()
        ];

        $story->setRequest($request);

        if ($responseOrException instanceof Exception){
            $error = [
                'code' => $responseOrException->getCode(),
                'message' => $responseOrException->getMessage(),
                'file' => $responseOrException->getFile(),
                'line' => $responseOrException->getLine(),
                'trace' => $responseOrException->getTrace()

            ];

            $story->setError($error);

            if ($responseOrException instanceof BadResponseException){

                $response = $responseOrException->getResponse();

                $story->setResponse([
                    'status' => $response->getStatusCode(),
                    'body' => (string) $response->getBody()
                ]);

                $story->setCode($response->getStatusCode());
            }

        } else {
            $story->setResponse([
                'status' => $responseOrException->getStatusCode(),
                'body' => (string) $responseOrException->getBody()
            ]);

            $story->setCode($responseOrException->getStatusCode());
        }

        $story->setType($data['type']);
        $story->setEvent($data['event']);

        if (isset($data['order'])) {
            $story->setOrder($data['order']);
        }

        $story->setSender($this->actorProvider->getActor());
        $story->setRecipient($target);

        $this->entityManager->persist($story);
        $this->entityManager->flush();
    }
}
