<?
return [
    // rest or grcp
    'transport' => 'rest',
    // The timeout in seconds when communicating with GCP
    'timeout' => 10,
    // How many times to retry the request when it fails
    'retry' => 2,
    // How many milliseconds to wait after retry fails
    'retry_wait' => 100
];