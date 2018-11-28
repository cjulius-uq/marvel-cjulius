import Ember from 'ember';

export default Ember.Component.extend({
    actions: {
        toggleFavourite(character) {
            character.toggleProperty('favourite');
            character.save();
        },
        removeFavourite(character) {
            // TODO Add confirmation box
            character.toggleProperty('favourite');
            character.save();
        }
    }
});
