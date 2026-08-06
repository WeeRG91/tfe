export default {
    title: 'Recherche globale',
    description: 'Que recherchez-vous ?',
    resultTypes: {
        dishes: 'Plats',
        drinks: 'Boissons',
        ingredients: 'Ingrédients',
        meats: 'Viandes',
        allergens: 'Allergènes',
        roles: 'Rôles',
        users: 'Utilisateurs',
    },
    placeholders: { search: 'Rechercher des plats, boissons, ingrédients...' },
    messages: {
        startTyping: 'Commencez à saisir du texte pour lancer une recherche…',
        noResults: 'Aucun résultat trouvé.',
        resultsFound: '{count} résultat trouvé | {count} résultats trouvés',
    },
    keyboard: {
        navigate: 'pour naviguer',
        select: 'pour sélectionner',
        close: 'pour fermer',
    },
};
