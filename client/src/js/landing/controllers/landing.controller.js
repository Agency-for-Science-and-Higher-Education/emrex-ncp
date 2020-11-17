landingApp.controller('LandingController', function ($rootScope, $scope) {
    $scope.nias = function () {
        window.location.replace("/login");
    };
});