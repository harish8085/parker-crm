@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/dashboard.css')}}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
@endsection
@section('body')
<div style="margin-bottom: 20px;">

    {{-- Row 1: My Queue --}}
    <div class="row">
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #FA8B3A; margin-bottom: 0;">
                <p class="cards-pg">Pending Applications</p>
                <h3 class="card-total">{{$pending_applications}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #2DD683; margin-bottom: 0;">
                <p class="cards-pg">Approved Today</p>
                <h3 class="card-total">{{$approved_today}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #00B3FF; margin-bottom: 0;">
                <p class="cards-pg">Approved This Month</p>
                <h3 class="card-total">{{$approved_this_month}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #bb2124; margin-bottom: 0;">
                <p class="cards-pg">Rejected This Month</p>
                <h3 class="card-total">{{$rejected_this_month}}</h3>
            </div>
        </div>
    </div>

    {{-- Row 2: Overall Stats --}}
    <div class="row rows-container">
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #6C63FF; margin-bottom: 0;">
                <p class="cards-pg">Total Processed</p>
                <h3 class="card-total">{{$total_processed}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #2DD683; margin-bottom: 0;">
                <p class="cards-pg">Approval Rate</p>
                <h3 class="card-total">{{$approval_rate}}%</h3>
            </div>
        </div>
    </div>

</div>

{{-- Chart: Monthly Approval/Rejection Trend --}}
<div class="row">
    <div class="col-12 p-2">
        <div class="card card-container">
            <div class="char-card-header">
                <h2 class="percent" style="color: #6174A5;">Monthly Approval / Rejection Trend</h2>
            </div>
            <div>
                <canvas id="makerTrendChart" style="width:100%; max-height: 400px;"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
    var xValues = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var approvedData = JSON.parse('{!! $monthlyApprovedData !!}');
    var rejectedData = JSON.parse('{!! $monthlyRejectedData !!}');

    new Chart(document.getElementById("makerTrendChart").getContext("2d"), {
        type: "bar",
        data: {
            labels: xValues,
            datasets: [
                {
                    label: "Approved",
                    backgroundColor: "#2DD683",
                    data: approvedData,
                },
                {
                    label: "Rejected",
                    backgroundColor: "#bb2124",
                    data: rejectedData,
                }
            ],
        },
        options: {
            scales: {
                yAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }]
            }
        }
    });
</script>
<script src="{{asset('assets/js/dashboard.js')}}"></script>
@endsection
