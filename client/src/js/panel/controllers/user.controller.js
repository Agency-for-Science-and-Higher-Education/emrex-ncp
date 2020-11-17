emrexApp.controller('UserController',  function ($rootScope, $scope, $http, $translate) {

    $scope.dark = false;

    function loadExecutorInfo() {
        $http.get('executors/' + $translate.use()).then(function (response) {
            $scope.executors = response['data'];
            if (JSON.stringify(response['data']).indexOf("aculty") > 0) {
                $rootScope.lang = 'en';
                $translate.use('en');
            }
        });
    }

    function loadUserInfo() {
        $http.get('user/'+$translate.use() + '/'+$rootScope.isvu).then(function (response) {
            $scope.data = response['data'];
            $scope.mesasage = "";
            $scope.elmo = "";
            $scope.returnUrl = $scope.data.returnUrl;

            //alert($scope.data['university']);
            //alert($scope.dark);

            if ($scope.data['university'] == null) {
                $scope.dark = true;
                //alert($scope.dark);
            }
        });
    }

    $scope.transfer = function() {
        $http({
            method: 'GET',
            url: 'transfer/'+$rootScope.isvu,
        }).then(function (data, status, headers) {
            $scope.elmo = data.data;
            let form = document.getElementById("ncp_form");
            form.action = $scope.returnUrl;
            document.getElementById('elmo_file').value = data.data;
            form.submit();
        });
    };

    $scope.cancel = function() {
        let form = document.getElementById("ncp_form");
        form.action = $scope.returnUrl;
        document.getElementById('elmo_file').value = "";
        document.getElementById('return_code').value = "NCP_CANCEL";
        form.submit();
    };

    $scope.downloadXML = function() {
        if ($scope.dark === false) {
            $(this).attr("href", 'download/xml/'+$rootScope.isvu);
            window.location.href = 'download/xml/'+$rootScope.isvu;
        } else {
            alert('Na odabranom visokom učilištu nemate zapisa.')
        }
    };

    $scope.downloadPDF = function() {
        if ($scope.dark === false) {
            $(this).attr("href", 'download/pdf/'+$rootScope.isvu);
            window.location.href = 'download/pdf/'+$rootScope.isvu;
        } else {
            alert('Na odabranom visokom učilištu nemate zapisa.')
        }
    };

    /*$scope.requestPDF = function() {
        $http({
            method: 'GET',
            url: 'request/pdf/'+$rootScope.isvu,
        }).then(function (data, status, headers) {
            alert(data);
            alert(data.data);
        });
    };*/

    $rootScope.menuFlag = false;
    $rootScope.gradesFlag = true;

    $rootScope.showMenu = function () {
        $rootScope.menuFlag = false;
        $rootScope.gradesFlag = true;
    };

    $rootScope.showGrades = function (isvu) {
        $rootScope.menuFlag = true;
        $rootScope.gradesFlag = false;
        $rootScope.isvu = isvu;
        $scope.dark = false;
        loadUserInfo();
    };

    loadExecutorInfo();
});