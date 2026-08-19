<?php

return [
    // Direktori backup di LUAR webroot (mis. /home/user/backups di atas public_html).
    'external_path' => env('BACKUP_EXTERNAL_PATH', base_path('../backups')),

    // Path binary mysqldump (biasanya di PATH pada shared hosting).
    'mysqldump_path' => env('MYSQLDUMP_PATH', 'mysqldump'),
];
