'use strict'

###*
 # @ngdoc function
 # @name carpoolingApp.controller:JourneysCtrl
 # @description
 # # JourneysCtrl
 # Controller of the carpoolingApp
###
angular.module 'carpoolingApp'
  .controller 'JourneysCtrl', ($scope, $http, $routeParams, $filter, $location, API, AuthService) ->

    $scope.form_journey_create = () ->
      $scope.alerts = []
      if not $scope.form_new_journey.$valid
        $scope.alerts.push {
          'type': 'danger',
          'text': 'El formulario contiene errores.'
        }
        return

      new_journey_clean = $.extend({}, $scope.new_journey)
      new_journey_clean.time = moment(new_journey_clean.time).format('HH:mm')
      AuthService.get_session_id()
        .then (session_id) ->
          $http(
            method: 'POST',
            url: API.url + '/journeys',
            headers: {'Authorization': 'Token token=' + session_id},
            data: {'journey': new_journey_clean}
          ).success((data, status, headers, config) ->
            $scope.journeys.push(data)
            $scope.$broadcast('updated_journeys')
            return
          ).error((data, status, headers, config) ->
            $scope.alerts = data
            return
          )
          return
        return
      return

    $scope.my_journeys = false
    $scope.journeys = []
    if $routeParams.mine == 'mine'
      $scope.my_journeys = true
      $scope.alerts = []
      $scope.new_journey = {
        'time': new Date()
      }
      $scope.journeys_title = 'Mis recorridos'
      $scope.current_session_filter = (element) ->
        $scope.current_session? and
          (element.user_id == $scope.current_session.user_id or
            element.passengers.indexOf($scope.current_session.user_id) >= 0)
    else
      $scope.my_journeys = false
      $scope.journeys_title = 'Recorridos'

    if $scope.my_journeys && ! $scope.is_user_logged_in()
      $location.path "/"
    return
