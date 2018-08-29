<?php
namespace ValuePad\Api\Support;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Config\Repository as Config;
use DateTime;

class AccessMiddleware
{
    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var string
     */
    private $path;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->isEnabled = $config->get('app.access_logs.enabled', false);
        $this->path = $config->get('app.access_logs.path');
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->isEnabled){
            $data = [
                'uri' => $request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo(),
                'method' => strtoupper($request->getMethod()),
                'headers' => array_map(function($v){ return $v[0] ?? null; }, $request->headers->all()),
                'query' => $request->query->all(),
                'body' => $request->json()->all(),
                'createdAt' => (new DateTime())->format(DateTime::ATOM)
            ];

            file_put_contents($this->path.'/vp_access_logs.log', json_encode($data)."\n", FILE_APPEND);
        }

        return $next($request);
    }
}
