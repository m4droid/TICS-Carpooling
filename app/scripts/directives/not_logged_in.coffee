'use strict'

###*
 # @ngdoc function
 # @name carpoolingApp.directives:notLoggedIn
 # @description
 # # notLoggedIn
 # Directive of the carpoolingApp
###
angular.module 'carpoolingApp'
  .directive 'notLoggedIn', () ->
    restrict: 'E',
    templateUrl: 'views/directives/not_logged_in.html'
