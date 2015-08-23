<?php
require 'vendor/autoload.php';

$url = 'http://www.votreprestashop.com'; //remplacez par votre URL
$key  = 'VOTRESUPERCLEAPICONFIGUREDANSPRESTASHOP'; //remplacez par votre Clé API Prestashop
$debug = true;

$webService = new PrestaShopWebservice($url, $key, $debug);   

try {
    /* 
     |
     |  Ajout de la Catégorie
     |
     */
    
    //préparation catégoire à envoyer
    $xml = $webService->get(array('url' => $url.'/api/categories?schema=blank'));

    //récupération node category
    $category = $xml->children()->children();

    $category->name->language[0][0] = "Ma catégorie";
    $category->name->language[0][0]['id'] = 1;
    $category->name->language[0][0]['xlink:href'] = $url . '/api/languages/' . 1;

    $category->link_rewrite->language[0][0] = "ma-categorie";
    $category->link_rewrite->language[0][0]['id'] = 1;
    $category->link_rewrite->language[0][0]['xlink:href'] = $url . '/api/languages/' . 1;

    $category->id_parent = 2; //Accueil
    $category->active = 1;

    //Envoie des données
    $opt = array('resource' => 'categories');
    $opt['postXml'] = $xml->asXML();
    $xml = $webService->add($opt);      

    //on récupère l'id de la nouvelle catégorie insérée
    $ps_category_id = $xml->category->id; 

    /* 
     |
     |  Ajout du Produit 
     |
     */
    $xml = $webService->get(array('url' => $url.'/api/products?schema=blank'));

    $product = $xml->children()->children();

    $product->price = 99; //Prix TTC
    $product->wholesale_price = 89; //Prix d'achat
    $product->active = '1';
    $product->on_sale = 0; //on ne veux pas de bandeau promo
    $product->show_price = 1;
    $product->available_for_order = 1;

    $product->name->language[0][0] = "Produit webservice";
    $product->name->language[0][0]['id'] = 1;
    $product->name->language[0][0]['xlink:href'] = $url . '/api/languages/' . 1;

    $product->description->language[0][0] = "Description produit webservice";
    $product->description->language[0][0]['id'] = 1;
    $product->description->language[0][0]['xlink:href'] = $url . '/api/languages/' . 1;

    $product->description_short->language[0][0] = "Descr. courte";
    $product->description_short->language[0][0]['id'] = 1;
    $product->description_short->language[0][0]['xlink:href'] = $url . '/api/languages/' . 1;
    $product->reference = "ref_product_webservice";
    //On va gérer le stock ensuite
    $product->depends_on_stock = 0; 
    //Association avec notre catégorie créée auparavant
    $product->associations->categories->addChild('category')->addChild('id', $ps_category_id);
    $product->id_category_default = $ps_category_id;

    //envoi du produit
    $opt = array('resource' => 'products');
    $opt['postXml'] = $xml->asXML();
    $xml = $webService->add($opt);      

    //on récupère l'id du produit inséré
    $ps_product_id = $xml->product->id; 

    /* 
     |
     |  Ajout du stock Produit 
     |
     */
    $opt['resource'] = 'products';
    $opt['id'] = $ps_product_id;
    $xml = $webService->get($opt);
    //(les "stock_availables ont créé automatiquement par PS à l'insert du produit précédent)
    foreach ($xml->product->associations->stock_availables->stock_available as $stock) {

        $xml2 = $webService->get(array('url' => $url . '/api/stock_availables?schema=blank'));
        $stock_availables = $xml2->children()->children();
        $stock_availables->id = $stock->id;
        $stock_availables->id_product  = $ps_product_id;
        $stock_availables->quantity = 50;
        $stock_availables->id_shop = 1;
        $stock_availables->out_of_stock = 1;
        $stock_availables->depends_on_stock = 0;
        $stock_availables->id_product_attribute = $stock->id_product_attribute;

        //POST des données vers la ressource 
        $opt = array('resource' => 'stock_availables');
        $opt['putXml'] = $xml2->asXML();
        $opt['id'] = $stock->id ;
        $xml2 = $webService->edit($opt);
    }

    /* 
     |
     |  Ajout de l'image Produit 
     |
     */
    $urlImage = $url.'/api/images/products/'.$ps_product_id.'/';
    //entrez ici le chemin de l'image complet pour votre fichier
    $image_path = 'public/img/product_webservice.png';
    $image_mime = image_type_to_mime_type(exif_imagetype($image_path));

    $args['image'] = new CurlFile($image_path, $image_mime);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
    curl_setopt($ch, CURLOPT_URL, $urlImage);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, $key.':'); //API KEY Prestashop     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
    $result = curl_exec($ch);
    curl_close($ch);
    
} catch (PrestaShopWebserviceException $e) {
    $trace = $e->getTrace();
    if ($trace[0]['args'][0] == 404) echo 'Bad ID';
    else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
    else echo $e->getMessage();
}  