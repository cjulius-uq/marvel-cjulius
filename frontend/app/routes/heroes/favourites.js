import Ember from 'ember';

export default Ember.Route.extend({
    actions: {
        didTransition() {
            Ember.run.scheduleOnce('afterRender', this, function() {
                $('#profileModal').foundation('reveal', 'open');

            });
        }
    }
});

