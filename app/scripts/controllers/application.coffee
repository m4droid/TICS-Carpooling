'use strict'

###*
 # @ngdoc function
 # @name carpoolingApp.controller:ApplicationCtrl
 # @description
 # # ApplicationCtrl
 # Controller of the carpoolingApp
###
angular.module 'carpoolingApp'
  .controller 'ApplicationCtrl', ($scope, $rootScope, $location, AuthService) ->
    $scope.global_alerts = []

    $scope.is_user_logged_in = ->
      $scope.current_session? and $scope.current_session.id? and $scope.current_session.user_id?

    $scope.login = (username, password) ->
      $scope.alerts = []
      AuthService.login username, password
        .then (user) ->
          $rootScope.current_session = user
          $location.path "/paths"
        , (alerts) ->
          $scope.alerts = alerts

    $scope.logout = ->
      $scope.global_alerts = []
      AuthService.logout()
        .then (status) ->
          $location.path "/"
          return

    $scope.redirect_to_home = ->
      $location.path "/"
      return

    $scope.user_is_admin = ->
      $scope.current_user_data? and $scope.current_user_data.type == 'admin'

    AuthService.check_session()
