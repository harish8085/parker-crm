@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/dashboard.css')}}">
<style>
    .report-card {
        border: none;
        border-radius: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
        height: 100%;
    }
    .report-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .report-card .card-body {
        padding: 20px;
    }
    .report-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #fff;
        margin-bottom: 14px;
    }
    .report-card h5 {
        font-size: 15px;
        font-weight: 600;
        color: #333;
        margin-bottom: 6px;
    }
    .report-card p {
        font-size: 12px;
        color: #777;
        margin-bottom: 0;
    }
    .report-card a {
        text-decoration: none;
    }
    .reports-header {
        margin-bottom: 20px;
    }
    .reports-header h4 {
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }
    .reports-header p {
        color: #888;
        font-size: 14px;
    }
</style>
@endsection
@section('body')
<div class="reports-header">
    <h4>Reports</h4>
    <p>Select a report to view detailed data and export to Excel</p>
</div>
<div class="row">
    @foreach($reports as $report)
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 p-2">
        <a href="{{ route($report['route']) }}" style="text-decoration:none;">
            <div class="card report-card" style="border-left: 5px solid {{ $report['color'] }};">
                <div class="card-body">
                    <div class="report-icon" style="background-color: {{ $report['color'] }};">
                        <i class="{{ $report['icon'] }}"></i>
                    </div>
                    <h5>{{ $report['title'] }}</h5>
                    <p>{{ $report['description'] }}</p>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>
@endsection
