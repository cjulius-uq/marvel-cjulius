import Ember from 'ember';
import config from './config/environment';

const Router = Ember.Router.extend({
  location: config.locationType,
  rootURL: config.rootURL
});

Router.map(function() {
    this.route('login', { path: '/' });
});

Router.map(function() {
    this.route('heroes', function() {
        this.route('profile', { path: '/profile/:friendlyName' });
    });
    this.route('favourites', { path: '/heroes/favourites' });
});

export default Router;
