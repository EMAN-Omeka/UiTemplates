# UiTemplates

Ce plugin est une version simplifiée de celui utilisé pour le projet Eman. 

Il permet de gérer l'affichage des pages items via une interface graphique, sans avoir à retoucher les fichiers de thème.

Pré-requis : pour profiter de la présentation en deux colonnes, le thème doit utiliser le tag "aside".

------

Ce plugin vise :

- à factoriser autant que possible les personnalisations
- à les rendre paramétrables via l’IHM
- à s’affranchir des fichiers du thème

Pour ce faire, le plugin propose une interface avec tous les paramètres utiles (titres, champs à afficher dans un bloc, ordre, colonnes, etc.). Les valeurs de ces paramètres sont stockées en base de données, et peuvent donc être différentes pour chaque projet.

En terminologie Zend : nous avons déporté un certain nombre de paramètres des pages de la Vue vers le Contrôleur ; les fichiers du niveau Vue sont donc les mêmes pour tous les projets ; seules les données et leur structure (Modèle et Contrôleur, donc) sont différentes d’un projet à l’autre.

De plus, le plugin utilise ses propres fichiers Vue, afin d’exploiter au maximum la logique qu’il implémente. 

Donc, les anciens fichiers du thème ne sont plus utilisés quand l’option « remplacer xxx/show » est active.

Ce qui signifie que tout changement dans ces fichiers sera sans effet tant que l’option sera active.

Améliorations possibles :

- Interface 'drag and drop' pour la configuration des pages.
- ...
