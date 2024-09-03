<?php
// Vérifier si le formulaire a été soumis
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Vérifie si le fichier a été uploadé sans erreur.
    if(isset($_FILES["Liste"]) && $_FILES["Liste"]["error"] == 0){
        $allowed = array("csv" => "text/csv");
        $filename = $_FILES["Liste"]["name"];
        $filetype = $_FILES["Liste"]["type"];
        $filesize = $_FILES["Liste"]["size"];

        // Vérifie l'extension du fichier
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)) die("Erreur : Veuillez sélectionner un format de fichier valide.");

        // Vérifie la taille du fichier - 5Mo maximum
        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize) die("Error: La taille du fichier est supérieure à la limite autorisée.");

        // Vérifie le type MIME du fichier
        if(in_array($filetype, $allowed)){
            // Vérifie si le fichier existe avant de le télécharger.
                if(file_exists("/tmp/" . $_FILES["Liste"]["name"])){
                
                echo $_FILES["Liste"]["name"] . " existe déjà.";
                } else{

                $upload_dir="/var/tmp/";
                $resultat=move_uploaded_file($_FILES["Liste"]["tmp_name"], "/tmp/monfichier.csv");
                $output = shell_exec('chmod 755 /tmp/monfichier.csv');

                echo "Votre fichier a été téléchargé avec succès.";
            } 
        } else{
            echo "Error: Il y a eu un problème de téléchargement de votre fichier. Veuillez réessayer."; 
        }
    } else{
        echo "Error: " . $_FILES["Liste"]["error"];
    }
}
echo '<br><b>votre pdf est en cours de création, veuillez patienter</b><br>';

if (($open = fopen("/tmp/monfichier.csv", "r")) !== false) {
    while (($data = fgetcsv($open, 0, ","," ","")) !== false) {
        $array[] = $data;
    }
 
    fclose($open);
}

echo " votre fichier est en cours de création <br>";
$i=0;
$c=0;
$compte=0;
$page=0;
$Page=0;
$lignes=19;
$progress=0;

// on s'assure que le répertoir est vide
$commande="rm /dev/shm/*";
$output = shell_exec($commande);


// create an empty half page 500x1460 pixels
$commande="convert -size 500x1460 xc:white /dev/shm/empty.png";
$output = shell_exec($commande);

for ($i=0; $i < (count($array)); $i++) {

echo "=";
$progress++;
if ($progress == 19) {
	echo "page " . $page+1;
	echo "<br>";
	$progress=0;
	}
ob_flush();
flush();  
$compte++;
// generate qrcode
$commande="qrencode -s 3 -m 0 -o /dev/shm/tmp.png " . $array[$i][0];
$output = shell_exec($commande);
$commande="convert -bordercolor white -border 3 /dev/shm/tmp.png /dev/shm/code.png";
$output = shell_exec($commande);

// generate ID 
$commande="convert -background white -fill black -font Arial -pointsize 18 -rotate -90 -gravity center -size 69x25 caption:" . $array[$i][0] . " /dev/shm/tmp.png";
$output = shell_exec($commande);
$commande="convert -bordercolor white -border 1 /dev/shm/tmp.png /dev/shm/num.png";
$output = shell_exec($commande);

//generate text
$commande='convert -background white -fill black -font Arial -pointsize 18 -size 400x69 -gravity west caption:"' . $array[$i][1] . '\n' . $array[$i][2] . '\n' . $array[$i][3] . '" /dev/shm/tmp.png';
$output = shell_exec($commande);
$commande="convert -bordercolor white -border 1 /dev/shm/tmp.png /dev/shm/texte.png";
$output = shell_exec($commande);

// append all the three picture to from the label
$commande="convert +append /dev/shm/code.png /dev/shm/num.png /dev/shm/texte.png /dev/shm/tmp.png";
$output = shell_exec($commande);

$commande="convert -bordercolor black -border 1 /dev/shm/tmp.png /dev/shm/sticker.png";
$output = shell_exec($commande);

// merge the labels into all.png to create half a page
if (file_exists("/dev/shm/all.png")) {
	$commande="convert -append /dev/shm/all.png /dev/shm/sticker.png /dev/shm/tmp1.png";
	$output = shell_exec($commande);
        $commande="mv /dev/shm/tmp1.png /dev/shm/all.png";
        $output = shell_exec($commande);
	} else {
        $commande="mv /dev/shm/sticker.png /dev/shm/all.png";
        $output = shell_exec($commande);
	}
if ($compte > $lignes) {
	$page++;
	$commande="mv /dev/shm/all.png /dev/shm/page" . $page . ".png";
        $output = shell_exec($commande);
	if (($page % 2) == 0) {
		$commande="convert +append /dev/shm/page" . $page-1 . ".png /dev/shm/page" . $page . ".png /dev/shm/Page" . $Page . ".png";
        	$output = shell_exec($commande);
		$Page++;
		}
	$compte=0;
	}
}
if ($compte > 0) {
	$page++;
	$commande="mv /dev/shm/all.png /dev/shm/page" . $page . ".png";
        $output = shell_exec($commande);
	if ($page % 2 == 0) {
		$commande="convert +append /dev/shm/page" . $page-1 . ".png /dev/shm/page" . $page . ".png /dev/shm/Page" . $Page . ".png";
                $output = shell_exec($commande);
		} else {
		$commande="convert +append /dev/shm/page" . $page . ".png /dev/shm/empty.png /dev/shm/Page" . $Page . ".png";
     	        $output = shell_exec($commande);
		}
	}
$commande="img2pdf --output /dev/shm/sortie.pdf --pagesize A4 --border .5cm:.5cm /dev/shm/Page*";
$output = shell_exec($commande);

$commande="rm /dev/shm/*.png";
$output = shell_exec($commande);
echo "<br><br><b>Votre fichier est prêt : </b>";
// todo edit the link below to point to your server
echo "<a href='http://[yourserver]/etiqd.php'>Fichier étiquettes</a>";;

?>
