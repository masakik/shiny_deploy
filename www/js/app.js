var app = angular.module('shinyDeploy', ['ngRoute', 'ws']);

app.config(function ($routeProvider, $locationProvider, wsProvider) {
    $locationProvider.html5Mode(true);

    wsProvider.setUrl('ws://127.0.0.1:8090');

    $routeProvider
        .when('/', {
            controller: 'HomeController',
            templateUrl: '/js/app/views/home.html'
        })
        .when('/servers', {
            controller: 'ServersController',
            templateUrl: '/js/app/views/servers.html'
        })
        .when('/repositories', {
            controller: 'RepositoriesController',
            templateUrl: '/js/app/views/repositories.html'
        })
        .otherwise({ redirectTo: '/' });
});

app.run(function(ws) {
    // connect to websocket server:
    ws.connect();
});