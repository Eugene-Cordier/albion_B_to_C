# Albion_B_to_C

## Description du projet

Ce projet a été conçu pour les joueurs d'Albion Online qui effectuent des trajets de transport entre les villes royales et cherchent à gagner du temps. L'objectif est de faciliter la prise de décision sur les objets à transporter, en se basant sur des données actualisées.

Le projet fonctionne en deux étapes principales :

- Mise à jour des données : Le joueur doit d'abord utiliser l'Albion Data Client pour récupérer les informations sur les objets, les prix et les distances entre les villes.

- Analyse des données : Une fois les données mises à jour, le projet génère un tableau Excel permettant de visualiser rapidement quels objets sont les plus rentables à transporter entre les différentes villes. Cela permet aux joueurs de maximiser leurs profits tout en économisant du temps.

En automatisant cette analyse, ce projet aide les joueurs à prendre des décisions éclairées et à optimiser leurs trajets, tout en leur permettant de se concentrer davantage sur le jeu.

## Pour utiliser ce projet

pour utiliser le projet, il vous faut installer :

- php 8.2 [download php](https://www.php.net/downloads.php)
- composer 2.6.5 [download composer](https://getcomposer.org/download/)
- symfony 5.7 [download symfony](https://symfony.com/download)
- albion data client [doc project](https://github.com/ao-data/albiondata-client/?tab=readme-ov-file)

## Initialisation du projet

Placez vous dans le projet, ouvrez un treminal puis les commandes suivantes  :
téléchargement des packages :  

- ```composer install```

lancement du serveur local :  

- ```symfony serve```

## Utilisation du projet  

- lancer albion data client en fond, rendez-vous ensuite dans les market qui vous intéressent. lancez une recherche pour les items qui vous intéressent. Cela permet de mettre à jour les prix des items.

- tapez l'url [](http://localhost:8000/transport), cela génère un fichier excel à la racine du projet.
Un exemple result.xlsx est à la racine du projet.

- Pour chaque item il y a la quantité moyenne vendu, le bénéfice par item en colonne C et G pour des transports préparés (ordres d'achat) et pour les transports exceptionnels

- vous pouvez changer le nom du fichier généré, vos routes, villes et items dans le fichier src/Controller/TransportController.php lignes 15-52

## Problèmes possibles

- Vous ne pouvez pas générer 2 tableurs avec le même non au même endroit. Avant de générer un fichier, déplacer celui existant autre part ou changez le nom dans src/Controller/TransportController.php ligne 15

- Il est possible qu'il y ait des données manquantes ou non mises à jour. Pour éviter cela mettez les à jour à l'aide du albion data client.
