'use strict';

/**
 * @ngdoc function
 * @name piholeAdminApp.controller:DashboardCtrl
 * @description
 * # DashboardCtrl
 * Controller of the piholeAdminApp
 */
angular.module('piholeAdminApp')
  .controller('MenuCtrl', ['$scope', 'API', '$interval', 'CacheService', function ($scope, API, $interval, CacheService) {
    var inCache = [];


    var getStatus = function () {
      API.getStatus().then(function (status) {
        $scope.status = status;
      });


    };

    var getTemp = function(){
      API.getTemp().then(function (temp) {
        $scope.temp = temp.temp;
      });
    };


    getStatus();
    getTemp();

    $interval(function () {
      getStatus();
    }, 10 * 1000);

    $interval(function () {
      getTemp();
    }, 60 * 1000);
  }]);
