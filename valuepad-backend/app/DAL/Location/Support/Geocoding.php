<?php
namespace ValuePad\DAL\Location\Support;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use ValuePad\Core\Location\Interfaces\GeocodingInterface;
use ValuePad\Core\Location\Objects\Coordinates;
use ValuePad\Core\Location\Objects\Location;
use Illuminate\Config\Repository as Config;
use Exception;
use DateTime;
use Log;
class Geocoding implements GeocodingInterface
{
    const URL = 'https://maps.googleapis.com/maps/api/geocode/json';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $token;

    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param Config $config
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(Config $config, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->client = new Client();
        $this->token = $config->get('app.geo.token');
        $this->isEnabled = $config->get('app.geo.enabled', false);
    }

    /**
     * @param Location $location
     * @return Coordinates
     */
    public function toCoordinates(Location $location)
    {
        if (!$this->isEnabled){
            return null;
        }

        if ($coordinates = $this->tryGetCoordinates($location)){
            return $this->createCoordinates($coordinates[0], $coordinates[1]);
        }

        if (!$place = $this->tryGetPlace($location)){
            $place = new Place();
            $place->setAddress(strtolower((string) $location));
        }

        $isFailed = false;

        try {
            list($latitude, $longitude) = $this->pullCoordinates($location);
            $place->setAttempts(0);
            $place->setError(null);
            $place->setMessage(null);
            $place->setLatitude($latitude);
            $place->setLongitude($longitude);
        } catch (Exception $ex) {
            $place->addAttempt();
            $place->setMessage($ex->getMessage());

            if ($ex instanceof ErrorException){
                $place->setError($ex->getError());
            } else {
                $place->setError(new Error(Error::UNKNOWN));
                Log::warning($ex);
            }

            $isFailed = true;
        }

        $place->setUpdatedAt(new DateTime());

        if ($place->getId() === null){
            $this->entityManager->persist($place);
        }

        $this->entityManager->flush();

        if ($isFailed){
            return null;
        }

        return $this->createCoordinates($place->getLatitude(), $place->getLongitude());
    }

    /**
     * @param string $latitude
     * @param string $longitude
     * @return Coordinates
     */
    private function createCoordinates($latitude, $longitude)
    {
        $coordinates =  new Coordinates();

        $coordinates->setLongitude($longitude);
        $coordinates->setLatitude($latitude);

        return $coordinates;
    }

    /**
     * @param Location $location
     * @return array
     */
    private function tryGetCoordinates(Location $location)
    {
        if (!$place = $this->tryGetPlace($location)){
            return null;
        }

        if ($place->getLatitude() === null || $place->getLongitude() === null){
            return null;
        }

        return [$place->getLatitude(), $place->getLongitude()];
    }

    /**
     * @param Location $location
     * @return Place
     */
    private function tryGetPlace(Location $location)
    {
        /**
         * @var Place $place
         */
        $place = $this->entityManager->getRepository(Place::class)
            ->findOneBy(['address' => strtolower(trim((string) $location))]);

        if (!$place){
            return null;
        }

        return $place;
    }

    /**
     * @param Location $location
     * @return array
     * @throws ErrorException
     */
    private function pullCoordinates(Location $location)
    {
        $response = $this->client->get(static::URL, [
            'query' => [
                'address' => (string) $location,
                'key' => $this->token
            ]
        ]);

        $code = (int) $response->getStatusCode();

        if ($code !== 200){
            throw new ErrorException(new Error(Error::UNKNOWN), 'Request failed with the "'.$code.'" code.');
        }

        $data = $response->getBody()->getContents();

        $data = json_decode($data, true);

        if ($data === null){
            throw new ErrorException(new Error(Error::UNKNOWN), 'Unable to parse retrieved data.');
        }

        $status = $data['status'];

        if ($status !== 'OK'){
            $this->throwError($status);
        }

        $data = $data['results'][0]['geometry']['location'];

        return [$data['lat'], $data['lng']];
    }

    /**
     * @param string $status
     * @throws ErrorException
     */
    private function throwError($status)
    {
        $config = [
            'ZERO_RESULTS' => [
                Error::ZERO_RESULTS,
                'The geocode was successful but returned no results. This may occur if the geocoder was passed a non-existent address.',
            ],

            'OVER_QUERY_LIMIT' => [
                Error::OVER_QUERY_LIMIT,
                'You are over your quota.'
            ],

            'REQUEST_DENIED' => [
                Error::DENIED,
                'Your request was denied.'
            ],

            'INVALID_REQUEST' => [
                Error::INVALID,
                'The address is missing.'
            ],

            'UNKNOWN_ERROR' => [
                Error::SERVER,
                'The request could not be processed due to a server error. The request may succeed if you try again.'
            ]
        ];

        if (!$data = array_take($config, $status)){
            throw new ErrorException(new Error(Error::UNKNOWN), 'Unknown status: '.$status);
        }

        throw new ErrorException(new Error($data[0]), $data[1]);
    }

    /**
     * @param Coordinates $coordinates
     * @return Location
     */
    public function toLocation(Coordinates $coordinates)
    {
        // TODO: Implement toAddress() method.
    }
}
