@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/dashboard.css')}}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
@endsection
@section('body')
<div style="margin-bottom: 20px;">

    {{-- Row 1: My Applications (only Pending and Completed) --}}
    <div class="row">
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 p-2">
            <div class="card card-container" style="border-left: 8px solid #FED142; margin-bottom: 0;">
                <p class="cards-pg">Pending Applications</p>
                <h3 class="card-total">{{$pending_application}}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 p-2">
            <div class="card card-container" style="border-left: 8px solid #2DD683; margin-bottom: 0;">
                <p class="cards-pg">Completed Applications</p>
                <h3 class="card-total">{{$completed_application}}</h3>
            </div>
        </div>
    </div>

</div>

{{-- Chart: Pending vs Completed --}}
<div class="row">
    <div class="col-lg-6 col-md-8 p-2">
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
    new Chart(document.getElementById("chartjs-doughnut").getContext("2d"), {
        type: "doughnut",
        data: {
            labels: ["Pending", "Completed"],
            datasets: [{
                data: ['{{$pending_application}}', '{{$completed_application}}'],
                backgroundColor: ["#FED142", "#42CC7D"],
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
