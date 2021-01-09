# Scraper
Simple and very pragmatic Scraper for RetroPie finding the Game Art straight of Google.
I've got tired with all the other scrapers out there that needed an login/token/apikey *(for good reasons offcourse)* or what not.
This is basicly an automation of what i would otherwise manually do.

This script (*and my 4 year old son*) do not care about game metadata all it wants is a box cover image so the player has something visual to choose from.

Not the most elegant scraper in the world, but it works for me and might needs adjustments for you.
It loops over the default RetroPie Rom directory emulators, finding games matching the known game extentions.
Then trough Curl querying Google, and splitting the result on images.
After that it downloads the images one by one until it found an image that has an resultion with a minimum of 180px width.


![](example.gif)

## Setup
SSH into your RetroPie machine

    ssh pi@retropie.local

Install PHP

    sudo apt-get install php

#### Run
    git clone git@github.com:pimstolk/scraper.git
    cd scraper
    php scraper.php


#### Example output:
    Found gba games: 4
    1 / 4 :gba - 2 Games in 1 - Quad Desert Fury + Monster Trucks (USA).gba  Found cover with size:225x225
    2 / 4 :gba - Disney Sports - Motocross (Europe) (En,Fr,De,Es,It).gba******  Found cover with size:487x500
    3 / 4 :gba - Game Boy Advance Video - Sonic X - Volume 1 (USA).gba*  Found cover with size:191x266
    4 / 4 :gba - Motocross Maniacs Advance (U) [!].gba***  Found cover with size:268x266
      
    Found gbc games: 1
    1 / 1 :gbc - Championship Motocross 2001 featuring Ricky Carmichael (U) [C][!].gbc  Found cover with size:342x345
    
    Found nes games: 2
    1 / 2 :nes - Super Mario Bros 2 (E) [!].nes*  Found cover with size:256x359
    2 / 2 :nes - Super Mario Bros 3 (E).nes  Found cover with size:683x1024
    
    Found psx games: 12
    1 / 12 :psx - 2Xtreme (USA).cue*****  Found cover with size:782x768
    2 / 12 :psx - ATV - Quad Power Racing (USA).cue****  Found cover with size:844x1081
    3 / 12 :psx - ATV - Quad Power Racing [NTSC-U] [SLUS-01137].cue  Found cover with size:250x250
    4 / 12 :psx - Championship Motocross Featuring Ricky Carmichael.cue*  Found cover with size:800x783



## Backup your current gamelist and downloaded images.

    tar -czvf backup_gamelist.gz /home/pi/.emulationstation/gamelists/
    tar -czvf backup_downloaded_images.gz /home/pi/.emulationstation/downloaded_images/


## Copy new gamelist and downloaded images

    //Please make sure you have a backup of your gamelist!!! (see above)
    cp -rT ./gamelists/ ~/.emulationstation/gamelists/
    
    cp -rT ./downloaded_images/ ~/.emulationstation/downloaded_images/


