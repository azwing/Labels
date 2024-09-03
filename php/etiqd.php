<?php
$filename="/dev/shm/sortie.php";
// presentation du pdf
header('Content-type: application/pdf');
//  on va le nommer etiquettes.pdf
header('Content-Disposition: attachment; filename="etiquettes.pdf"');
// emplacement du fichier
readfile('/dev/shm/sortie.pdf');

?>

