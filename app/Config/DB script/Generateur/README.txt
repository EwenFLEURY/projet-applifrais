Ce dossier contient un générateur de données qui va alimenter automatiquement la base de données gsb_frais avec 
des fiches de frais aléatoires pour chacun des visiteurs existants.

Pour procéder à la génération, il faut : 
- Avoir mis en place la base de données gsb_frais 
- Vider chacune des tables concernées par le générateur (FicheFrais, LignesFraisForfait et LignesFraisHorsForfait)
- Ouvrir avec un éditeur de texte le fichier majGsb_main.php
- Remplacer les valeurs de la connexion à la base avec vos paramètres
- Copier le présent dossier (Generateur) dans le répertoire de publication de votre serveur WEB
- Lancer l'interprétation du script majGsb_main.php avec le navigateur
- Supprimer le dossier Generateur du répertoire de publication