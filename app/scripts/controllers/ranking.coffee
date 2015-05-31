'use strict'

###*
 # @ngdoc function
 # @name carpoolingApp.controller:RankingCtrl
 # @description
 # # RankingCtrl
 # Controller of the carpoolingApp
###
angular.module 'carpoolingApp'
  .controller 'RankingCtrl', ($scope, $http, API) ->

    $scope.avatars_path = API.url + '/avatars/'
    $scope.ranking = []

    $http(
      method: 'GET',
      url: API.url + '/ranking',
    ).success((data, status, headers, config) ->
      $scope.ranking = data
      return
    ).error((data, status, headers, config) ->
      $scope.alerts = data
      return
    )
    return
