'use strict'

###*
 # @ngdoc function
 # @name carpoolingApp.controller:LoginCtrl
 # @description
 # # LoginCtrl
 # Controller of the carpoolingApp
###
angular.module 'carpoolingApp'
  .controller 'LoginCtrl', ($scope) ->

    $scope.form_login_submit = () ->
      $scope.login $scope.username, $scope.password
