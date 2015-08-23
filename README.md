# learning-ps-webservice

Exemple d'ajout d'un produit avec catégorie, stock et image vers le webservice Prestashop.
Fichiers source du tutorial http://www.phildl.com/webservice-prestashop-ajout-dun-produit/

## Installation

```
git clone https://github.com/PhilDL/learning-ps-webservice
cd learning-ps-webservice
composer install
```

## Utilisation

N'oubliez pas de *modifier* index.php pour y entrer l'URL de votre boutique Prestashop et votre clé API.

lancez un serveur php

```
php -S localhost:8000
```

et rendez vous à http://localhost:8000 pour envoyer votre produit et voir l'output de résultat xml.