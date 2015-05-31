'use strict'

###*
 # @ngdoc function
 # @name carpoolingApp.controller:ProfileCtrl
 # @description
 # # ProfileCtrl
 # Controller of the carpoolingApp
###
angular.module 'carpoolingApp'
  .controller 'ProfileCtrl', ($scope, $rootScope, $routeParams, $http, $location, API, Session, AuthService) ->

    $scope.set_avatar_path = (element) ->
      if $scope.profile_user? and $scope.profile_user.avatar? and $scope.profile_user.avatar.indexOf('data:image') < 0 and $scope.profile_user.avatar.indexOf('http') < 0
        $scope.profile_user.avatar = API.url + '/avatars/' + $scope.profile_user.avatar
      return

    $scope.avatar_changed = (element) ->
      avatar_file = element.files[0]
      reader = new FileReader()
      reader.onload = (event) ->
        $scope.$apply () ->
          $scope.profile_user.avatar = event.currentTarget.result
      if avatar_file?
        reader.readAsDataURL(avatar_file)
      return

    $scope.form_profile_send = () ->
      $scope.alerts = []

      new_profile = angular.copy($scope.profile_user)
      new_profile.dob = moment(new_profile.dob).format('YYYY-MM-DD')

      AuthService.get_session_id()
        .then (session_id) ->
          $http(
            method: 'PUT',
            url: API.url + '/users',
            headers: {'Authorization': 'Token token=' + session_id},
            data: new_profile
          ).success((data, status, headers, config) ->
            $scope.alerts = [{
              'type': 'success',
              'text': 'El perfil ha sido modificado exitosamente.'
            }]
            return
          ).error((data, status, headers, config) ->
            $scope.alerts = data
            return
          )
          return
        return
      return

    $scope.open = (event) ->
      event.preventDefault()
      event.stopPropagation()
      $scope.opened = true;

    if not $scope.is_user_logged_in()
      return

    $scope.profile_user_id = $routeParams.user_id
    if not $scope.profile_user_id?
      $location.path "/profiles/" + $scope.current_session.profile_user_id

    $scope.profile_user = {}
    $scope.is_new_profile = $scope.profile_user.id == $scope.current_session.user_id

    $http({method: 'GET', url: API.url + '/users', params: {id: $scope.profile_user_id}})
      .success (data, status, headers, config) ->
        $scope.profile_user = data
        $scope.set_avatar_path()
        return
    return
