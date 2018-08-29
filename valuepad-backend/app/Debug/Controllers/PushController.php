<?php
namespace ValuePad\Debug\Controllers;
use Illuminate\Http\Request;


/**
 * The controller is used to catch notifications and log 'em into the file for later verification in tests and etc.
 *
 *
 */
class PushController extends AbstractResourceController
{
	protected $file = 'push.json';

    public function store(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if ($request->has('_tag')){
            $data['_tag'] = $request->input('_tag');
        }

        $this->storage->store($data);
    }
}
