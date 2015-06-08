'use strict'

###*
 # @ngdoc function
 # @name carpoolingApp.directives:journeysList
 # @description
 # # journeysList
 # Directive of the carpoolingApp
###
angular.module 'carpoolingApp'
  .directive 'journeysList', () ->
    restrict: 'AE',
    scope:
      filterBy: '='
      currentUser: '='
    templateUrl: 'views/directives/journeys_list.html'
    link: (scope) ->
      scope.$on('updated_journeys', () ->
        scope.load_journeys()
      )
      return
    controller: ['$scope', '$http', 'AuthService', 'API', ($scope, $http, AuthService, API) ->
      $scope.days = [
        {'number': 1, 'label': 'L', 'text': 'Lunes'},
        {'number': 2, 'label': 'M', 'text': 'Martes'},
        {'number': 3, 'label': 'M', 'text': 'Miércoles'},
        {'number': 4, 'label': 'J', 'text': 'Jueves'},
        {'number': 5, 'label': 'V', 'text': 'Viernes'},
        {'number': 6, 'label': 'S', 'text': 'Sábado'},
        {'number': 7, 'label': 'D', 'text': 'Domingo'}
      ]

      $scope.music_styles = [
        {'key': 'rock', 'label': 'R', 'text': 'Rock'},
        {'key': 'pop', 'label': 'P', 'text': 'Pop'},
        {'key': 'electronic', 'label': 'E', 'text': 'Electrónica'},
        {'key': 'metal', 'label': 'M', 'text': 'Metal'}
        {'key': 'hiphop', 'label': 'H', 'text': 'Hip hop'}
        {'key': 'funk', 'label': 'F', 'text': 'Funk'}
      ]

      $scope.get_day_by_index = (index) ->
        $scope.days[index]

      $scope.join_journey = (journey_id) ->
        AuthService.get_session_id()
          .then (session_id) ->
            $http(
              method: 'POST',
              url: API.url + '/journeys_passengers',
              headers: {'Authorization': 'Token token=' + session_id},
              data: {'journey_id': journey_id}
            ).success((data, status, headers, config) ->
              $scope.load_journeys()
              return
            )
            return
          return
        return

      $scope.leave_journey = (journey_id) ->
        AuthService.get_session_id()
          .then (session_id) ->
            $http(
              method: 'DELETE',
              url: API.url + '/journeys_passengers/?journey_id=' + journey_id,
              headers: {'Authorization': 'Token token=' + session_id},
            ).success((data, status, headers, config) ->
              $scope.load_journeys()
              return
            )
            return
          return
        return

      $scope.load_journeys = () ->
        $http({method: 'GET', url: API.url + '/journeys'})
          .success (data, status, headers, config) ->
            $scope.journeys = data
            return
        return

      $scope.journeys = []
      $scope.load_journeys()
      return
    ]
