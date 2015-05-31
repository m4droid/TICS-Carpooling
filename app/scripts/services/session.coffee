'use strict'

###*
 # @ngdoc function
 # @name carpoolingApp.service:Session
 # @description
 # # Session
 # Service of the carpoolingApp
###
angular.module 'carpoolingApp'
  .service 'Session', ($http, $rootScope, API, localStorageService) ->       
    this.create = (session_data) ->
      this.id = session_data.id
      this.user_id = session_data.user_id
      localStorageService.cookie.set 'SESSION', this

      if not $rootScope.current_user_data?
        $http({method: 'GET', url: API.url + '/users', params: {'id': this.user_id}})
          .success (data, status, headers, config) ->
            $rootScope.current_user_data = data
            return
      return this
    this.destroy = () ->
      localStorageService.cookie.remove 'SESSION'
      this.id = undefined
      this.user_id = undefined
      $rootScope.current_user_data = undefined
      return
    return
