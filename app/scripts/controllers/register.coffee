'use strict'

###*
 # @ngdoc function
 # @name carpoolingApp.controller:RegisterCtrl
 # @description
 # # RegisterCtrl
 # Controller of the carpoolingApp
###
angular.module 'carpoolingApp'
  .controller 'RegisterCtrl', ($scope, $routeParams, $http, $location, API) ->
    if $scope.is_user_logged_in()
      $location.path "/"
      return

    $scope.is_new_profile = true
    $scope.profile_user = {}
    $scope.max_date = moment().subtract(18, 'years')

    $scope.avatar_changed = (element) ->
      avatar_file = element.files[0]
      reader = new FileReader()
      reader.onload = (event) ->
        $scope.$apply () ->
          $scope.profile_user.avatar = event.currentTarget.result
      if avatar_file?
        reader.readAsDataURL(avatar_file)

    $scope.form_profile_send = () ->
      $scope.alerts = []

      new_profile = angular.copy($scope.profile_user)
      new_profile.dob = moment(new_profile.dob).format('YYYY-MM-DD')

      $http.post(API.url + '/users', new_profile)
        .success (data, status, headers, config) ->
          $scope.login $scope.profile_user.id, $scope.profile_user.password
          return
        .error (data, status, headers, config) ->
          $scope.alerts = data
          return
      return

    $scope.is_user_logged_in = () ->
      true

    $scope.open = (event) ->
      event.preventDefault()
      event.stopPropagation()

      $scope.opened = true;
