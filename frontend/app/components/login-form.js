import Ember from 'ember';
import UnauthenticatedRouteMixin from 'ember-simple-auth/mixins/authenticated-route-mixin';

export default Ember.Component.extend({
    session: Ember.inject.service(),
    actions: {
        login(username, password, callback) {
            this.get('session')
                .authenticate('authenticator:jwt', username, password)
                .catch((error) => {
                    this.set('error', error.message);
                }).then(() => {
                    callback();
                })
            ;
        }
    }
});
