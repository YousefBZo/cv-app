<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| DEPLOYMENT NOTE:
| For production, update the following values:
|   - allowed_origins → your real frontend domain(s)
|   - max_age         → 86400 (24 h) to cache preflight responses
|   - exposed_headers → ['ETag'] for cache validation
|
*/
return [
    'paths' => ['api/*', 'storage/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
