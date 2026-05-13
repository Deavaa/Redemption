<?php
 $vdir = "resources/views/admin";

 $fixes = [
    "MarkEntry" => "admin.mark-entries",
    "TeacherAssignment" => "admin.teacher-assignments",
    "IdCard" => "admin.id-cards",
];

foreach ($fixes as $mod => $rp) {
    $bad = "admin." . strtolower($mod) . "s";
    foreach (["index.blade.php", "create.blade.php", "edit.blade.php"] as $vf) {
        $path = "$vdir/$mod/$vf";
        if (file_exists($path)) {
            $c = file_get_contents($path);
            $c = str_replace($bad, $rp, $c);
            file_put_contents($path, $c);
            echo "Fixed $mod/$vf: $bad -> $rp\n";
        }
    }
}

// Also fix plural labels
 $m = ["MarkEntrys" => "Mark Entries", "TeacherAssignments" => "Teacher Assignments", "IdCards" => "ID Cards"];
foreach ($m as $bad => $good) {
    $p = "$vdir/".explode("s", $bad, 2)[0]."/index.blade.php";
    if (file_exists($p)) {
        $c = file_get_contents($p);
        $c = str_replace($bad, $good, $c);
        file_put_contents($p, $c);
        echo "Fixed label: $bad -> $good\n";
    }
}
echo "Done.";
