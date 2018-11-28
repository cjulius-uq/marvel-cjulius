import Ember from 'ember';
import UnauthenticatedRouteMixin from 'ember-simple-auth/mixins/authenticated-route-mixin';

export default Ember.Component.extend({
    offset: 0,
    limit: 20,

    store: Ember.inject.service(),
    init: function() {
        this._super();

        this.characters = [];
        this.loadCharacters();
    },
    loadCharacters: function() {
        let options = {
            offset: this.offset,
            limit: this.limit,
        };
        if (this.get('favourites')) {
            options.favourites = true;
        }

        this.get('store').query('character', options).then((characters) => {
            const savedCharacters = this.get('characters');

            savedCharacters.pushObjects(characters.toArray());
            this.set('characters', savedCharacters);
        });
        //this.set('characters', );
    },
    scrollHandler: function(e) {
        // Determine if we're at the bottom of the page
        const scrollPosition = document.documentElement.scrollTop + document.documentElement.offsetHeight;
        const scrollHeight = document.documentElement.scrollHeight;
        if (scrollPosition >= scrollHeight * 0.6) {
            if (this.characters.get('length') >= this.offset + this.limit) {
                this.set('offset', this.offset + this.limit)
                this.loadCharacters();
            }
        }
    },
    didRender: function() {
        window.addEventListener('scroll', this.scrollHandler.bind(this));
    },
    willDestroyElement: function() {
        window.removeEventListener('scroll', this.scrollHandler.bind(this));
    },
});
