@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/dashboard.css')}}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
@endsection
@section('body')
<div style="margin-bottom: 20px;">

    {{-- Row 1: My Queue --}}
    <div class="row">
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 p-2">
            <div class="card card-container" style="border-left: 8px solid #FA8B3A; margin-bottom: 0;">
                <p class="cards-pg">Awaiting Review</p>
                <h3 class="card-total">{{$awaiting_review}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 p-2">
            <div class="card card-container" style="border-left: 8px solid #2DD683; margin-bottom: 0;">
                <p class="cards-pg">Completed This Month</p>
                <h3 class="card-total">{{$completed_this_month}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 p-2">
            <div class="card card-container" style="border-left: 8px solid #bb2124; margin-bottom: 0;">
                <p class="cards-pg">Rejected This Month</p>
                <h3 class="card-total">{{$rejected_this_month}}</h3>
            </div>
        </div>
    </div>

    {{-- Row 2: Transaction Stats --}}
    <div class="row rows-container">
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #00B3FF; margin-bottom: 0;">
                <p class="cards-pg">Pending Transactions</p>
                <h3 class="card-total">{{$pending_transactions}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #6C63FF; margin-bottom: 0;">
                <p class="cards-pg">Approved Transactions</p>
                <h3 class="card-total">{{$approved_transactions}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #2DD683; margin-bottom: 0;">
                <p class="cards-pg">Completed Transactions</p>
                <h3 class="card-total">{{$completed_transactions}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #FA8B3A; margin-bottom: 0;">
                <p class="cards-pg">Cancelled Transactions</p>
                <h3 class="card-total">{{$cancelled_transactions}}</h3>
            </div>
        </div>
    </div>

    {{-- Row 3: Settlement Stats --}}
    <div class="row rows-container">
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #FED142; margin-bottom: 0;">
                <p class="cards-pg">Pending Settlement</p>
                <h3 class="card-total">₹ {{indianNumberFormat($pending_settlement)}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <div class="card card-container" style="border-left: 8px solid #2DD683; margin-bottom: 0;">
                <p class="cards-pg">Completed Settlement</p>
                <h3 class="card-total">₹ {{indianNumberFormat($total_settlement)}}</h3>
            </div>
        </div>
    </div>

</div>

{{-- Charts --}}
<div class="row">
    <div class="col-lg-6 col-md-6 p-2">
        <div class="card card-container">
            <div class="char-card-header">
                <h2 class="percent" style="color: #6174A5;">Monthly Transactions Completed</h2>
            </div>
            <div>
                <canvas id="transactionChart" style="width:100%; max-height: 400px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-6 p-2">
        <div class="card card-container">
            <div class="char-card-header">
                <h2 class="percent" style="color: #6174A5;">Monthly Settlement Amount</h2>
            </div>
            <div>
                <canvas id="settlementChart" style="width:100%; max-height: 400px;"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
    var xValues = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    new Chart(document.getElementById("transactionChart").getContext("2d"), {
        type: "bar",
        data: {
            labels: xValues,
            datasets: [{
                label: "Completed Transactions",
                backgroundColor: "#2DD683",
                data: JSON.parse('{!! $monthlyTransactionData !!}'),
            }],
        },
        options: {
            scales: {
                yAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }]
            }
        }
    });

    new Chart(document.getElementById("settlementChart").getContext("2d"), {
        type: "bar",
        data: {
            labels: xValues,
            datasets: [{
                label: "Settlement Amount",
                backgroundColor: "#3366CC",
                data: JSON.parse('{!! $monthlySettlementData !!}'),
            }],
        },
        options: {
            scales: {
                yAxes: [{ ticks: { beginAtZero: true } }]
            }
        }
    });
</script>
<script src="{{asset('assets/js/dashboard.js')}}"></script>
@endsection
