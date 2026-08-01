<?php
$f = 'app/Controllers/StudentController.php';
$c = file_get_contents($f);

$c = preg_replace('/\'disability_type\'\s*=>\s*\'required\',/', '', $c);
$c = preg_replace('/\'disability_detail\'\s*=>\s*\'nullable\',/', '', $c);
$c = preg_replace('/\'care_instructions\'\s*=>\s*\'nullable\',/', '', $c);
$c = preg_replace('/\'special_needs_summary\'\s*=>\s*\'nullable\',/', '', $c);
$c = preg_replace('/\'allergies\'\s*=>\s*\'nullable\',/', '', $c);
$c = preg_replace('/\'triggers\'\s*=>\s*\'nullable\',/', '', $c);
$c = preg_replace('/\'medications\'\s*=>\s*\'nullable\',/', '', $c);
$c = preg_replace('/if \(!isset\(\$data\[\'full_name\'\]\) && isset\(\$data\[\'first_name\'\]\)\) \{.*?if \(empty\(\$data\[\'disability_type\'\]\)\) \{.*?\}\s*/s', "if (!isset(\$data['full_name']) && isset(\$data['first_name'])) {\n            \$data['full_name'] = trim((\$data['first_name'] ?? '') . ' ' . (\$data['middle_name'] ?? '') . ' ' . (\$data['last_name'] ?? ''));\n        }\n\n", $c);

file_put_contents($f, $c);
echo "Cleaned StudentController.php fields";
