let landingApp = angular.module('LandingApp', [
    'pascalprecht.translate',
    'ngSanitize',
    'ngCookies'
]);

landingApp.config(function($sceDelegateProvider) {
    $sceDelegateProvider.resourceUrlWhitelist([
        // Allow same origin resource loads.
        'self',
        // Allow loading from our assets domain.  Notice the difference between * and **.
        'https://*.studij.hr/**']);
});

landingApp.config(function($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
});

landingApp.config(['$httpProvider', function ($httpProvider) {
    $httpProvider.defaults.cache = true;
}]);

landingApp.config(['$translateProvider', function ($translateProvider) {
    $translateProvider.useSanitizeValueStrategy('escape');
    $translateProvider.translations('hr', translationsHR);
    $translateProvider.translations('en', translationsEN);
    $translateProvider.preferredLanguage('hr');
    $translateProvider.useCookieStorage();
}]);

landingApp.run(['$rootScope', '$location', '$cookieStore', '$http', '$translate', function ($rootScope, $location, $cookieStore, $http, $translate) {

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