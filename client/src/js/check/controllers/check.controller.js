checkApp.controller('CheckController',  function ($rootScope, $scope, $http, $translate) {
    $scope.hide_flag = true;

    $scope.check = function() {
        $http.get('check/' + document.getElementById('record-number').value + '-' + document.getElementById('control-number').value + '.html').then(function (response) {
            document.getElementById("document_anchor").innerHTML = response['data'];
            $scope.hide_flag = false;
        });
    };
});