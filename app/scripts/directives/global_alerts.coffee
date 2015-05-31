'use strict'

###*
 # @ngdoc function
 # @name carpoolingApp.directives:globalAlerts
 # @description
 # # globalAlerts
 # Directive of the carpoolingApp
###
angular.module 'carpoolingApp'
  .directive 'globalAlerts', () ->
    restrict: 'E',
    templateUrl: 'views/directives/global_alerts.html'
