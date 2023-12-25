app.controller('sittingCtrl', function($scope , $http, $timeout , DBService) {
    $scope.loading = false;
    $scope.formData = {
        no_of_adults:0,
        no_of_baby_staff:0,
        no_of_children:0,
        name:'',
        mobile:"",
        paid_amount:0,
        hours_occ:'',
    };

    $scope.filter = {};

    $scope.entry_id = 0;
    $scope.total_upi_collection = 0;
    $scope.total_cash_collection = 0;
    $scope.total_collection = 0;

    $scope.last_hour_upi_total = 0;
    $scope.last_hour_cash_total = 0;
    $scope.last_hour_total = 0;

    $scope.check_shift = "";
    $scope.pay_types = [];
    $scope.hours = [];
    
    $scope.init = function () {
        
        DBService.postCall($scope.filter, '/api/sitting/init').then((data) => {

            if (data.success) {
                $scope.pay_types = data.pay_types;
                $scope.hours = data.hours;
                $scope.entries = data.entries;

                $scope.total_upi_collection = data.total_shift_upi;
                $scope.total_cash_collection = data.total_shift_cash;
                $scope.total_collection = data.total_collection;

                $scope.last_hour_upi_total = data.last_hour_upi_total;
                $scope.last_hour_cash_total = data.last_hour_cash_total;
                $scope.last_hour_total = data.last_hour_total;
                
                $scope.check_shift = data.check_shift;
            }
        });
    }
    $scope.filterClear = function(){
        $scope.filter = {};
        $scope.init();
    }

    $scope.edit = function(entry_id){
        $scope.entry_id = entry_id;
        DBService.postCall({entry_id : $scope.entry_id}, '/api/sitting/edit-init').then((data) => {
            if (data.success) {
                $scope.formData = data.sitting_entry;
                $("#exampleModalCenter").modal("show");
            }
            
        });
    }
    $scope.add = function(){
        $("#exampleModalCenter").modal("show");    
    }

    $scope.hideModal = () => {
        $("#exampleModalCenter").modal("hide");
        $scope.entry_id = 0;
        $scope.formData = {
            no_of_adults:0,
            no_of_baby_staff:0,
            no_of_children:0,
            name:'',
            mobile:"",
            total_amount:0,
            paid_amount:0,
            balance_amount:0,
            hours_occ:0,
        };
    }

    $scope.onSubmit = function () {
        $scope.loading = true;
        // console.log($scope.formData);return;
        DBService.postCall($scope.formData, '/api/sitting/store').then((data) => {
            if (data.success) {
                $("#exampleModalCenter").modal("hide");
                $scope.entry_id = 0;
                $scope.formData = {
                    no_of_adults:0,
                    no_of_baby_staff:0,
                    no_of_children:0,
                    name:'',
                    mobile:"",
                    total_amount:0,
                    paid_amount:0,
                    balance_amount:0,
                    hours_occ:0,
                    check_in:'',
                    check_out:'',
                };
                $scope.init();
                setTimeout(function(){
                    window.open(base_url+'/admin/sitting/print/'+data.id, '_blank');

                }, 800);

            }
            $scope.loading = false;
        });
    }

    $scope.calCheck = () => {
        DBService.postCall({check_in:$scope.formData.check_in,hours_occ:$scope.formData.hours_occ}, '/api/sitting/cal-check').then((data) => {
            if (data.success) {
               // console.log(data);
               $scope.formData.check_out = data.check_out;
               $scope.changeAmount();
            }
        });
    }
    $scope.changeAmount = function () {
        $scope.formData.total_amount = 0;
        if($scope.formData.hours_occ > 0){  
            var hours = $scope.formData.hours_occ - 1; 

            if($scope.formData.no_of_adults > 0){
                $scope.formData.total_amount += 30 * $scope.formData.no_of_adults;
                $scope.formData.total_amount += hours * 20 * $scope.formData.no_of_adults;
            }

            if($scope.formData.no_of_children > 0){
                $scope.formData.total_amount += 20 * $scope.formData.no_of_children;
                $scope.formData.total_amount +=  hours * 10 * $scope.formData.no_of_children;
            }

        }
        $scope.formData.balance_amount = $scope.formData.total_amount - $scope.formData.paid_amount;


    }

    $scope.delete = function (id) {
        if(confirm("Are you sure?") == true){
            DBService.getCall('/api/sitting/delete/'+id).then((data) => {
                alert(data.message);
                $scope.init();
            });
        }
       
    }
});

