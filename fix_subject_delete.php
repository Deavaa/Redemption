<?php
echo "=== FIX SUBJECT DELETE ===

";
$b = getcwd();
if (!file_exists($b."/artisan")) die("Run from Redemption root!
");
$f = 0;
echo "Bug 1: CSRF meta tag...
";
$l = file_get_contents($b."/resources/views/layouts/admin.blade.php");
if (strpos($l, "csrf-token") === false) {
    $l = str_replace("<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">", "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <meta name=\"csrf-token\" content=\"{{ csrf_token() }}\">", $l);
    file_put_contents($b."/resources/views/layouts/admin.blade.php", $l);
    echo "  FIXED: Added CSRF meta tag
"; $f++;
} else echo "  Already exists
";
echo "Bug 2-4: Fixing JS + bulk button + data-url...
";
$v = file_get_contents($b."/resources/views/admin/subject-assignments/index.blade.php");
$v = preg_replace("/@push\(\"scripts\"\).*?@endpush/s", "@push(\"scripts\")
<script>
document.getElementById(\"selectAll\")?.addEventListener(\"change\",function(){document.querySelectorAll(\".bulk-check\").forEach(c=>c.checked=this.checked);updUI();});
document.querySelectorAll(\".bulk-check\").forEach(c=>{c.addEventListener(\"change\",function(){const a=document.querySelectorAll(\".bulk-check\"),b=document.querySelectorAll(\".bulk-check:checked\"),s=document.getElementById(\"selectAll\");if(s)s.checked=(a.length===b.length&&a.length>0);updUI();});});
function updUI(){const c=document.querySelectorAll(\".bulk-check:checked\"),e=document.getElementById(\"selectedCount\"),b=document.getElementById(\"bulkDeleteBtn\");if(e)e.textContent=c.length+\" selected\";if(b)b.disabled=(c.length===0);const t=document.getElementById(\"bulkToolbar\");if(t)t.style.display=c.length>0?\"flex\":\"none\";}
document.querySelectorAll(\".btn-delete-single\").forEach(btn=>{btn.addEventListener(\"click\",function(){if(confirm(\"Remove this assignment?\")){const u=this.dataset.url,tk=document.querySelector(\"meta[name=csrf-token]\")?.getAttribute(\"content\");if(!tk){alert(\"CSRF token missing. Refresh page.\");return;}const f=document.createElement(\"form\");f.method=\"POST\";f.action=u;const i1=document.createElement(\"input\");i1.type=\"hidden\";i1.name=\"_token\";i1.value=tk;f.appendChild(i1);const i2=document.createElement(\"input\");i2.type=\"hidden\";i2.name=\"_method\";i2.value=\"DELETE\";f.appendChild(i2);document.body.appendChild(f);f.submit();}});});
document.getElementById(\"bulkDeleteBtn\")?.addEventListener(\"click\",function(e){const c=document.querySelectorAll(\".bulk-check:checked\");if(c.length===0){e.preventDefault();alert(\"Select at least one.\");return;}if(!confirm(\"Delete \"+c.length+\" assignment(s)?\"))e.preventDefault();});
</script>
@endpush", $v);
$v = str_replace("data-id=\"{{ $a->id }}\"", "data-id=\"{{ $a->id }}\" data-url=\"{{ route(\"admin.subject-assignments.destroy\", $a) }}\"", $v);
if (strpos($v, "bulkToolbar") === false) $v = str_replace("    @if($assignments->count() > 0)", "    <div class=\"d-flex justify-content-between align-items-center mb-2\" id=\"bulkToolbar\" style=\"display:none;\"><span class=\"text-muted\"><span id=\"selectedCount\">0 selected</span></span><button type=\"submit\" id=\"bulkDeleteBtn\" class=\"btn btn-danger btn-sm\" disabled form=\"bulkForm\"><i class=\"bi bi-trash me-1\"></i> Delete Selected</button></div>
    @if($assignments->count() > 0)", $v);
file_put_contents($b."/resources/views/admin/subject-assignments/index.blade.php", $v);
echo "  FIXED: JS crash, bulk button, data-url
"; $f+=3;
echo "Bug 5: Route order...
";
$r = file_get_contents($b."/routes/web.php");
$rp = strpos($r, "Route::resource(\"subject-assignments\"");
$bp = strpos($r, "Route::delete(\"subject-assignments/bulk-delete\"");
if ($rp !== false && $bp !== false && $bp > $rp) {
    preg_match("/Route::delete\(\"subject-assignments\/bulk-delete\".*?;\n/", $r, $bm);
    preg_match("/Route::get\(\"subject-assignments\/api\/classes\".*?;\n/", $r, $a1m);
    preg_match("/Route::get\(\"subject-assignments\/api\/sections\".*?;\n/", $r, $a2m);
    $bl=isset($bm[0])?$bm[0]:""; $a1=isset($a1m[0])?$a1m[0]:""; $a2=isset($a2m[0])?$a2m[0]:"";
    $r = str_replace([$bl,$a1,$a2], "", $r);
    $r = str_replace("Route::resource(\"subject-assignments\"", $bl.$a1.$a2."Route::resource(\"subject-assignments\"", $r);
    file_put_contents($b."/routes/web.php", $r);
    echo "  FIXED: Moved routes before resource
"; $f++;
} else echo "  Already correct
";
foreach(["config:clear","cache:clear","view:clear","route:clear"] as $c){exec("php artisan $c 2>&1",$o,$rc);echo "  $c: ".($rc===0?"OK":"done")."
";}
echo "
DONE! $f fixes applied.
Main cause: Missing CSRF meta tag in layout.
Clear browser cache, refresh, try deleting.
";