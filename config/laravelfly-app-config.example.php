<?php

if (!defined('LARAVELFLY_MODE')) return [];

return [
        'config_changed_in_requests' => [
            /** depends
             * Debugbar is disabled after its booting, so it's necessary to maintain this config for each request.
             * // 'debugbar.enabled',
             */
        ],

        /**
         * useless providers
         *
         * There providers will be removed from app('config')['app.providers'] on worker, before any requets
         */
        'providers_ignore' => [
            Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
            Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class,
        ],

        /**
         * Providers to reg and boot after each request.
         *
         * There providers will be removed from app('config')['app.providers'] on worker, before any requets
         */
        'providers_in_request' => [
        ],

        /**
         * providers to reg and boot on worker, before any request. only for Coroutine mode
         *
         * format:
         *      proverder_name => [],
         *
         * you can also supply singleton services to made on worker
         * only singleton services are useful and valid here.
         * and the singleton services must not be changed during any request,
         * otherwise they should be made in request, no on worker.
         *
         * a singeton service is like this:
         *     *   $this->app->singleton('hash', function ($app) { ... });
         *
         * formats:
         *      proverder2=> [
         *        '_replace' => 'provider1', // the provider1 will be replaced by provider2 and deleted from app['config']['app.providers']
         *        'singleton_service_1' => true,  //  service will be made on worker
         *        'singleton_service_2' => false, //  service will not be made on worker,
         *                                            even if the service has apply if using coroutineFriendlyServices()
         *      ],
         */
        'providers_on_worker' => [
            LaravelFly\Coroutine\Illuminate\Auth\AuthServiceProvider::class=> [
                '_replace'=> Illuminate\Auth\AuthServiceProvider::class ,
            ],
            Illuminate\Broadcasting\BroadcastServiceProvider::class => [],
            Illuminate\Bus\BusServiceProvider::class => [],
            Illuminate\Cache\CacheServiceProvider::class => [
                'cache' => true,
                'cache.store' => true,
                /* depends */
                // if memcached is used, enable it
                // 'memcached.connector' => true,

            ],
            LaravelFly\Coroutine\Illuminate\Cookie\CookieServiceProvider::class=> [
                '_replace'=> Illuminate\Cookie\CookieServiceProvider::class ,
            ],
            LaravelFly\Coroutine\Illuminate\Database\DatabaseServiceProvider::class=> [
                '_replace'=> Illuminate\Database\DatabaseServiceProvider::class,
            ],
            Illuminate\Encryption\EncryptionServiceProvider::class => [
                'encrypter' => true,
            ],
            Illuminate\Filesystem\FilesystemServiceProvider::class => [
                'filesystem.disk' => true,
                'filesystem.cloud' => LARAVELFLY_SINGLETON['filesystem.cloud'],
            ],
            /* This reg FormRequestServiceProvider, whose boot is related to request */
            // Illuminate\Foundation\Providers\FoundationServiceProvider::class=>[] : providers_across ,
            Illuminate\Hashing\HashServiceProvider::class => [
                'hash' => LARAVELFLY_SINGLETON['hash']
            ],
            Illuminate\Mail\MailServiceProvider::class => [],

            // Illuminate\Notifications\NotificationServiceProvider::class,

            /*todo need test : reg allowed?  */
            // Illuminate\Pagination\PaginationServiceProvider::class,

            Illuminate\Pipeline\PipelineServiceProvider::class => [],
            Illuminate\Queue\QueueServiceProvider::class => [],
            Illuminate\Redis\RedisServiceProvider::class => [
                'redis' => LARAVELFLY_SINGLETON['redis'],
            ],
            Illuminate\Auth\Passwords\PasswordResetServiceProvider::class => [],
            Illuminate\Session\SessionServiceProvider::class => [
                // todo test
//                'session' => true,

                // 'session.store' => false,
                // 'Illuminate\Session\Middleware\StartSession' =>false,
            ],
            Illuminate\Translation\TranslationServiceProvider::class => [],
            Illuminate\Validation\ValidationServiceProvider::class => [
//                /* todo
//                  todo it's related to db, when db reconnet, how it ? */
//                /* Illuminate\Validation\ValidationServiceProvider::class :*/
//                // 'validator' => [],
//                // 'validation.presence' => [],
            ],
           \LaravelFly\Coroutine\Illuminate\View\ViewServiceProvider::class => [
                '_replace'=>Illuminate\View\ViewServiceProvider::class ,
            ],
            /*
             * Application Service Providers...
             */
            App\Providers\AppServiceProvider::class => [],
            //todo
            App\Providers\AuthServiceProvider::class => [],
            App\Providers\EventServiceProvider::class => [],
            App\Providers\RouteServiceProvider::class => [],

        ],


        /**
         * Which properties of base services need to backup. Only for One or Greedy mode
         *
         * See: Illuminate\Foundation\Application::registerBaseServiceProviders
         */
        'BaseServices' => [

            /* Illuminate\Events\EventServiceProvider::class : */
            'events' => [
                'listeners', 'wildcards', 'queueResolver',
            ],

            /* Illuminate\Routing\RoutingServiceProvider::class : */
            'router' => [

                /** not necessary to backup,
                 * it will be changed during next request
                 * // 'current',
                 */

                /** not necessary to backup,
                 * the ref to app('request') will be released during next request
                 * //'currentRequest',
                 */

                /** depends
                 * Uncomment them if it's not same on each requests. They may be changed by Route::middleware
                 */
                // 'middleware','middlewareGroups','middlewarePriority',

                'obj.routes' => [
                    /** depends
                     *
                     * Uncomment them if some of your routes are created during any request.
                     * Besides, because values of these four properties are associate arrays,
                     * if names of routes created during request are sometime different , please uncomment them ,
                     */
                    // 'routes' , 'allRoutes' , 'nameList' , 'actionList' ,
                ],
            ], /* end 'router' */

            'url' => [
                /** not necessary to backup,
                 *
                 * the ref to app('request') will be released during next request;
                 * and no need set request for `url' on every request , because there is a $app->rebinding for request:
                 *      $app->rebinding( 'request', $this->requestRebinder() )
                 * //'request'
                 */

                /* depends */
                // 'forcedRoot', 'forceSchema',
                // 'cachedRoot', 'cachedSchema',
            ],


            /** nothing need to backup
             *
             * // 'redirect' => false,
             * // 'routes' => false,
             * // 'log' => false,
             */
        ],
    ] +



    (LARAVELFLY_MODE != 'Greedy' ? [] :
        [
            /**
             * providers to boot in worker, before any request only for Greedy mode
             *
             */
            'providers_in_worker' => [
                Illuminate\Cookie\CookieServiceProvider::class => [
                    'cookie' => [
                        /** depends
                         * uncomment them if they are changed during request
                         */
                        // 'path', 'domain',

                        //todo necessary?
                        'queued',
                    ],
                ],
                Illuminate\Mail\MailServiceProvider::class => [
                    /* depends */
                    /* comment 'mailer' if your app do not use mail */
                    'mailer' => [
                        'failedRecipients',

                        /** depends
                         */
                        //'from' ,
                        //'to' ,
                        //'pretending' ,

                    ],
                ],
                // Illuminate\Pagination\PaginationServiceProvider::class=>[] :
                Illuminate\Pipeline\PipelineServiceProvider::class => [
                    'Illuminate\\Contracts\\Pipeline\\Hub' => [],
                ],
                Illuminate\Queue\QueueServiceProvider::class => [
                    /** depends
                     */
                    //'queue' => [],
                    //'queue.connection' => [],
                ],
                Illuminate\Session\SessionServiceProvider::class => [
                    'session' => [],
                    'session.store' => [
                        'id', 'name', 'attributes',
                    ],
                    'Illuminate\Session\Middleware\StartSession' => [
                        'sessionHandled',
                    ],
                ],
                Illuminate\Translation\TranslationServiceProvider::class => [
                    'translator' => [],
                ],

            ],

            /** load views as early as possible
             *
             * Before any request , these view files will be found.
             *
             * Only for Greedy mode
             */
            'views_to_find_in_worker' => [
                // 'home','posts.create','layout.master',
            ]

        ]);