app.controller('shiftCtrl', function($scope , $http, $timeout , DBService) {
    $scope.loading = false;
    $scope.filter = {
        type : 2,
    }

    $scope.init = function () {
        $scope.loading = false;

        DBService.postCall($scope.filter, '/api/shift/init').then((data) => {
            if (data.success) {                 
                $scope.shitting_data = data.shitting_data ; 
                $scope.massage_data = data.massage_data ; 
                $scope.cloack_data = data.cloack_data; 
               
                $scope.total_shift_upi = data.total_shift_upi ; 
                $scope.total_shift_cash = data.total_shift_cash ; 
                $scope.total_collection = data.total_collection ; 

                $scope.last_hour_upi_total = data.last_hour_upi_total ; 
                $scope.last_hour_cash_total = data.last_hour_cash_total ; 
                $scope.last_hour_total = data.last_hour_total ;

                $scope.check_shift = data.check_shift ; 
                $scope.shift_date = data.shift_date ; 
            }
            $scope.loading = true;
        });
    }    

    $scope.prevInit = function () {
        $scope.loading = false;

        DBService.postCall($scope.filter, '/api/shift/prev-init').then((data) => {
            if (data.success) {                 
                $scope.shitting_data = data.shitting_data ; 
                $scope.massage_data = data.massage_data ; 
                $scope.locker_data = data.locker_data ; 

                $scope.total_shift_upi = data.total_shift_upi ; 
                $scope.total_shift_cash = data.total_shift_cash ; 
                $scope.total_collection = data.total_collection ; 
                
                $scope.check_shift = data.check_shift ; 
                $scope.shift_date = data.shift_date ; 
            }
            $scope.loading = true;
        });
    }
    
});

app.controller('massageCtrl', function($scope , $http, $timeout , DBService) {
    $scope.loading = false;
    $scope.formData = {
        paid_amount:0,
        time_period:'',
    };

    $scope.filter = {};
    $scope.m_id = 0;
    $scope.m_entries = [];
 
    $scope.init = function () {
        
        DBService.postCall($scope.filter, '/api/massage/init').then((data) => {
            $scope.pay_types = data.pay_types;
            $scope.m_entries = data.m_entries;
        });
    }
    $scope.filterClear = function(){
        $scope.filter = {};
        $scope.init();
    }

    $scope.edit = function(m_id){
        $scope.m_id = m_id;
        DBService.postCall({m_id : $scope.m_id}, '/api/massage/edit-init').then((data) => {
            if (data.success) {
                $scope.formData = data.m_entry;
                $("#massageModal").modal("show");
            }
        });
    }
    $scope.add = function(){
        $("#massageModal").modal("show");
    }

    $scope.hideModal = () => {
        $("#massageModal").modal("hide");
        $scope.entry_id = 0;
        $scope.formData = {
            paid_amount:0,
            time_period:0,
            in_time:'',
            out_time:'',

        };
        $scope.init();
    }

    $scope.changeTime = function(){
        
        $scope.formData.paid_amount = 0;
        if($scope.formData.time_period == 10){
            $scope.formData.paid_amount = 100;
        }
        if($scope.formData.time_period == 20){
            $scope.formData.paid_amount = 180;
        }
    }

    $scope.onSubmit = function () {
        $scope.loading = true;
        DBService.postCall($scope.formData, '/api/massage/store').then((data) => {
            if (data.success) {


                $("#massageModal").modal("hide");
                $scope.entry_id = 0;
                $scope.formData = {
                    paid_amount:0,
                    time_period:0,
                };
                $scope.init();
                setTimeout(function(){
                    window.open(base_url+'/admin/massage/print/'+data.id, '_blank')
                }, 800);
            }
            $scope.loading = false;
        });
    }

    $scope.delete = function (id) {
        if(confirm("Are you sure") == true){
            DBService.getCall('/api/massage/delete/'+id).then((data) => {
                alert(data.message);
                $scope.init();
            });
        }
       
    }

});
app.controller('userCtrl', function($scope , $http, $timeout , DBService) {
    $scope.loading = false;
    $scope.formData = {
        name:'',
        email:'',
        mobile:'',
        password:'',
        confirm_password:'',
    };
    $scope.filter = {};
    $scope.user_id = 0;
    $scope.users = [];
 
    $scope.init = function () {
        DBService.postCall($scope.filter, '/api/users/init').then((data) => {
            $scope.users = data.users;
        });
    }
    $scope.filterClear = function(){
        $scope.filter = {};
        $scope.init();
    }

    $scope.edit = function(user_id){
        $scope.user_id = user_id;
        DBService.postCall({user_id : $scope.user_id}, '/api/users/edit-init').then((data) => {
            if (data.success) {
                $scope.formData = data.user;
                $("#userModal").modal("show");
            }
        });
    }

    $scope.hideModal = () => {
        $("#userModal").modal("hide");
        $scope.user_id = 0;
        $scope.formData = {
            name:'',
            email:'',
            mobile:'',
            password:'',
            confirm_password:'',
        };
        $scope.init();
    }

    $scope.add = () => {
        $("#userModal").modal("show");
        $scope.user_id = 0;
        $scope.formData = {
            name:'',
            email:'',
            mobile:'',
            password:'',
            confirm_password:'',
        };
    }

    $scope.onSubmit = function () {
        $scope.loading = true;
        DBService.postCall($scope.formData, '/api/users/store').then((data) => {
            if (data.success) {
                alert(data.message);
                $("#userModal").modal("hide");
                $scope.formData = {
                    name:'',
                    email:'',
                    mobile:'',
                    password:'',
                    confirm_password:'',
                };
                $scope.init();
            }else{
                alert(data.message);
            }
            $scope.loading = false;
        });
    }
});

