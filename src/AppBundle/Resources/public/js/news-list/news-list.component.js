'use strict';

angular.
module('NewsApp').
component('newsList', {
    templateUrl:  '/app_dev.php/ru/news_list',
    controller: function NewsListController($scope, $http) {
        var self = this;

        $http.get('/app_dev.php/ru/getJsonNews').success(function(data) {
            $scope.news = data;
            $scope.totalItems = data.all;
            $scope.currentPage = 1;
            $scope.itemsPerPage = $scope.viewby;
            $scope.totalPages = Math.ceil($scope.totalItems / 10);
            var it = $scope.locale;
            var item = $scope.currentPage;
        });

        $scope.setPage = function (pageNo) {
            $scope.currentPage = pageNo;
        };

        var execFiles = [];
        var jsonContent = {};
        $scope.viewby = function (countNews) {
            var existStatus = execFiles.indexOf('/app_dev.php/ru/getJsonNews/'+countNews);
            if(existStatus == -1) {
                $http.get('/app_dev.php/ru/getJsonNews/'+countNews).success(function(data) {
                    $scope.news = data;
                    execFiles.push('/app_dev.php/ru/getJsonNews/'+countNews);
                    jsonContent[countNews] = data;
                    console.log('Вызвана функция, значение - ' + countNews);
                });
            }
            else {
                $scope.news = jsonContent[countNews];
            }

        }
    }
});

