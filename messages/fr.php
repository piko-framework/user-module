<?php

$confirmationMailBody =  <<<MSG
Bonjour,

Merci de vous être inscrit sur {site_name}. Votre compte a été créé et doit être activé avant que vous puissiez l'utiliser.
Pour l'activer, cliquez sur le lien ci-dessous ou copiez et collez le dans votre navigateur :

{link}

Après activation vous pourrez vous connecter sur {base_url} en utilisant l'identifiant suivant et le mot de passe utilisé à l'enregistrement :
Identifiant : {username}
MSG;

$resetPasswordMailBody = <<<MSG
Bonjour,

Une demande de changement de mot passe a été effectuée pour votre compte sur {site_name}.

Votre identifiant est : {username}.

Pour changer votre mot de passe , cliquez sur le lien ci-dessous.

{link}

Merci.
MSG;

return [
    'Users' => 'Utilisateurs',
    'Name' => 'Nom',
    'Username' => 'Identifiant',
    'Email' => 'Email',
    'Password' => 'Mot de passe',
    'Last login at' => 'Dernière connexion',
    'Created at' => 'Créé le',
    'Id' => 'Id',
    'Roles' => 'Rôles',
    'Role name' => 'Nom du rôle',
    'Role name must be filled in.' => 'Le nom du role doit être renseigné.',
    'Role already exists.' => 'Le rôle existe déjà.',
    'Description' => 'Description',
    'Role permissions' => 'Permissions du rôle',
    'Users management' => 'Gestion des utilisateurs',
    'Are you sure you want to perform this action?' => 'Êtes-vous certain de vouloir effectuer cette action ?',
    'Create user' => 'Nouvel utilisateur',
    'Edit user' => 'Modifier l\'utilisateur',
    'New role' => 'Nouveau rôle',
    'Delete' => 'Supprimer',
    'Close' => 'Fermer',
    'Cancel' => 'Annuler',
    'Permissions' => 'Permissions',
    'New permission' => 'Nouvelle permission',
    'Permission name' => 'Nom de la permission',
    'Permission name must be filled in.' => 'Le nom de la permission doit être renseigné.',
    'Permission already exists.' => 'La permission existe déjà',
    'User successfully saved' => 'Utilisateur correctement enregistré',
    'Save' => 'Enregistrer',
    'Save error!' => 'Erreur lors de l\'enregistrement',
    'Email must be filled in.' => 'L\'email doit être renseigné.',
    '{email} is not a valid email address.' => '{email} n\'est pas une adresse email valide.',
    'Username must be filled in.' => 'Le nom d\'utilisateur doit être renseigné.',
    'The username should only contain alphanumeric characters.' => 'Le nom d\'utilisateur ne doit contenir que des caractères alphanumériques.',
    'This email is already used.' => 'Cet email est déjà utilisé.',
    'This username is already used.' => 'Cet identifant est déjà utilisé.',
    'Password must be filled in.' => 'Le mot de passe doit être renseigné.',
    'Password is to short. Minimum {num}: characters.' => 'Mot de passe trop court. Minimum {num} caractères.',
    'Passwords are not the same.' => 'Les mots de passe ne sont pas identiques.',

    // Register Account
    'Your account was created. Please activate it through the confirmation email that was sent to you.' => 'Votre compte a été créé. Merci de l\'activer via le mail de confirmation qui vous a été envoyé.',
    'confirmation_mail_body' => $confirmationMailBody,
    'Registration confirmation on {site_name}' => 'Confirmation de l\'inscription sur {site_name}',
    // Account activation
    'Your account has been activated. You can now log in.' => 'Votre compte a bien été activé. Vous pouvez désormais vous connecter.',
    'Unable to activate your account. Please contact the site manager.' => 'Impossible d\'activer votre compte. Merci de contacter le responsable du site.',
    'Your account has already been activated.' => 'Votre compte a déjà été activé.',
    // Password reset / reminder
    'A link has been sent to you by email ({email}). It will allow you to recreate your password.' => 'Un lien vous a été envoyé par email ({email}). Il vous permettra de recréer votre mot de passe.',
    'reset_password_mail_body' => $resetPasswordMailBody,
    'Password change request on {site_name}' => 'Demande de changement de mot de passe sur {site_name}',
    'Account not found.' => 'Compte innexistant',
    'Your password has been successfully updated.' => 'Votre mot de passe a bien été modifié.',
    'Forget password' => 'Mot de passe oublié',
    'Your email or your username' => 'Votre email ou votre identifiant',
    'Send' => 'Envoyer',
    'Change your account ({account}) password' => 'Réinitialisation du mot de passe pour le compte : {account}',
    // Edit account
    'You must be logged to access this page.' => 'Vous devez vous connecter pour accéder à cette page.',
    'Changes saved!' => 'Modifications enregistrées !',
    'Edit your account' => 'Modification de votre compte',
    'Password (leave blank to keep the same)' => 'Mot de passe (laisser vide pour garder le même)',
    'Last name' => 'Nom',
    'First name' => 'Prénom',
    'Company' => 'Entreprise',
    'Phone number' => 'Téléphone',
    'Address' => 'Adresse',
    'Zip code' => 'Code postal',
    'City' => 'Ville',
    'Country' => 'Pays',

    // Login
    'Authentication failure' => 'Échec de l\'authentification.',
    'Login' => 'Connexion',
    'No account yet?' => 'Pas encore de compte ?',
    'Register' => 'Créer un compte',
    'Forget password?' => 'Mot de passe oublié ?',
    // register
    'Confirm your password' => 'Confirmez votre mot de passe'

];