app.controller('cloackCtrl', function($scope , $http, $timeout , DBService) {
    $scope.loading = false;
    $scope.formData = {
        name:'',
        mobile:"",
        paid_amount:0,
        no_of_day:'',
        locker_id:'',
        no_of_bag:0,
    };

    $scope.filter = {};

    $scope.entry_id = 0;

    $scope.check_shift = "";
    $scope.pay_types = [];
    $scope.days = [];

    $scope.init = function () {
        
        DBService.postCall($scope.filter, '/api/cloack-rooms/init').then((data) => {
            if (data.success) {
                $scope.pay_types = data.pay_types;
                $scope.l_entries = data.l_entries;
                $scope.days = data.days;
            }
        });
    }
    $scope.filterClear = function(){
        $scope.filter = {};
        $scope.init();
    }

    $scope.edit = function(entry_id){
        $scope.entry_id = entry_id;
        DBService.postCall({entry_id : $scope.entry_id}, '/api/cloack-rooms/edit-init').then((data) => {
            if (data.success) {
                $scope.formData = data.l_entry;
                $("#exampleModalCenter").modal("show");
            }
            
        });
    }    

    $scope.checkoutLoker = function(entry_id){
        $scope.entry_id = entry_id;
        if(confirm("Are you sure?") == true){
             DBService.postCall({entry_id : $scope.entry_id}, '/api/cloack-rooms/checkout-init').then((data) => {
                if (data.timeOut) {
                    $scope.formData = data.l_entry;
                    
                    $("#checkoutLokerModel").modal("show");
                }else{
                    $scope.init(); 
                }
                
            });
        }
    }

    $scope.add = function(){
        $scope.entry_id = 0;
        $("#exampleModalCenter").modal("show");    
    }

    $scope.hideModal = () => {
        
        $scope.entry_id = 0;
        $scope.formData = {
            name:'',
            mobile:"",
            total_amount:0,
            paid_amount:0,
            balance_amount:0,

        };
        $("#exampleModalCenter").modal("hide");
        $("#checkoutLokerModel").modal("hide");
    }

    $scope.onSubmit = function () {
        $scope.loading = true;
       
        DBService.postCall($scope.formData, '/api/cloack-rooms/store').then((data) => {
            if (data.success) {
                $scope.loading = false;

                $("#exampleModalCenter").modal("hide");
                $scope.entry_id = 0;
                $scope.formData = {
                    name:'',
                    mobile:"",
                    paid_amount:0,
                    no_of_day:'',
                    locker_id:'',
                };
                $scope.init();
                setTimeout(function(){
                    window.open(base_url+'/admin/cloack-rooms/print/'+data.id,'_blank');
                }, 800);

            }
            $scope.loading = false;
        });
    }
    $scope.onCheckOut = function () {
        $scope.loading = true;
        DBService.postCall($scope.formData, '/api/cloack-rooms/checkout-store').then((data) => {
            if (data.success) {
                $("#checkoutLokerModel").modal("hide");
                $scope.entry_id = 0;
                $scope.formData = {
                    name:'',
                    mobile:"",
                    total_amount:0,
                    paid_amount:0,
                    balance_amount:0,
                    hours_occ:0,
                    check_in:'',
                    check_out:'',
                    no_of_bag:0,
                };
                $scope.init();
            }
            $scope.loading = false;
        });
    }


    $scope.changeAmount = function(){
        $scope.formData.paid_amount = 0;
        console.log($scope.formData.paid_amount);
        var amount = 50;
        if($scope.formData.no_of_day > 1){
            amount  = (amount + (($scope.formData.no_of_day-1)*75));
        }
        $scope.formData.paid_amount = (amount*$scope.formData.no_of_bag);
    }

    $scope.delete = function (id) {
        if(confirm("Are you sure?") == true){
            DBService.getCall('/api/cloack-rooms/delete/'+id).then((data) => {
                alert(data.message);
                $scope.init();
            });
        }
    }

    
});


