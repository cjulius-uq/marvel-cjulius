import Ember from 'ember';

export default DS.Model.extend({
    name: DS.attr('string'),
    description: DS.attr('string'),
    favourite: DS.attr('boolean'),
    thumbnailUrl: DS.attr('string'),
    friendlyName: DS.attr('string'),
    numComics: DS.attr('number'),
    numEvents: DS.attr('number'),
    numSeries: DS.attr('number'),
    profileUrl: Ember.computed('friendlyName', function() {
        return `/heroes?hero=${this.get('friendlyName')}`;
    }),
});
