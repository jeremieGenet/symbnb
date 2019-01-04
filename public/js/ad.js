
/* JAVASCRIPT OU PLUTOT JQUERY QUI GERE NOS ANNONCES 

    SUPPRESSIION DES IMAGES DE LA COLLECTION D'IMAGE
*/

// Je récupère le boutton id = add-image (celui Ajouter une image), et lorsqu'on click dessus (évenement) alors...
$('#add-image').click(function(){
    // Je récupère le numéro des futurs champs que je vais créer (On créer une constante de type number)
    const index = +$('#widgets-counter').val(); // On définit en fait le nb de 'div' de classe 'form-group' dans la div de id = annonce_images
                                    // le "+" devant l'expression permet de récup une valeur de type number (sinon on concatènerait des chaines de caractères)
                                    // (.val() permet de recupérer le valeur)
    //console.log(index); // Affiche dans la console un nombre qui s'incrémente à chaque appuie sur le bouton "Ajouter une image"
    // Je récupère le prototype des entrées (On créer une constante de type number)
    const tmpl = $('#annonce_images').data('prototype').replace(/__name__/g, index); // On va chercher dans la div id=annonce_image ...
    // ... la donnée data-prototype (voir inspecteur html) et on la remplace par une regex qui dit : à chaque __name__ ("g" pour dire qu'il y en a plusieurs) je remplace par ma constante index (Ainsi le nombre sera définit)
    // console.log(tmpl); // Affiche dans la console, quand on appuie sur le boutton le contenu de la div avec le data-prototype

    // J'injecte ce code au sein de la div
    $('#annonce_images').append(tmpl);   

    $('#widgets-counter').val(index + 1); // Ici on incrémente le compteur de notre input caché

    // Je gère le bouton supprimer
    handleDeleteButtons();
});

// Fonction qui va gérer la suppression d'une entry (url + caption) quand on appuie sur le bouton avec un "X"
function handleDeleteButtons(){
    // On récupère les boutons qui ont l'attribut data-action="delete" (on en a un seul, celui en rouge avec le "X"), et lorsqu'on click dessus alors...
    $('button[data-action="delete"]').click(function(){
        const target = this.dataset.target; // On stock dans la constante target (this ici représente le bouton sur lequel on click,
        // dataset représente tout les attributs data-quelquechose et target pour accéder à l'attribut data-target)
        //console.log(target); // Affiche: #block_annonce_image_0 ou 1 ou 2 ... soit l'id de l'entry (url + caption) et du bouton avec le "X"
        $(target).remove(); // Puis on supprime la div qui posséde l'id target (#block_annonce_image_0 ou 1 ou 2 ...) soit l'entry et le bouton
    })
}

// Fonction qui permet de mettre à jour le compteur (de notre input caché qui se trouve dans le fichier '_collection.html.twig')
function updateCounter(){
    const count = +$('#annonce_images div.form-group').length;
    $('#widgets-counter').val(count);
}

updateCounter(); // On met à jour notre compteur
handleDeleteButtons();