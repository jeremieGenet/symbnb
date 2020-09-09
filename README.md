# Maquette de site de location immobilière de vacances (même principe que airbnb)

# Technos principales :
  Symfony et Bootstrap
  
# Description
  Réalisation d'un proto pour agence immobilière 
    - Espace membre (enregistrement, connexion, gestion de profil, multi-permissions)
    - Administration (CRUD des annonces, Statistique des biens, permissions)
    - Annonces (affichages des annonces (pagination), affichage du bien, possibilité de réserver un bien(calendrier de réservation), notation du bien)
    
# Détails:
  Les annonces représentent un bien qui posséde une image principale, un collection d'images, description, titre, note des utilisateur (si celui-ci à déjà été loué).
  Le bien de l'annonce est réservable par un utilisateur connecté, et celui ci peut à travers son profil gérer sa réservation, puis la noté après le séjour.
  Le bien est réservable s'il n'est pas déjà réservé.
  Si le bien est réservé il change de statut et devient non réservable pendant la période, mais reste réservable pour d'autre périodes (gestion du calendrier
  de réservation)
