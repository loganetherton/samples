<?php
use ValuePad\Support\Shortcut;

return [
    /*
     * Sets whether the library should work in Debug Mode
     */
    'debug' => env('APP_DEBUG', false),

    /*
     * Sets config options to the transformer
     */
    'transformer' => [

        /*
         * Sets the key name of the wrapper around collection/item when outputting the result
         */
        'wrapper' => [

            /*
             * Sets the key name for the collection.
             * If null is set or the element is left unset, then the collection will be left unwrapped.
             */
            'collection' => 'data'
        ]
    ],
    'composite' => [
        'router' => [
			'route' => Shortcut::prependGlobalRoutePrefix('batch'),
			'options' => [
				'middleware' => 'cors'
			]
		]
    ]
];