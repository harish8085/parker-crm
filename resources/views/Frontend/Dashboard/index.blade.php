@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/dashboard.css')}}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>

@endsection
@section('body')
<div style="margin-bottom: 20px;">
    <div class="row">
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #00B3FF; margin-bottom: 0;">
                <p class="cards-pg">Total Application</p>
                <h3 class="card-total">{{$total_application}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2 ">
            <div class="card card-container" style="border-left: 8px solid #2DD683; margin-bottom: 0;">
                <p class="cards-pg">Completed Application</p>
                <h3 class="card-total">{{$completed_application}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2 ">
            <div class="card card-container" style="border-left: 8px solid #FED142; margin-bottom: 0;">
                <p class="cards-pg">Pending Application</p>
                <h3 class="card-total">{{$pending_application}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2 ">
            <div class="card card-container" style="border-left: 8px solid #FA8B3A; margin-bottom: 0;">
                <p class="cards-pg">Reject Application</p>
                <h3 class="card-total">{{$rejected_application}}</h3>
            </div>
        </div>
    </div>
    <div class="row rows-container">
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #00B3FF; margin-bottom: 0;">
                <p class="cards-pg">Today Sales</p>
                <h3 class="card-total">{{$today_sales}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2 ">
            <div class="card card-container" style="border-left: 8px solid #2DD683; margin-bottom: 0;">
                <p class="cards-pg">Monthly Sales</p>
                <h3 class="card-total">{{$monthly_sales}}</h3>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2 ">
            <div class="card card-container" style="border-left: 8px solid #FED142; margin-bottom: 0;">
                <p class="cards-pg">Pending Settlement</p>
                <h3 class="card-total">₹ {{indianNumberFormat($pending_settlement)}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #FA8B3A; margin-bottom: 0;">
                <p class="cards-pg">Total Settlement</p>
                <h3 class="card-total">₹ {{indianNumberFormat($total_settlement)}}</h3>
            </div>
        </div>

    </div>
    <div class="row rows-container">
        @if($role_id != 2 && $role_id != 3)
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #00B3FF; margin-bottom: 0;">
                <p class="cards-pg">Today Channel Partner</p>
                <h3 class="card-total">{{$total_channel_partner}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2 ">
            <div class="card card-container" style="border-left: 8px solid #2DD683; margin-bottom: 0;">
                <p class="cards-pg">Today Sales Person</p>
                <h3 class="card-total">{{$total_sales_person}}</h3>
            </div>
        </div>

        @if(auth()->user()->hasPermission('staff','view'))
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2 ">
            <div class="card card-container" style="border-left: 8px solid #FED142; margin-bottom: 0;">
                <p class="cards-pg">Total Users</p>
                <h3 class="card-total">{{$total_staff}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #FA8B3A; margin-bottom: 0;">
                <p class="cards-pg">Total Roles</p>
                <h3 class="card-total">{{$total_roles}}</h3>
            </div>
        </div>
        @endif
        @endif

    </div>


</div>

<!-- bar chart -->

<div class="row">
    <div class="col-lg-7 col-md-6 p-2">

        <div class="card card-container">
            <div class="char-card-header">
                <h2 class="percent" style="color: #6174A5;">Monthly Statistics</h2>
            </div>
            <div>
                <canvas id="myChart" style="width:100%; max-width:600px; max-height: 450px; "></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5 col-md-6 p-2">
        <div class="card card-container">
            <div class="char-card-header">
                <h2 class="percent" style="color: #6174A5;">Application Statistics</h2>
            </div>
            <div style="display: flex; width: 100%; justify-content: center;">
                <div class="chart-container">
                    <canvas id="chartjs-doughnut" style="width: 100%; max-width: 300px; max-height: 450px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /# row -->

@endsection
@section('script')
<script>
    var xValues = ["Jan", "Fab", "Mar", "Apr", "May", "Jun", "July", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var yValues = JSON.parse('{{$monthlyData}}');
    var barColors = ["#3366CC", "#3366CC", "#3366CC", "#3366CC", "#3366CC", "#3366CC", "#3366CC", "#3366CC", "#3366CC", "#3366CC", "#3366CC", "#3366CC", "#3366CC"];
    var ctx1 = document.getElementById("myChart").getContext("2d");

    new Chart(ctx1, {
        type: "bar",
        data: {
            labels: xValues,
            datasets: [{
                label: "Settlement",
                backgroundColor: barColors,
                data: yValues,
            }],


        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });

    var ctx = document.getElementById("chartjs-doughnut").getContext("2d");

    var myDoughnutChart = new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: ["Pending", "Rejected", "Completed"], // Add labels here
            datasets: [{

                data: ['{{$pending_application}}', '{{$rejected_application}}', '{{$completed_application}}'],
                backgroundColor: ["#3366CC", "#bb2124", "#42CC7D"],
                borderColor: "transparent",
                borderWidth: 5,
                innerRadius: 5,
            }, ],
        },
        options: {
            maintainAspectRatio: false,
            cutoutPercentage: 70,
        },
    });
</script>
<script src="{{asset('assets/js/dashboard.js')}}"></script>

@endsection