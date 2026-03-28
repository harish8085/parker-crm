@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/dashboard.css')}}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
@endsection
@section('body')
<div style="margin-bottom: 20px;">

    {{-- Row 1: Application Stats --}}
    <div class="row">
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <a class="dashboard-card-link" href="{{url('application')}}">
                <div class="card card-container" style="border-left: 8px solid #00B3FF; margin-bottom: 0;">
                    <p class="cards-pg">Total Applications</p>
                    <h3 class="card-total">{{$total_application}}</h3>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <a class="dashboard-card-link" href="{{ url('application') }}?status=completed">
                <div class="card card-container" style="border-left: 8px solid #2DD683; margin-bottom: 0;">
                    <p class="cards-pg">Completed Applications</p>
                    <h3 class="card-total">{{$completed_application}}</h3>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <a class="dashboard-card-link" href="{{ url('application') }}?status=pending">
                <div class="card card-container" style="border-left: 8px solid #FED142; margin-bottom: 0;">
                    <p class="cards-pg">Pending Applications</p>
                    <h3 class="card-total">{{$pending_application}}</h3>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <a class="dashboard-card-link" href="{{ url('application') }}?status=rejected">
                <div class="card card-container" style="border-left: 8px solid #FA8B3A; margin-bottom: 0;">
                    <p class="cards-pg">Rejected Applications</p>
                    <h3 class="card-total">{{$rejected_application}}</h3>
                </div>
            </a>
        </div>
    </div>
    {{-- Row 4: Transaction Stats --}}
    <div class="row rows-container">
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <a class="dashboard-card-link" href="{{ url('transactions') }}?status=pending">
                <div class="card card-container" style="border-left: 8px solid #00B3FF; margin-bottom: 0;">
                    <p class="cards-pg">Pending Transactions</p>
                    <h3 class="card-total">{{$pending_transactions}}</h3>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <a class="dashboard-card-link" href="{{ url('transactions') }}?status=approved">
                <div class="card card-container" style="border-left: 8px solid #6C63FF; margin-bottom: 0;">
                    <p class="cards-pg">Approved Transactions</p>
                    <h3 class="card-total">{{$approved_transactions}}</h3>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <a class="dashboard-card-link" href="{{ url('transactions') }}?status=completed">
                <div class="card card-container" style="border-left: 8px solid #2DD683; margin-bottom: 0;">
                    <p class="cards-pg">Completed Transactions</p>
                    <h3 class="card-total">{{$completed_transactions}}</h3>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <a class="dashboard-card-link" href="{{ url('transactions') }}?status=cancelled">
                <div class="card card-container" style="border-left: 8px solid #FA8B3A; margin-bottom: 0;">
                    <p class="cards-pg">Cancelled Transactions</p>
                    <h3 class="card-total">{{$cancelled_transactions}}</h3>
                </div>
            </a>
        </div>
    </div>

   

    {{-- Row 3: Users --}}
    <div class="row rows-container">
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <a class="dashboard-card-link" href="{{url('channel')}}">
                <div class="card card-container" style="border-left: 8px solid #00B3FF; margin-bottom: 0;">
                    <p class="cards-pg">Total Channel Partners</p>
                    <h3 class="card-total">{{$total_channel_partner}}</h3>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <a class="dashboard-card-link" href="{{url('channel')}}">
                <div class="card card-container" style="border-left: 8px solid #2DD683; margin-bottom: 0;">
                    <p class="cards-pg">Total Associates</p>
                    <h3 class="card-total">{{$total_associate}}</h3>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <a class="dashboard-card-link" href="{{ url('application') }}?status=approved">
                <div class="card card-container" style="border-left: 8px solid #FED142; margin-bottom: 0;">
                    <p class="cards-pg">Approved Applications</p>
                    <h3 class="card-total">{{$approved_application}}</h3>
                </div>
            </a>
        </div>
    </div>

   
    {{-- Row 2: Settlement Stats --}}
    <div class="row rows-container">
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <a class="dashboard-card-link" href="{{url('settlement')}}">
                <div class="card card-container" style="border-left: 8px solid #FED142; margin-bottom: 0;">
                    <p class="cards-pg">Pending Settlement</p>
                    <h3 class="card-total">₹ {{indianNumberFormat($pending_settlement)}}</h3>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2">
            <a class="dashboard-card-link" href="{{url('settlement')}}">
                <div class="card card-container" style="border-left: 8px solid #2DD683; margin-bottom: 0;">
                    <p class="cards-pg">Completed Settlement</p>
                    <h3 class="card-total">₹ {{indianNumberFormat($total_settlement)}}</h3>
                </div>
            </a>
        </div>
    </div>

    {{-- Row 5: Top Performing Channels --}}
    @if($topChannels->count() > 0)
    <div class="row rows-container">
        <div class="col-12 p-2">
            <div class="card card-container">
                <h2 class="percent" style="color: #6174A5; margin-bottom: 15px;">Top Performing Channels</h2>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="font-weight:600; color:#555;">#</th>
                                <th style="font-weight:600; color:#555;">Channel Name</th>
                                <th style="font-weight:600; color:#555;">Total Applications</th>
                                <th style="font-weight:600; color:#555;">Total Disburse Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topChannels as $index => $channel)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $channel->first_name }} {{ $channel->last_name }}</td>
                                <td>{{ $channel->applications_count }}</td>
                                <td>₹ {{ indianNumberFormat($channel->total_disburse) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- Charts --}}
<div class="row">
    <div class="col-lg-7 col-md-6 p-2">
        <div class="card card-container">
            <div class="char-card-header">
                <h2 class="percent" style="color: #6174A5;">Monthly Settlement</h2>
            </div>
            <div>
                <canvas id="myChart" style="width:100%; max-width:600px; max-height: 450px;"></canvas>
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

@endsection
@section('script')
<script>
    var xValues = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var yValues = JSON.parse('{!! $monthlyData !!}');
    var barColors = Array(12).fill("#3366CC");

    new Chart(document.getElementById("myChart").getContext("2d"), {
        type: "bar",
        data: {
            labels: xValues,
            datasets: [{
                label: "Settlement Amount",
                backgroundColor: barColors,
                data: yValues,
            }],
        },
        options: {
            scales: {
                yAxes: [{ ticks: { beginAtZero: true } }]
            }
        }
    });

    new Chart(document.getElementById("chartjs-doughnut").getContext("2d"), {
        type: "doughnut",
        data: {
            labels: ["Pending", "Rejected", "Completed", "Approved"],
            datasets: [{
                data: ['{{$pending_application}}', '{{$rejected_application}}', '{{$completed_application}}', '{{$approved_application}}'],
                backgroundColor: ["#FED142", "#bb2124", "#42CC7D", "#3366CC"],
                borderColor: "transparent",
                borderWidth: 5,
            }],
        },
        options: {
            maintainAspectRatio: false,
            cutoutPercentage: 70,
        },
    });
</script>
<script src="{{asset('assets/js/dashboard.js')}}"></script>
@endsection

