let checkApp = angular.module('CheckApp', [
    'pascalprecht.translate',
    'ngSanitize',
    'ngCookies'
]);

checkApp.config(function($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
});

checkApp.config(['$httpProvider', function ($httpProvider) {
    $httpProvider.defaults.cache = true;
}]);

checkApp.config(['$translateProvider', function ($translateProvider) {
    $translateProvider.useSanitizeValueStrategy('escape');
    $translateProvider.translations('hr', translationsHR);
    $translateProvider.translations('en', translationsEN);
    $translateProvider.preferredLanguage('hr');
    $translateProvider.useCookieStorage();
}]);

checkApp.run(['$rootScope', '$translate', '$location', '$cookieStore', '$http', function ($rootScope, $translate, $location, $cookieStore, $http) {

    $rootScope.lang = 'hr';

    $rootScope.changeLanguage = function () {
        if ($translate.use() === 'hr') {
            $rootScope.lang = 'en';
            $translate.use('en');
        } else {
            $rootScope.lang = 'hr';
            $translate.use('hr');
        }
    };
}]);