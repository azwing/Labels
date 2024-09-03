# Labels

# Bash script

Bash script to create cacti labels with qrcode

This script reads a comma separated file as example shown below:

001-001,P / BQEZNKKXJ:Ethnoplants-92,Pereskiopsis scandens,23/05/23 / Zone 10b : 1,7 à 4,4°C
002-002,S10 / BQEZNKKXJ:Ethnoplants-1134,Trichocereus bridgesii,13/05/23

Unique ID, Type / Order Nr / Vendor , Plant Name, Date / USDA Zone

Then it creates a pdf file with two colum labels.

Example Label view

![label](https://github.com/azwing/Labels/blob/main/Label.png)

This Label shows:<br>
"220" unique ID<br>
"073" Unique ID of the mother plant where seeds where collected<br>
"S57" is the saw ID number<br>
"Graines" Seeds in French<br>
"Perso" stands for personal production otherwise it would be the vendor name<br>
"Gymnocalycium monvillei" is the plant name<br>
"18/08/24" is the saw date <br>

# dependencies
sudo apt install imagemagick qrencode

# Web interface
Works under linux and makes usage of virtual filesystem for most of the poperations (/dev/shm/)
Under php there is code that can be installed on an Apache server with php.

Only point to edit is the [yourserver] URL at bottom od etiq.php near
