<?php

declare(strict_types=1);

return [
    'actions' => [
        'show'      => 'voir',
        'edit'      => 'éditer',
        'save'      => 'sauvegarder',
        'delete'    => 'supprimer',
        'create'    => 'créer',
        'duplicate' => 'dupliquer',
    ],
    'actions_model' => [
        'show'      => 'Voir :model',
        'edit'      => 'Éditer :model',
        'save'      => 'Sauvegarder :model',
        'delete'    => 'Supprimer :model',
        'create'    => 'Créer :model',
        'duplicate' => 'Dupliquer :model',
        'list_all'  => 'Lister l\'intégralité des :model',
    ],
    'libs' => [
        'toolbox' => [
            'assertFieldIsUnique' => 'La :valeur du champ :attribut est déjà utilisée',
        ]
    ],
    'messages'   => [
        'has_been_created'                => ':model a été créé',
        'cannot_be_created'               => ':model n\'a pas été créé',
        'has_been_updated'                => ':model a été mis à jour',
        'cannot_be_updated'               => ':model n\'a pas été mis à jour',
        'has_been_deleted'                => ':model a été supprimé',
        'cannot_be_deleted_with_children' => ':model n\'a pas été supprimé',
        'cannot_event_on_model'           => 'Impossible de :event un nouveau :model|Impossible de
            :event une nouvelle :model',
        'order_changed'                   => 'L\'ordre a été modifié !',
        'order_not_changed'               => 'L\'ordre n\'a pas été modifié !',
        'publish_status_saved'            => 'Changement d\'état de publication réussi !',
        'right'                           => 'Vous n\'avez pas les droits d\'accès à cette partie !',
        'theme_updated'                   => 'Le thème a été mis à jour !',
        'lang_updated'                    => 'La langue a été mis à jour !',
        'translation_default_required'    => 'La traduction par défaut (:fallbackLocale) est obligatoire
            lors de la publication !',
        'translation_restrict'            => 'Traduction restreinte aux dossiers obligatoires !',
    ],
    'search' => [
        'label'         => 'Rechercher :elements',
        'keywords'      => 'Vos mots-clés ici...',
        'apply_search'  => 'Appliquer une recherche',
        'remove_search' => 'Supprimer la recherche'
    ],
    'filter' => [
        'sort_ascending'  => 'Trier :name dans l\'ordre croissant',
        'sort_descending' => 'Trier :name dans l\'ordre décroissant',
        'sort_delete'     => 'Supprimer le tri',
        'sort_arrow'      => 'Ici, les flèches sont utilisées pour trier et non pour filtrer',
    ],
    'sweetalert' => [
        'send'           => 'Oui, envoyer !',
        'confirm'        => 'Oui, supprimer !',
        'cancel'         => 'Non, annuler',
        'are_you_sure'   => 'Êtes-vous sûr ?',
        'data_lost'      => 'Toutes les données seront perdues.',
        'delete_element' => 'L\'élément <strong>:modelName</strong> sera supprimé.',
        'send_email'     => 'L\'utilisateur <strong>:modelName</strong> recevra un e-mail
            de réinitialisation du mot de passe.',
    ],
    'meta' => [
        'all_models'          => 'Intégralité des :model',
        'all_models_list'     => 'Lister l\'intégralité des :model.',
        'creation_model'      => 'Création :model',
        'creation_model_desc' => 'Form for entering information to create a new :model',
        'edition_model'       => 'Édition :model',
        'edition_model_desc'  => 'Modification des informations :model précédemment enregistrées',
        'view_model'          => 'Aperçu :model',
        'view_model_desc'     => 'Aperçu des informations :model précédemment enregistrées',
    ],
    'other' => [
        'no_model_found'  => 'Aucun résultat (:model) trouvé',
        'up'              => 'Modifier l\'ordre d\'apparition vers le haut',
        'down'            => 'Modifier l\'ordre d\'apparition vers le bas',
        'user-right'      => 'Vous n\'avez pas les droits',
        'publish'         => 'Publier',
        'unpublish'       => 'Dépublier',
        'required_fields' => '* Champs obligatoires',
        'access_link'     => 'Ouvrir dans un nouvel onglet',
    ],
];
