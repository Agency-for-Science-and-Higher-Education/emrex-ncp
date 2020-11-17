let emrexApp = angular.module('EMREXApp', [
    'pascalprecht.translate',
    'ngSanitize',
    'ngCookies'
]);

emrexApp.config(function($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
});

emrexApp.config(['$httpProvider', function ($httpProvider) {
    $httpProvider.defaults.cache = true;
}]);

emrexApp.config(['$translateProvider', function ($translateProvider) {
    $translateProvider.useSanitizeValueStrategy('escape');
    $translateProvider.translations('hr', translationsHR);
    $translateProvider.translations('en', translationsEN);
    $translateProvider.preferredLanguage('hr');
    $translateProvider.useCookieStorage();
}]);

emrexApp.run(['$rootScope', '$translate', '$location', '$cookieStore', '$http', function ($rootScope, $translate, $location, $cookieStore, $http) {

    window.setInterval(function () {
        $.ajax({url: "check", success: function(result) {
            if (result != 1) {location.reload(true)}
        }});
    }, 2001);

    if ($translate.use() === 'hr') {
        $rootScope.lang = 'hr';
        $translate.use('hr');
    } else {
        $rootScope.lang = 'en';
        $translate.use('en');
    }
}]);