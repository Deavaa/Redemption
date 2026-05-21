<?php
$dsn = 'mysql:host=127.0.0.1;dbname=school_of_redemption;charset=utf8mb4';
$user = 'root';
$pass = '';
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$tables = [
    'branches',
    'academic_years',
    'terms',
    'settings',
    'permissions',
    'roles',
    'permission_role',
    'users',
    'role_user',
    'classes',
    'sections',
    'subjects',
    'teachers',
    'teacher_assignments',
    'students',
    'student_enrollments',
    'sliders',
    'gallery_images',
    'class_assets',
    'exams',
    'library_books',
];

$data = [];
foreach ($tables as $table) {
    $stmt = $pdo->query("SELECT * FROM `$table`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data[$table] = $rows;
}

$output = "<?php\n\nnamespace Database\\Seeders;\n\nuse Illuminate\\Database\\Seeder;\nuse Illuminate\\Support\\Facades\\DB;\n\nclass ExistingDatabaseSeeder extends Seeder\n{\n    public function run(): void\n    {\n        DB::statement('SET FOREIGN_KEY_CHECKS=0;');\n\n";

foreach ($tables as $table) {
    $output .= "        DB::table('$table')->truncate();\n";
}

$output .= "\n";

foreach ($tables as $table) {
    $rows = $data[$table];
    if (count($rows) === 0) {
        continue;
    }
    $export = var_export($rows, true);
    $export = preg_replace('/^([ ]*)array \(/m', '$1[', $export);
    $export = str_replace(['array (', ')'], ['[', ']'], $export);
    $export = preg_replace('/\n([ ]*)\]/', "\n$1]", $export);
    $output .= "        DB::table('$table')->insert($export);\n\n";
}

$output .= "        DB::statement('SET FOREIGN_KEY_CHECKS=1;');\n    }\n}\n";

file_put_contents(__DIR__ . '/../database/seeders/ExistingDatabaseSeeder.php', $output);
echo "Generated database/seeders/ExistingDatabaseSeeder.php\n";
