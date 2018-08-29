<?php
namespace ValuePad\DAL\Company\Support;

use GuzzleHttp\Client;
use Illuminate\Http\Response;
use ValuePad\Core\Company\Interfaces\DistanceCalculatorInterface;
use Illuminate\Config\Repository as Config;
use Exception;

class DistanceCalculator implements DistanceCalculatorInterface
{
    const URL = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $key;

    /**
     * @param Config $config
     * @param Client $client
     */
    public function __construct(Config $config, Client $client)
    {
        $this->key = $config->get('app.geo.token');
        $this->client = $client;
    }

    public function calculate(array $origins = [], $destination)
    {
        $response = $this->client->get(static::URL, [
            'query' => [
                'origins' => join('|', $origins),
                'destinations' => $destination,
                'units' => 'imperial',
                'key' => $this->key
            ]
        ]);

        $code = $response->getStatusCode();

        if ($code !== Response::HTTP_OK) {
            throw new Exception('Calculating distance failed.');
        }

        $body = json_decode($response->getBody()->getContents(), true);

        $distances = [];
        foreach ($body['rows'] as $i => $row) {
            $distances[$origins[$i]] = null;
            if ($row['elements'][0]['status'] === 'OK') {
                $distances[$origins[$i]] = round(
                    $row['elements'][0]['distance']['value'] / 1609.5 // Convert meters to miles
                );
            }
        }

        return $distances;
    }
}
