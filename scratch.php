<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$users = User::role('enseignant')->with('teacherProfile.modules')->get();
foreach ($users as $u) {
    echo $u->full_name.' -> ';
    if ($u->teacherProfile && $u->teacherProfile->modules) {
        echo $u->teacherProfile->modules->pluck('name')->join(', ');
    } else {
        echo 'RIEN';
    }
    echo PHP_EOL;
}
