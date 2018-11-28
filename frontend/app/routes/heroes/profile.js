import Ember from 'ember';
import Character from '../../models/character'

export default Ember.Route.extend({
    model(params) {
        return this.get('store').queryRecord('character', {friendlyName: params.friendlyName});
        /*return Character.create({
            name: 'A-Bomb (HAS)',
            friendlyName: 'a_bomb',
            thumbnail: 'http://i.annihil.us/u/prod/marvel/i/mg/3/20/5232158de5b16.jpg',
            favourite: false,
            description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
            numComics: 241,
            numStories: 52,
            numSeries: 51,
            numEvents: 532,
        });*/

    },
    actions: {
        didTransition() {
            Ember.run.scheduleOnce('afterRender', this, function() {
                // Reflow after images load
                $('#profileModal img').on('load', () => {
                    Ember.$(document).foundation('equalizer', 'reflow');
                    console.log('load');
                });
                // Reflow after modal opens
                $(document).on('opened.fndtn.reveal', '[data-reveal]', function() {
                    Ember.$(document).foundation('equalizer', 'reflow');
                    console.log('opened');
                });

                $(document).on('closed.fndtn.reveal', '[data-reveal]', (e) => {
                    if (e.target.id === 'profileModal') {
                        this.transitionTo('heroes');
                    }
                });
                $('#profileModal').foundation('reveal', 'open');

            });
        }
    }
});

