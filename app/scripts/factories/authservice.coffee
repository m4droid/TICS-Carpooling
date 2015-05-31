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
            reject [
              'text': 'Usuario/password invalidos',
              'type': 'danger'
            ]
            return
        return
    get_session_id: () ->
      $q (resolve, reject) ->
        session_data = localStorageService.cookie.get 'SESSION'
        if session_data?
          $rootScope.current_session = Session.create session_data
          resolve $rootScope.current_session.id
        else
          reject()
          return
        return
    is_authenticated: () ->
      Session.user_id?
    update_user_data: () ->
      Session.save_user_data()
      return
    logout: () ->
      $q (resolve, reject) ->
        $http.delete(API.url + '/sessions')
          .success (data, status, headers, config) ->
            Session.destroy()
            $rootScope.current_session = undefined
        resolve()
