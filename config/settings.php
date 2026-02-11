<?php

declare(strict_types=1);

return [
    'settings' => [
        'displayErrorDetails' => (getenv('APP_ENV') === 'development' || filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN)), // Should be false in production
        'logError'            => false,
        'logErrorDetails'     => false,
    ],
];
