export default {
    title: 'Global Search',
    description: 'What are you looking for?',
    resultTypes: {
        dishes: 'Dishes',
        drinks: 'Drinks',
        ingredients: 'Ingredients',
        meats: 'Meats',
        allergens: 'Allergens',
        roles: 'Roles',
        users: 'Users',
    },
    placeholders: { search: 'Search dishes, drinks, ingredients...' },
    messages: {
        startTyping: 'Start typing to search…',
        noResults: 'No results found.',
        resultsFound: 'Found {count} result | Found {count} results',
    },
    keyboard: {
        navigate: 'to navigate',
        select: 'to select',
        close: 'to close',
    },
};
