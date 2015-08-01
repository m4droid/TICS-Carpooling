'use strict'

###*
 # @ngdoc function
 # @name carpoolingApp.controller:UsersCtrl
 # @description
 # # UsersCtrl
 # Controller of the carpoolingApp
###
angular.module 'carpoolingApp'
  .controller 'UsersCtrl', ($scope, $http, API, AuthService) ->
    $scope.load_users = ->
      AuthService.get_session_id()
        .then (session_id) ->
          $http({
            method: 'GET',
            url: API.url + '/users',
            headers: {'Authorization': 'Token token=' + session_id}
          }).success (data, status, headers, config) ->
            $scope.data = data
            return
          return
        return

    $scope.confirm_ban = (action, user_id) ->
      $scope.modal_action = action
      $scope.modal_ban_user = user_id
      return

    $scope.ban_user = (user_id) ->
      AuthService.get_session_id()
        .then (session_id) ->
          $http({
            method: 'POST',
            url: API.url + '/users_banned',
            headers: {'Authorization': 'Token token=' + session_id}
            data: {'user_id': user_id}
          }).success (data, status, headers, config) ->
            $scope.load_users()
            return
          return
        return

    $scope.unban_user = (user_id) ->
      AuthService.get_session_id()
        .then (session_id) ->
          $http({
            method: 'DELETE',
            url: API.url + '/users_banned/?user_id=' + user_id,
            headers: {'Authorization': 'Token token=' + session_id}
          }).success (data, status, headers, config) ->
            $scope.load_users()
            return
          return
        return

    $scope.is_user_banned = (user_id) ->
      if not $scope.data? or not $scope.data.banned_users?
        return false
      find_banned_user = (user, index, array) ->
        return user.id == user_id

      $scope.data.banned_users.some(find_banned_user)

    $scope.load_users()
    return
