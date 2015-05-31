'use strict'

###*
 # @ngdoc overview
 # @name carpoolingApp
 # @description
 # # carpoolingApp
 #
 # Main module of the application.
###
angular
  .module 'carpoolingApp', [
    'ngAnimate',
    'ngCookies',
    'ngResource',
    'ngRoute',
    'ngSanitize',
    'ngTouch',
    'LocalStorageModule',
    'ui.bootstrap'
  ]
  .constant "API",
    url: 'http://localhost/tarea3grupo23/backend'
  .config ($routeProvider) ->
    $routeProvider
      .when '/why_carpooling',
        title: '¿Por qué?'
        templateUrl: 'views/home.html'
        controller: 'HomeCtrl'
      .when '/register',
        title: 'Registro'
        templateUrl: 'views/profile.html'
        controller: 'RegisterCtrl'
      .when '/login',
        title: 'Login'
        templateUrl: 'views/login.html'
        controller: 'LoginCtrl'
      .when '/journeys/:mine?',
        title: 'Recorridos'
        templateUrl: 'views/journeys.html'
        controller: 'JourneysCtrl'
      .when '/profiles/:user_id?',
        title: 'Mi perfil'
        templateUrl: 'views/profile.html'
        controller: 'ProfileCtrl'
      .when '/top3',
        title: 'Top 3'
        templateUrl: 'views/ranking.html'
        controller: 'RankingCtrl'
      .otherwise
        redirectTo: '/journeys'
  .config (localStorageServiceProvider) ->
    localStorageServiceProvider.setPrefix('carpoolingApp')
  .run ($rootScope, $http, AuthService) ->
    AuthService.get_session_id()
  .run ($location, $rootScope) ->
    $rootScope.$on '$routeChangeSuccess', (event, current, previous) ->
      if current.$$route?
        $rootScope.title = current.$$route.title;
