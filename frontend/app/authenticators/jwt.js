import Base from 'ember-simple-auth/authenticators/base';
import RSVP from 'rsvp';

export default Base.extend({
    ajax: Ember.inject.service(),
    session: Ember.inject.service(),

    tokenEndpoint: '/auth/generateToken',
    tokenCheckpoint: '/auth/checkToken',

    restore(data) {
        setupAuthHeader(data.token);

        return new RSVP.Promise((resolve, reject) => {
                this.get('ajax')
                    .request(this.get('tokenCheckpoint'))
                    .then((response) => {
                        if (response.valid) {
                            resolve(data);
                        } else {
                            reject(data);
                        }
                    })
                    .catch((error) => {
                        reject(error);
                    })
                ;
        });
    },
    authenticate(username, password) {
        return new RSVP.Promise((resolve, reject) => {
            this.get('ajax')
                .post(this.get('tokenEndpoint'), {
                    data: {
                        username,
                        password
                    }
                })
                .then(
                    (response) => {
                        setupAuthHeader(response.token);
                        resolve(response);
                    })
                .catch((error) => {
                    if (error.errors && error.errors[0]) {
                        reject(error.errors[0].detail);
                        throw error.errors[0].detail;
                    }
                    reject(error);
                    throw error;
                })
            ;
        });
    },
    invalidate(data) {
    },
});
function setupAuthHeader(token) {
    Ember.$.ajaxSetup({
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
}
