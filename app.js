'use strict';

/**
 * @ngdoc overview
 * @name ngReviewApp
 * @description
 * # ngReviewApp
 *
 * Main module of the application.
 */

angular.module('ngReviewApp',['ngRoute','firebase','LocalStorageModule','booksApp.controllers','booksApp.services'])

.config(function($routeProvider){

	$routeProvider
	.when("/",{
		templateUrl: 'views/main.html',
		controller: 'MainController',
		controllerAs: 'vm',
		authRequired: true
	})
	.when("/reviews",{
		templateUrl: 'views/main.html',
		controller: 'MainController',
		controllerAs: 'vm',
		authRequired: true
	})
    .when('/login', {
            templateUrl: 'views/login.html',
            controller: 'LoginController',
            controllerAs: 'vm'
    })
    .when('/register', {
            templateUrl: 'views/register.html',
            controller: 'LoginController',
            controllerAs: 'vm'
    })
    .when('/logout', {
            templateUrl: 'views/logout.html',
            controller: 'LogoutController',
            controllerAs: 'vm'
    })	
    .when('/addreview', {
        templateUrl: 'views/addreview.html',
        controller: 'ReviewController',
        controllerAs: 'vm',
        authRequired: true
    })
    .when('/books', {
        templateUrl: 'views/books.html',
        controller: 'booksAppCtrl',
        
    })
        .when('/admin', {
        templateUrl: 'views/admin.html',
        controller: 'MainController',
        controllerAs: 'vm',
        authRequired: true
        
    })
.otherwise({redirectTo: '/'});
    

})

.run(['AuthFactory', function (AuthFactory) {
    AuthFactory.fillAuthData();
}]);