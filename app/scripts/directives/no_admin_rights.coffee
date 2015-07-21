'use strict'

###*
 # @ngdoc function
 # @name carpoolingApp.directives:noAdminRights
 # @description
 # # noAdminRights
 # Directive of the carpoolingApp
###
angular.module 'carpoolingApp'
  .directive 'noAdminRights', () ->
    restrict: 'E',
    templateUrl: 'views/directives/no_admin_rights.html'
