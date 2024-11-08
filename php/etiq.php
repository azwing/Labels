<?php
function cmd($commande)
{
$output=shell_exec($commande);
}

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

if (($open = fopen($_FILES["Liste"]["tmp_name"], "r")) !== false) {
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
cmd("rm /dev/shm/*");

// create an empty half page 500x1460 pixels
cmd("convert -size 500x1460 xc:white /dev/shm/empty.png");

for ($i=0; $i < (count($array)); $i++) {

echo " * ";
$progress++;
if ($progress > $lignes ) {
        echo " demi-page " . $page+1;
        echo "<br>";
        $progress=0;
        }
ob_flush();
flush();  
$compte++;
// generate qrcode
cmd("qrencode -s 3 -m 0 -o /dev/shm/tmp.png " . $array[$i][0]);
cmd("convert -bordercolor white -border 3 /dev/shm/tmp.png /dev/shm/code.png");

// generate ID 
cmd("convert -background white -fill black -font Arial -pointsize 18 -rotate -90 -gravity center -size 69x25 caption:" . $array[$i][0] . " /dev/shm/tmp.png");
cmd("convert -bordercolor white -border 1 /dev/shm/tmp.png /dev/shm/num.png");

//generate text
cmd('convert -background white -fill black -font Arial -pointsize 17 -size 400x69 -gravity west caption:"' . $array[$i][1] . '\n' . $array[$i][2] . '\n' . $array[$i][3] . '" /dev/shm/tmp.png');
cmd("convert -bordercolor white -border 1 /dev/shm/tmp.png /dev/shm/texte.png");

// append all the three picture to form the label
cmd("convert +append /dev/shm/code.png /dev/shm/num.png /dev/shm/texte.png /dev/shm/tmp.png");
cmd("convert -bordercolor black -border 1 /dev/shm/tmp.png /dev/shm/sticker.png");

// merge the labels into all.png to create half a page
if (file_exists("/dev/shm/all.png")) {
        cmd("convert -append /dev/shm/all.png /dev/shm/sticker.png /dev/shm/tmp1.png");
        cmd("mv /dev/shm/tmp1.png /dev/shm/all.png");
        } else {
        cmd("mv /dev/shm/sticker.png /dev/shm/all.png");
        }
if ($compte > $lignes) {
        $page++;
        cmd("mv /dev/shm/all.png /dev/shm/page" . $page . ".png");
        if (($page % 2) == 0) {
                // invert the second half page so that labels are same shape
                cmd("convert /dev/shm/page" . $page . ".png -rotate -180 /dev/shm/inv.png");
                cmd("mv /dev/shm/inv.png /dev/shm/page" . $page . ".png");
                cmd("convert +append /dev/shm/page" . $page-1 . ".png /dev/shm/page" . $page . ".png /dev/shm/Page" . $Page . ".png");
                $Page++;
                }
        $compte=0;
        }
}

// arrived at last page most probably the page is not entirely filled
if ($compte > 0) {
        $page++;
        cmd("mv /dev/shm/all.png /dev/shm/page" . $page . ".png");
        if ($page % 2 == 0) {
                echo "dernière demi-page " . $page ;
                // invert the second half page so that labels are same shape
                cmd("convert /dev/shm/page" . $page . ".png -rotate -180 /dev/shm/inv.png");
                cmd("mv /dev/shm/inv.png /dev/shm/page" . $page . ".png");
                cmd("convert +append /dev/shm/page" . $page-1 . ".png /dev/shm/page" . $page . ".png /dev/shm/Page" . $Page . ".png");
                } else {
                cmd("convert +append /dev/shm/page" . $page . ".png /dev/shm/empty.png /dev/shm/Page" . $Page . ".png");
                }
        }
//cmd("convert /dev/shm/Page* PNG8:/dev/shm/Page*");
cmd("img2pdf --output /dev/shm/sortie.pdf --pagesize A4 --border .5cm:.5cm /dev/shm/Page*");
echo "<br><br><b>Votre fichier est prêt : </b>";
echo "<a href='http://[2a01:e0a:2e1:d260:213d:c3ac:cb8:9214]/etiqd.php'>Fichier étiquettes</a>";;
cmd("rm /dev/shm/*.png");
?>
