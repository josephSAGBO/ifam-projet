<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Spatie\Permission\Models\Role;

// Ensure admin role exists
Role::firstOrCreate(['name' => 'admin']);
Role::firstOrCreate(['name' => 'teacher']);
Role::firstOrCreate(['name' => 'student']);
Role::firstOrCreate(['name' => 'parent']);

$admin = User::firstOrCreate(
    ['email' => 'admin@ifam.local'],
    [
        'first_name' => 'Admin',
        'last_name' => 'IFAM',
        'password' => bcrypt('password123'),
    ]
);
$admin->assignRole('admin');

$teacher = User::firstOrCreate(
    ['email' => 'teacher@ifam.local'],
    [
        'first_name' => 'Prof',
        'last_name' => 'IFAM',
        'password' => bcrypt('password123'),
    ]
);
$teacher->assignRole('teacher');

if (! $teacher->teacherProfile) {
    $teacher->teacherProfile()->create([
        'specialty' => 'Maths',
    ]);
}

$student = User::firstOrCreate(
    ['email' => 'student@ifam.local'],
    [
        'first_name' => 'Eleve',
        'last_name' => 'IFAM',
        'password' => bcrypt('password123'),
    ]
);
$student->assignRole('student');

if (! $student->studentProfile) {
    $student->studentProfile()->create([
        'nationality' => 'Béninoise',
    ]);
}

$parent = User::firstOrCreate(
    ['email' => 'parent@ifam.local'],
    [
        'first_name' => 'Parent',
        'last_name' => 'IFAM',
        'password' => bcrypt('password123'),
    ]
);
$parent->assignRole('parent');

echo "Users created successfully!\n";
