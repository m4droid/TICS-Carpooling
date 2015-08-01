'use strict'

###*
 # @ngdoc function
 # @name carpoolingApp.factories:AuthService
 # @description
 # # AuthService
 # Factory of the carpoolingApp
###
angular.module 'carpoolingApp'
  .factory 'AuthService', ($rootScope, $q, $http, API, Session, localStorageService) ->
    login: (username, password) ->
      $q (resolve, reject) ->
        $http.post(API.url + '/sessions', {'username': username, 'password': password})
          .success (data, status, headers, config) ->
            resolve Session.create data
            return
          .error (data, status, headers, config) ->
            reject data
            return
        return
    get_session_id: ->
      this.check_session()
      $q (resolve, reject) ->
        session_data = localStorageService.cookie.get 'SESSION'
        if session_data?
          $rootScope.current_session = Session.create session_data
          resolve $rootScope.current_session.id
        else
          reject()
          return
        return
    is_authenticated: ->
      Session.user_id?
    update_user_data: ->
      Session.save_user_data()
      return
    logout: ->
      $q (resolve, reject) ->
        if not Session.id?
          reject()
          return

        $http(
          method: 'DELETE',
          url: API.url + '/sessions',
          headers: {'Authorization': 'Token token=' + Session.id}
        ).success (data, status, headers, config) ->
          Session.destroy()
          $rootScope.current_session = undefined
          resolve()
          return
        .error (data, status, headers, config) ->
          reject()
          return
        return
    check_session: ->
      if Session.id?
        $http(
          method: 'GET',
          url: API.url + '/sessions',
          headers: {'Authorization': 'Token token=' + Session.id}
        ).success (data, status, headers, config) ->
          if not data.user_id?
            Session.destroy()
            $rootScope.current_session = undefined
          return
        return
