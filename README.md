# Labels
Bash script to create cacti labels with qrcode

This script reads a comma separated file as example shown below:

001-001,P / BQEZNKKXJ:Ethnoplants-92,Pereskiopsis scandens,23/05/23 / Zone 10b : 1,7 à 4,4°C
002-002,S10 / BQEZNKKXJ:Ethnoplants-1134,Trichocereus bridgesii,13/05/23

Unique ID, Type / Order Nr / Vendor , Plant Name, Date / USDA Zone

Then it creates a pdf file with two colum labels.

Example Label view

![label](https://github.com/azwing/Labels/blob/main/Label.png)

This Label shows:
"220" unique ID
"073" Unique ID of the mother plant where seeds where collected
"S57" is the saw ID number
"Graines" Seeds in French
"Perso" stands for personal production otherwise it would be the vendor name
"Gymnocalycium monvillei" is the plant name
"18/08/24" is the saw date 
